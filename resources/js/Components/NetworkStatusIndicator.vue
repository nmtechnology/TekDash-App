<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import NetworkDiagnostics from '@/Utils/NetworkDiagnostics';

const isOnline = ref(navigator.onLine);
const isApiConnected = ref(null);
const showDetails = ref(false);
const diagnosticResults = ref(null);
const isRunningDiagnostics = ref(false);

// Update online/offline status
function updateOnlineStatus() {
  isOnline.value = navigator.onLine;
  
  // When we come back online, check API connectivity
  if (isOnline.value) {
    checkApiConnection();
  } else {
    isApiConnected.value = false;
  }
}

// Check API connection
async function checkApiConnection() {
  try {
    const response = await fetch('/api/health', { 
      method: 'GET',
      headers: {
        'Cache-Control': 'no-cache',
        'Accept': 'application/json'
      },
      cache: 'no-store',
      // Just wait 2 seconds maximum for this simple check
      signal: AbortSignal.timeout(2000)
    });
    
    isApiConnected.value = response.ok;
  } catch (error) {
    console.warn('API health check failed:', error);
    isApiConnected.value = false;
  }
}

// Run detailed diagnostics
async function runDiagnostics() {
  try {
    isRunningDiagnostics.value = true;
    diagnosticResults.value = await NetworkDiagnostics.runAll();
    showDetails.value = true;
  } catch (error) {
    console.error('Diagnostics failed:', error);
  } finally {
    isRunningDiagnostics.value = false;
  }
}

// Initial check on mount
onMounted(() => {
  window.addEventListener('online', updateOnlineStatus);
  window.addEventListener('offline', updateOnlineStatus);
  
  // Initial check
  checkApiConnection();
});

// Clean up event listeners
onBeforeUnmount(() => {
  window.removeEventListener('online', updateOnlineStatus);
  window.removeEventListener('offline', updateOnlineStatus);
});
</script>

<template>
  <div class="network-status-container">
    <!-- Status indicator -->
    <div 
      class="network-status-indicator" 
      @click="isApiConnected === false ? runDiagnostics() : null"
      :title="isApiConnected === false ? 'Click to diagnose connection issues' : ''"
    >
      <!-- Online/Connected -->
      <div v-if="isOnline && isApiConnected" class="status-dot bg-green-500"></div>
      
      <!-- Online but API unavailable -->
      <div v-else-if="isOnline && isApiConnected === false" class="status-dot bg-yellow-500"></div>
      
      <!-- Checking status -->
      <div v-else-if="isApiConnected === null" class="status-dot bg-gray-500"></div>
      
      <!-- Completely offline -->
      <div v-else class="status-dot bg-red-500"></div>
    </div>
    
    <!-- Diagnostics modal -->
    <div v-if="showDetails" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showDetails = false">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg max-w-md w-full max-h-[80vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium">Network Diagnostics</h3>
          <button @click="showDetails = false" class="text-gray-500 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div v-if="isRunningDiagnostics" class="text-center py-4">
          <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-2"></div>
          <p>Running diagnostics...</p>
        </div>
        
        <div v-else-if="diagnosticResults">
          <!-- Network Status Summary -->
          <div class="mb-4 p-3 rounded" :class="{'bg-green-100 dark:bg-green-900/20': isOnline && isApiConnected, 'bg-yellow-100 dark:bg-yellow-900/20': isOnline && !isApiConnected, 'bg-red-100 dark:bg-red-900/20': !isOnline}">
            <p class="font-medium">
              <span v-if="isOnline && isApiConnected">✅ Connected to the server</span>
              <span v-else-if="isOnline && !isApiConnected">⚠️ Connected to internet but server unreachable</span>
              <span v-else>❌ No internet connection</span>
            </p>
          </div>
          
          <!-- Test Results -->
          <div class="space-y-3">
            <div v-for="(test, index) in diagnosticResults.tests" :key="index" 
                class="border rounded p-2"
                :class="{
                  'border-green-300 dark:border-green-600': test.status === 'success',
                  'border-yellow-300 dark:border-yellow-600': test.status === 'warning',
                  'border-red-300 dark:border-red-600': test.status === 'error'
                }">
              <div class="flex items-center">
                <div class="status-dot mr-2" 
                    :class="{
                      'bg-green-500': test.status === 'success',
                      'bg-yellow-500': test.status === 'warning',
                      'bg-red-500': test.status === 'error'
                    }">
                </div>
                <div class="flex-1">
                  <p class="font-medium">{{ test.name }}</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400">{{ test.message }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="mt-4 border-t pt-4">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Troubleshooting Tips:</p>
            <ul class="text-sm list-disc pl-5 space-y-1">
              <li>Check your internet connection</li>
              <li>Try refreshing the page</li>
              <li>Clear browser cache and cookies</li>
              <li>Try logging out and back in</li>
              <li>Disable browser extensions that might interfere with requests</li>
            </ul>
          </div>
          
          <div class="mt-4 flex justify-between">
            <button 
              @click="runDiagnostics"
              class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            >
              Run Again
            </button>
            <button 
              @click="showDetails = false"
              class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
            >
              Close
            </button>
          </div>
        </div>
        
        <div v-else class="text-center py-4">
          <p>No diagnostic results available.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.network-status-container {
  position: relative;
}

.network-status-indicator {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.bg-green-500 {
  box-shadow: 0 0 5px rgba(0, 255, 0, 0.5);
}

.bg-yellow-500 {
  box-shadow: 0 0 5px rgba(255, 255, 0, 0.5);
}

.bg-red-500 {
  box-shadow: 0 0 5px rgba(255, 0, 0, 0.5);
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1); }
}
</style>
