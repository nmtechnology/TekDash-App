<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js';
import NetworkStatusIndicator from '@/Components/NetworkStatusIndicator.vue';

// Register Chart.js components
ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);

const revenueData = ref({
  monthly: [],
  totalRevenue: 0,
  comparedToLastMonth: 0,
  comparedToLastYear: 0,
  forecastNextMonth: 0,
  last7DaysRevenue: 0,
  last30DaysRevenue: 0
});

// Add a debug flag
const showDebug = ref(false);
const debugInfo = ref(null);
// Add collapsed state
const isCollapsed = ref(false);
const chartRef = ref(null);

// Internet speed test variables
const isTestingSpeed = ref(false);
const speedTestResults = ref(null);

// Function to test internet speed with improved calculation
async function testInternetSpeed() {
  try {
    isTestingSpeed.value = true;
    speedTestResults.value = null;
    
    // First measure latency with a small HEAD request
    const pingStartTime = performance.now();
    await fetch('/favicon.ico?t=' + new Date().getTime(), {
      method: 'HEAD',
      cache: 'no-store'
    });
    const pingEndTime = performance.now();
    const latency = pingEndTime - pingStartTime;
    
    // For better accuracy, we'll repeatedly download a larger file
    const iterations = 3;
    let totalBytes = 0;
    let startTime = performance.now();
    
    // Try to download something larger than favicon
    const testUrl = '/build/assets/app.js'; // Usually larger than favicon
    
    // Download the same file multiple times for more data
    for (let i = 0; i < iterations; i++) {
      // Use a different URL each time to avoid caching
      const url = `${testUrl}?noc=${Date.now()}-${i}`;
      
      const response = await fetch(url, {
        method: 'GET',
        cache: 'no-store'
      });
      
      if (!response.ok) {
        console.log(`Failed to fetch ${url}, falling back to favicon`);
        // Try a larger asset first for more accurate measurement
        const fallbackResponse = await fetch(`/build/assets/app.css?noc=${Date.now()}-${i}`, {
          method: 'GET',
          cache: 'no-store'
        }).catch(() => {
          // If that fails too, try js file
          return fetch(`/build/assets/app.js?noc=${Date.now()}-${i}`, {
            method: 'GET',
            cache: 'no-store'
          });
        }).catch(() => {
          // Last resort - use favicon
          return fetch(`/favicon.ico?noc=${Date.now()}-${i}`, {
            method: 'GET',
            cache: 'no-store'
          });
        });
        if (!fallbackResponse.ok) {
          throw new Error(`HTTP error! status: ${fallbackResponse.status}`);
        }
        const data = await fallbackResponse.arrayBuffer();
        totalBytes += data.byteLength;
      } else {
        // Get the file data and add to our total
        const data = await response.arrayBuffer();
        totalBytes += data.byteLength;
      }
    }
    
    // Calculate time taken in seconds (excluding initial latency)
    const endTime = performance.now();
    // We subtract the latency for one request, as the rest is actual download time
    const downloadTime = (endTime - startTime - latency) / 1000; // Convert to seconds
    
    console.log(`Speed test debug - Total bytes: ${totalBytes}, Download time: ${downloadTime}s`);
    
    // Prevent division by zero or very small numbers
    if (downloadTime < 0.1) {
      console.warn('Download time too small for accurate measurement');
      await testWithLargerFile();
      return;
    }
    
    // Now we have total bytes and download time. File size in bits (8 bits = 1 byte)
    const fileSizeInBits = totalBytes * 8;
    
    // Calculate speed in Mbps (megabits per second) = bits / seconds / 1,000,000
    const speedMbps = fileSizeInBits / downloadTime / 1000000;
    
    console.log(`Speed calculation: ${fileSizeInBits} bits / ${downloadTime} seconds / 1,000,000 = ${speedMbps} Mbps`);
    
    // Store results - ensure we have a valid number
    speedTestResults.value = {
      fileSize: totalBytes,
      duration: downloadTime * 1000, // Back to ms for display
      speedMbps: isNaN(speedMbps) || !isFinite(speedMbps) ? 0 : speedMbps,
      latency: latency,
      timestamp: new Date().toISOString(),
      iterations: iterations
    };
    
    // If the result seems implausible, try a second approach
    if (speedMbps > 1000 || speedMbps < 0.1) {
      console.warn('Speed result seems unusual, trying alternative test method');
      await testWithLargerFile();
    }
    
  } catch (error) {
    console.error('Speed test failed:', error);
    
    // Try fallback method
    await testWithLargerFile();
  } finally {
    isTestingSpeed.value = false;
  }
}

// Try with a single larger file download - improved method
async function testWithLargerFile() {
  try {
    // Try to find a larger file for more accurate test - improved file list
    const fileUrls = [
      '/build/assets/app.js',   // Vite bundled JS (usually large)
      '/js/app.js',             // Laravel Mix assets
      '/css/app.css',           // CSS file
      '/favicon.ico'            // Fallback to favicon
    ];
    
    // Find the first file that exists and is larger than 10KB
    let testFileUrl = null;
    let fileSize = 0;
    
    for (const url of fileUrls) {
      try {
        // Try a HEAD request to see if the file exists
        const response = await fetch(url, { 
          method: 'HEAD',
          cache: 'no-store'
        });
        
        if (response.ok) {
          // Check file size if available
          const contentLength = response.headers.get('content-length');
          if (contentLength && parseInt(contentLength) > 10240) {
            // If file is larger than 10KB, use it
            testFileUrl = url;
            fileSize = parseInt(contentLength);
            console.log(`Found suitable test file: ${url} (${fileSize} bytes)`);
            break;
          } else {
            // File exists but size unknown or too small, save as fallback
            if (!testFileUrl) {
              testFileUrl = url;
            }
          }
        }
      } catch(e) {
        // Ignore errors and try next file
      }
    }
    
    // If no file was found, use a fixed URL
    if (!testFileUrl) {
      testFileUrl = '/favicon.ico'; // Last resort
    }
    
    console.log(`Using ${testFileUrl} for speed test`);
    
    // Now do the actual speed test with multiple requests for more accuracy
    let totalBytes = 0;
    let iterations = 3;
    
    // First measure latency separately
    const pingStartTime = performance.now();
    await fetch(testFileUrl, {
      method: 'HEAD',
      cache: 'no-store'
    });
    const pingEndTime = performance.now();
    const latency = pingEndTime - pingStartTime;
    
    // Now measure download speed
    const startTime = performance.now();
    
    // Download multiple times to get more data and better accuracy
    for (let i = 0; i < iterations; i++) {
      const response = await fetch(`${testFileUrl}?nocache=${Date.now()}-${i}`, {
        method: 'GET',
        cache: 'no-store'
      });
      
      const data = await response.blob(); // Use blob for more accurate size
      totalBytes += data.size;
    }
    
    const endTime = performance.now();
    
    // Calculate download time excluding latency
    const downloadTime = Math.max((endTime - startTime - latency) / 1000, 0.1); // in seconds, minimum 0.1
    
    console.log(`Alternative test - Total bytes: ${totalBytes}, Download time: ${downloadTime}s`);
    
    // Calculate in bits per second, then convert to Mbps
    const bitsTransferred = totalBytes * 8;
    const bitsPerSecond = bitsTransferred / downloadTime;
    const megabitsPerSecond = bitsPerSecond / 1000000;
    
    console.log(`Alternative calculation: ${bitsTransferred} bits / ${downloadTime} seconds / 1,000,000 = ${megabitsPerSecond} Mbps`);
    
    speedTestResults.value = {
      fileSize: totalBytes,
      duration: downloadTime * 1000,
      speedMbps: megabitsPerSecond,
      latency: latency,
      timestamp: new Date().toISOString(),
      iterations: iterations,
      testFile: testFileUrl,
      method: 'multi-download'
    };
  } catch (error) {
    console.error('Alternative speed test failed:', error);
    fallbackSpeedTest();
  }
}

// Simplified fallback test for when other methods fail
async function fallbackSpeedTest() {
  try {
    // Use a simple test with minimal overhead
    const testFile = '/favicon.ico';
    const iterations = 5;
    let totalBytes = 0;
    const startTime = performance.now();
    
    // Do multiple requests
    for (let i = 0; i < iterations; i++) {
      const response = await fetch(`${testFile}?t=${Date.now()}-${i}`, { 
        cache: 'no-store' 
      });
      
      if (!response.ok) continue;
      
      const data = await response.blob();
      totalBytes += data.size;
    }
    
    const endTime = performance.now();
    const durationSeconds = (endTime - startTime) / 1000;
    
    // Convert bytes to bits and calculate Mbps
    const bitsTransferred = totalBytes * 8;
    const mbps = bitsTransferred / durationSeconds / 1000000;
    
    speedTestResults.value = {
      fileSize: totalBytes,
      duration: durationSeconds * 1000, // ms
      speedMbps: mbps,
      latency: durationSeconds * 1000 / iterations, // simple estimate
      timestamp: new Date().toISOString(),
      iterations: iterations,
      method: 'fallback'
    };
  } catch (error) {
    console.error('All speed tests failed:', error);
    speedTestResults.value = {
      error: 'Internet speed measurement failed. Please check your connection and try again.',
      timestamp: new Date().toISOString()
    };
  }
}

const chartData = computed(() => {
  const labels = revenueData.value.monthly.map(item => item.month);
  const values = revenueData.value.monthly.map(item => item.revenue);
  
  return {
    labels: labels,
    datasets: [
      {
        label: 'Monthly Revenue',
        backgroundColor: 'rgba(132, 204, 22, 0.2)',
        borderColor: 'rgb(132, 204, 22)',
        data: values,
        tension: 0.4
      }
    ]
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: 'rgb(132, 204, 22)',
      bodyColor: '#fff'
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(255, 255, 255, 0.1)'
      },
      ticks: {
        color: 'rgba(255, 255, 255, 0.7)'
      }
    },
    x: {
      grid: {
        color: 'rgba(255, 255, 255, 0.1)'
      },
      ticks: {
        color: 'rgba(255, 255, 255, 0.7)'
      }
    }
  }
};

const isLoading = ref(true);
const errorMessage = ref(null);

async function fetchRevenueData() {
  try {
    isLoading.value = true;
    errorMessage.value = null;
    
    // Add timestamp to prevent caching
    const timestamp = new Date().getTime();
    const url = `/revenue-stats?t=${timestamp}`;
    
    // Try using the fetch API first (more reliable for some network issues)
    try {
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'Cache-Control': 'no-cache',
        },
        credentials: 'same-origin'
      });
      
      if (response.ok) {
        const data = await response.json();
        revenueData.value = data;
        
        if (data._debug) {
          debugInfo.value = data._debug;
          delete revenueData.value._debug;
        }
        
        // Make sure to resize the chart when data is loaded
        if (!isCollapsed.value) {
          nextTick(() => {
            if (chartRef.value && chartRef.value.chart) {
              chartRef.value.chart.resize();
            }
          });
        }
        
        console.log('Revenue data loaded with fetch API');
        isLoading.value = false;
        return; // Exit early if fetch was successful
      }
    } catch (fetchError) {
      console.warn('Fetch API failed, falling back to Axios', fetchError);
      // Continue with axios as fallback
    }
    
    // Fallback to Axios with timestamp
    const response = await axios.get(url, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Cache-Control': 'no-cache',
        'Pragma': 'no-cache',
        'Expires': '0',
      },
      timeout: 15000, // 15 seconds should be enough 
      withCredentials: true,
    });
    
    if (response.data) {
      revenueData.value = response.data;
      console.log('Revenue data loaded with Axios');
      
      if (response.data._debug) {
        debugInfo.value = response.data._debug;
        delete revenueData.value._debug;
      }
      
      // Resize chart
      if (!isCollapsed.value) {
        nextTick(() => {
          if (chartRef.value && chartRef.value.chart) {
            chartRef.value.chart.resize();
          }
        });
      }
    }
  } catch (error) {
    console.error('Error fetching revenue data:', error);
    
    // Better error handling with specific messages
    if (error.code === 'ERR_NETWORK') {
      errorMessage.value = "Network error: Unable to connect to the server. Please check your internet connection.";
    } else if (error.response) {
      // The server responded with a status code outside the 2xx range
      errorMessage.value = `Server error: ${error.response.status} - ${error.response.data?.message || 'Unknown error'}`;
    } else if (error.request) {
      // The request was made but no response was received
      errorMessage.value = "No response received from server. Please try again later.";
    } else {
      // Something else happened while setting up the request
      errorMessage.value = `Error: ${error.message}`;
    }
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchRevenueData();
});

// Format currency
function formatCurrency(value) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
}

// Toggle collapsed state
function toggleCollapse() {
  isCollapsed.value = !isCollapsed.value;
  
  // If expanding, wait for DOM to update then resize chart
  if (!isCollapsed.value) {
    nextTick(() => {
      if (chartRef.value && chartRef.value.chart) {
        chartRef.value.chart.resize();
      }
    });
  }
}

// Update the connectToQuickBooks method to use a fixed redirect URI
function connectToQuickBooks() {
  // Redirect to the QuickBooks authorization endpoint
  window.location.href = `/quickbooks/authorize`;
}
</script>

<template>
  <div class="revenue-stats-container glass-container p-5 mb-6 mt-12">
    <div class="flex justify-between items-center mb-4">
      <div class="flex items-center gap-2">
        <button
          @click="toggleCollapse"
          class="glass-button flex items-center justify-center w-6 h-6 rounded-full transition"
          :title="isCollapsed ? 'Expand' : 'Collapse'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': isCollapsed }" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 111.414 1.414l-4 4a1 1 01-1.414 0l-4-4a1 1 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
          NM Technology's Revenue Statistics
          <NetworkStatusIndicator class="inline-block ml-2" />
        </h2>
      </div>
      <div class="flex gap-2">
        <button 
          @click="connectToQuickBooks" 
          class="glass-button flex items-center gap-1 px-2 py-1 text-sm rounded transition"
          title="Connect with QuickBooks"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 015.656 0l4 4a4 4 0 11-5.656 5.656l-1.102-1.101" />
          </svg>
          Connect QuickBooks
        </button>
        <button
          @click="fetchRevenueData" 
          :disabled="isLoading"
          class="glass-button flex items-center gap-1 px-2 py-1 text-sm rounded transition"
        >
          <svg v-if="!isLoading" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span v-if="isLoading" class="animate-spin h-3 w-3 border-2 border-white border-t-purple-400 rounded-full"></span>
          {{ isLoading ? 'Loading...' : 'Refresh' }}
        </button>
        <button
          @click="showDebug = !showDebug"
          class="glass-button flex items-center gap-1 px-2 py-1 text-sm rounded transition"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          <span>{{ showDebug ? 'Hide Debug' : 'Show Debug' }}</span>
        </button>
      </div>
    </div>

    <!-- Summary when collapsed -->
    <div v-if="isCollapsed && !isLoading" class="glass-card p-3 rounded-lg">
      <div class="flex justify-between items-center">
        <div class="flex items-center gap-4">
          <div>
            <span class="text-gray-400 text-xs">Last 30 Days</span>
            <p class="text-purple-400 text-lg font-bold">{{ formatCurrency(revenueData.last30DaysRevenue) }}</p>
          </div>
          <div>
            <span class="text-gray-400 text-xs">Growth</span>
            <p :class="[
              'text-lg font-bold', 
              revenueData.comparedToLastMonth > 0 ? 'text-green-500' : 'text-red-500'
            ]">
              {{ revenueData.comparedToLastMonth > 0 ? '+' : '' }}{{ revenueData.comparedToLastMonth }}%
            </p>
          </div>
        </div>
        <p class="text-gray-400 text-xs">Click to expand for full details</p>
      </div>
    </div>
    
    <!-- Debug panel - always visible regardless of collapse state -->
    <div v-if="showDebug" class="glass-debug p-4 rounded-lg mb-4 overflow-auto max-h-64 text-sm font-mono">
      <h3 class="text-purple-400 mb-2">Debug Information:</h3>
      <div v-if="debugInfo">
        <p>Completed Status: <span class="text-cyan-300">{{ debugInfo.completedStatus }}</span></p>
        <p>Date Column: <span class="text-cyan-300">{{ debugInfo.completedDateColumn }}</span></p>
        <p>Available Statuses: <span class="text-cyan-300">{{ debugInfo.availableStatuses?.join(', ') }}</span></p>
        <p>Current Month Revenue: <span class="text-cyan-300">{{ formatCurrency(debugInfo.currentMonthRevenue) }}</span></p>
        <p>Last Month Revenue: <span class="text-cyan-300">{{ formatCurrency(debugInfo.lastMonthRevenue) }}</span></p>
      </div>
      <div v-else>No debug information available</div>
      
      <!-- Internet Speed Test Section -->
      <div class="mt-4 border-t border-gray-700 pt-4">
        <h3 class="text-purple-400 mb-2">Internet Speed Test:</h3>
        <button 
          @click="testInternetSpeed" 
          :disabled="isTestingSpeed"
          class="glass-button px-3 py-1 rounded text-white mb-2 flex items-center gap-1"
        >
          <span v-if="isTestingSpeed" class="inline-block w-3 h-3 rounded-full border-2 border-white border-t-transparent animate-spin mr-1"></span>
          {{ isTestingSpeed ? 'Testing...' : 'Test Connection Speed' }}
        </button>
        
        <div v-if="speedTestResults" class="mt-2">
          <div v-if="speedTestResults.error" class="text-red-400">
            Error: {{ speedTestResults.error }}
          </div>
          <div v-else class="grid grid-cols-2 gap-2">
            <div>
                <p>Download Speed: <span class="text-cyan-300 font-bold">{{ 
                typeof speedTestResults.speedMbps === 'number' && !isNaN(speedTestResults.speedMbps) && speedTestResults.speedMbps > 0
                  ? speedTestResults.speedMbps.toFixed(2) 
                  : speedTestResults.speedMbps === 0 ? '< 0.01' : '0.00'
                }} Mbps</span></p>
              <p>Latency: <span class="text-cyan-300 font-bold">{{ 
                typeof speedTestResults.latency === 'number' && !isNaN(speedTestResults.latency) 
                  ? speedTestResults.latency.toFixed(0) 
                  : '0' 
              }} ms</span></p>
            </div>
            <div>
              <p>Data Transferred: <span class="text-cyan-300">{{ (speedTestResults.fileSize / 1024).toFixed(2) }} KB</span></p>
              <p>Test Duration: <span class="text-cyan-300">{{ speedTestResults.duration.toFixed(0) }} ms</span></p>
              <p v-if="speedTestResults.iterations">Files Downloaded: <span class="text-cyan-300">{{ speedTestResults.iterations }}</span></p>
              <p v-if="speedTestResults.method">Method: <span class="text-cyan-300">{{ speedTestResults.method }}</span></p>
              <p>Timestamp: <span class="text-cyan-300">{{ new Date(speedTestResults.timestamp).toLocaleTimeString() }}</span></p>
            </div>
          </div>
        </div>
      </div>
      
      <h3 class="text-purple-400 mt-4 mb-2">Quick Check:</h3>
      <div>
        <a href="/debug/work-orders" target="_blank" class="text-cyan-300 underline">
          Check Work Orders Data
        </a>
      </div>
    </div>

    <!-- Content section with transition -->
    <transition
      name="collapse"
      @enter="el => el.style.height = el.scrollHeight + 'px'"
      @leave="el => el.style.height = '0'"
    >
      <div v-if="!isCollapsed" class="content-wrapper">
        <!-- Error message display -->
        <div v-if="errorMessage" class="glass-error p-4 rounded-lg mb-4">
          <h3 class="font-bold mb-1">Error Loading Data</h3>
          <p>{{ errorMessage }}</p>
          <div class="mt-2">
            <button 
              @click="fetchRevenueData" 
              class="glass-error-button px-3 py-1 rounded-md text-sm"
            >
              Retry
            </button>
          </div>
        </div>

        <!-- Loading indicator -->
        <div v-if="isLoading" class="flex justify-center items-center h-64">
          <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-purple-400"></div>
        </div>

        <div v-else>
          <!-- Recent time period stats cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="glass-card p-4 rounded-lg">
              <p class="text-gray-400 text-sm font-medium">Last 7 Days</p>
              <p class="text-purple-400 text-2xl font-bold">{{ formatCurrency(revenueData.last7DaysRevenue) }}</p>
            </div>

            <div class="glass-card p-4 rounded-lg">
              <p class="text-gray-400 text-sm font-medium">Last 30 Days</p>
              <p class="text-purple-400 text-2xl font-bold">{{ formatCurrency(revenueData.last30DaysRevenue) }}</p>
            </div>
            
            <div class="glass-card p-4 rounded-lg">
              <p class="text-gray-400 text-sm font-medium">Total Revenue</p>
              <p class="text-purple-400 text-2xl font-bold">{{ formatCurrency(revenueData.totalRevenue) }}</p>
            </div>

            <div class="glass-card p-4 rounded-lg">
              <p class="text-gray-400 text-sm font-medium">Growth vs. Last Month</p>
              <div class="flex items-center">
                <span :class="[
                  'text-xl font-bold', 
                  revenueData.comparedToLastMonth > 0 ? 'text-green-500' : 'text-red-500'
                ]">
                  {{ revenueData.comparedToLastMonth > 0 ? '+' : '' }}{{ revenueData.comparedToLastMonth }}%
                </span>
              </div>
            </div>
          </div>

          <!-- Chart and additional stats grid layout -->
          <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <!-- Chart takes up 3/4 of space on large screens -->
            <div class="lg:col-span-3 h-64 glass-card p-3 rounded-lg">
              <Line 
                v-if="revenueData.monthly.length > 0" 
                :data="chartData" 
                :options="chartOptions" 
                ref="chartRef"
              />
            </div>

            <!-- Stats column takes up 1/4 of space -->
            <div class="lg:col-span-1 flex flex-col gap-4">
              <div class="glass-card p-4 rounded-lg">
                <p class="text-gray-400 text-sm font-medium">Year Over Year</p>
                <div class="flex items-center">
                  <span :class="[
                    'text-xl font-bold', 
                    revenueData.comparedToLastYear > 0 ? 'text-green-500' : 'text-red-500'
                  ]">
                    {{ revenueData.comparedToLastYear > 0 ? '+' : '' }}{{ revenueData.comparedToLastYear }}%
                  </span>
                </div>
              </div>

              <div class="glass-card p-4 rounded-lg">
                <p class="text-gray-400 text-sm font-medium">Next Month Forecast</p>
                <p class="text-cyan-400 text-xl font-bold">{{ formatCurrency(revenueData.forecastNextMonth) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
/* Glass Morphism Styles */
.glass-container {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 16px;
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
  transition: all 0.3s ease;
}

.glass-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.18);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
  transition: all 0.3s ease;
}

.glass-card:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-3px);
  box-shadow: 0 12px 32px 0 rgba(31, 38, 135, 0.2);
}

.glass-button {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.8);
}

.glass-button:hover {
  background: rgba(139, 92, 246, 0.3); /* Purple-like color with transparency */
  border-color: rgba(139, 92, 246, 0.5);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
}

.glass-debug {
  background: rgba(30, 30, 30, 0.7);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(139, 92, 246, 0.2);
}

.glass-error {
  background: rgba(220, 38, 38, 0.2);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(220, 38, 38, 0.3);
}

.glass-error-button {
  background: rgba(220, 38, 38, 0.3);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(220, 38, 38, 0.4);
  color: white;
}

.glass-error-button:hover {
  background: rgba(220, 38, 38, 0.5);
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  .glass-container {
    background: rgba(30, 30, 30, 0.3);
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.25);
  }
  
  .glass-card {
    background: rgba(30, 30, 30, 0.3);
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.25);
  }
  
  .glass-card:hover {
    background: rgba(40, 40, 40, 0.4);
  }
  
  .glass-button {
    background: rgba(30, 30, 30, 0.4);
    border-color: rgba(255, 255, 255, 0.1);
  }
  
  .glass-button:hover {
    background: rgba(139, 92, 246, 0.25);
    border-color: rgba(139, 92, 246, 0.4);
  }
}

/* Collapse animation styles */
.content-wrapper {
  overflow: hidden;
}

.collapse-enter-active,
.collapse-leave-active {
  transition: height 0.3s ease-in-out, opacity 0.3s ease;
  overflow: hidden;
}

.collapse-enter-from,
.collapse-leave-to {
  height: 0;
  opacity: 0;
}
</style>
