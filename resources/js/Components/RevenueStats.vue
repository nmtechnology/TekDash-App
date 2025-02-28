<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js';

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

async function fetchRevenueData() {
  try {
    isLoading.value = true;
    const response = await axios.get('/revenue-stats');
    if (response.data) {
      revenueData.value = response.data;
      console.log('Revenue data loaded:', revenueData.value);
      if (response.data._debug) {
        debugInfo.value = response.data._debug;
        delete revenueData.value._debug;
      }
    }
  } catch (error) {
    console.error('Error fetching revenue data:', error);
    alert('Failed to load revenue data. Please check console for details.');
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
</script>

<template>
  <div class="revenue-stats-container bg-white/10 dark:bg-gray-800/70 overflow-hidden shadow-lg rounded-lg p-5 mb-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Revenue Statistics</h2>
      <div class="flex gap-2">
        <button
          @click="showDebug = !showDebug"
          class="flex items-center gap-1 px-2 py-1 text-sm btn outline text-gray-400 rounded hover:bg-gray-700 transition"
        >
          Debug
        </button>
        <button
          @click="fetchRevenueData" 
          class="flex items-center gap-1 px-2 py-1 text-sm btn outline text-lime-400 rounded hover:bg-lime-500 hover:text-gray-900 transition"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Debug panel -->
    <div v-if="showDebug" class="bg-gray-900 text-gray-200 p-4 rounded-lg mb-4 overflow-auto max-h-64 text-sm font-mono">
      <h3 class="text-lime-400 mb-2">Debug Information:</h3>
      <div v-if="debugInfo">
        <p>Completed Status: <span class="text-cyan-300">{{ debugInfo.completedStatus }}</span></p>
        <p>Date Column: <span class="text-cyan-300">{{ debugInfo.completedDateColumn }}</span></p>
        <p>Available Statuses: <span class="text-cyan-300">{{ debugInfo.availableStatuses?.join(', ') }}</span></p>
        <p>Current Month Revenue: <span class="text-cyan-300">{{ formatCurrency(debugInfo.currentMonthRevenue) }}</span></p>
        <p>Last Month Revenue: <span class="text-cyan-300">{{ formatCurrency(debugInfo.lastMonthRevenue) }}</span></p>
      </div>
      <div v-else>No debug information available</div>
      
      <h3 class="text-lime-400 mt-4 mb-2">Quick Check:</h3>
      <div>
        <a href="/debug/work-orders" target="_blank" class="text-cyan-300 underline">
          Check Work Orders Data
        </a>
      </div>
    </div>

    <!-- Loading indicator -->
    <div v-if="isLoading" class="flex justify-center items-center h-64">
      <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-lime-400"></div>
    </div>

    <div v-else>
      <!-- Recent time period stats cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card bg-gray-900/50 p-4 rounded-lg">
          <p class="text-gray-400 text-sm font-medium">Last 7 Days</p>
          <p class="text-lime-400 text-2xl font-bold">{{ formatCurrency(revenueData.last7DaysRevenue) }}</p>
        </div>

        <div class="stat-card bg-gray-900/50 p-4 rounded-lg">
          <p class="text-gray-400 text-sm font-medium">Last 30 Days</p>
          <p class="text-lime-400 text-2xl font-bold">{{ formatCurrency(revenueData.last30DaysRevenue) }}</p>
        </div>
        
        <div class="stat-card bg-gray-900/50 p-4 rounded-lg">
          <p class="text-gray-400 text-sm font-medium">Total Revenue</p>
          <p class="text-lime-400 text-2xl font-bold">{{ formatCurrency(revenueData.totalRevenue) }}</p>
        </div>

        <div class="stat-card bg-gray-900/50 p-4 rounded-lg">
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
        <div class="lg:col-span-3 h-64 bg-gray-900/50 p-3 rounded-lg">
          <Line v-if="revenueData.monthly.length > 0" :data="chartData" :options="chartOptions" />
        </div>

        <!-- Stats column takes up 1/4 of space -->
        <div class="lg:col-span-1 flex flex-col gap-4">
          <div class="stat-card bg-gray-900/50 p-4 rounded-lg">
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

          <div class="stat-card bg-gray-900/50 p-4 rounded-lg">
            <p class="text-gray-400 text-sm font-medium">Next Month Forecast</p>
            <p class="text-cyan-400 text-xl font-bold">{{ formatCurrency(revenueData.forecastNextMonth) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.stat-card {
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
</style>
