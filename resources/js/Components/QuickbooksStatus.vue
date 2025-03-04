<template>
  <div class="mb-4">
    <div class="flex items-center justify-between glossy-section p-3">
      <div class="flex items-center">
        <svg class="h-6 w-6 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 24 24">
          <path d="M21.6 0H2.4C1.08 0 0 1.08 0 2.4v19.2C0 22.92 1.08 24 2.4 24h19.2c1.32 0 2.4-1.08 2.4-2.4V2.4C24 1.08 22.92 0 21.6 0z" fill="#2CA01C"/>
          <path d="M21.6 0H2.4C1.08 0 0 1.08 0 2.4v19.2C0 22.92 1.08 24 2.4 24h19.2c1.32 0 2.4-1.08 2.4-2.4V2.4C24 1.08 22.92 0 21.6 0zM7.68 18.24c0 .6-.36.96-.96.96H4.92c-.6 0-.96-.36-.96-.96v-1.56c0-.6.36-.96.96-.96h1.8c.6 0 .96.36.96.96v1.56zm4.44 0c0 .6-.36.96-.96.96H9.36c-.6 0-.96-.36-.96-.96V7.8c0-.6.36-.96.96-.96h1.8c.6 0 .96.36.96.96v10.44zm4.44 0c0 .6-.36.96-.96.96h-1.8c-.6 0-.96-.36-.96-.96v-5.52c0-.6.36-.96.96-.96h1.8c.6 0 .96.36.96.96v5.52zm4.44 0c0 .6-.36.96-.96.96h-1.8c-.6 0-.96-.36-.96-.96v-8.28c0-.6.36-.96.96-.96h1.8c.6 0 .96.36.96.96v8.28z" fill="white"/>
        </svg>
        <div>
          <h3 class="text-lime-400 text-sm font-medium">QuickBooks Status</h3>
          <p v-if="isLoading" class="text-xs text-gray-400">Checking connection...</p>
          <p v-else-if="status.connected" class="text-xs text-gray-400">
            Connected to {{ status.company }}
          </p>
          <p v-else class="text-xs text-red-400">
            Not connected
          </p>
        </div>
      </div>
      
      <div>
        <span v-if="isLoading" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300">
          <svg class="animate-spin -ml-1 mr-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Checking
        </span>
        <span v-else-if="status.connected" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-300">
          Connected
        </span>
        <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-900 text-red-300">
          Disconnected
        </span>
      </div>
      
      <div>
        <a 
          v-if="!status.connected" 
          href="/quickbooks/connect" 
          class="px-3 py-1 text-xs rounded-md bg-blue-800 hover:bg-blue-700 text-white"
        >
          Connect
        </a>
        <button 
          v-else
          @click="reconnect" 
          class="px-3 py-1 text-xs rounded-md bg-gray-700 hover:bg-gray-600 text-white"
        >
          Reconnect
        </button>
      </div>
    </div>
    
    <div v-if="status.connected && status.expiresAt" class="text-xs text-gray-400 mt-1 text-center">
      Connection expires: {{ formatExpiration(status.expiresAt) }}
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import QuickbooksService from '@/Services/QuickbooksService';

export default {
  setup() {
    const status = ref({
      connected: false,
      company: '',
      expiresAt: null,
      message: ''
    });
    const isLoading = ref(true);
    
    // Check QuickBooks connection status on component mount
    onMounted(async () => {
      try {
        isLoading.value = true;
        status.value = await QuickbooksService.checkConnectionStatus();
      } catch (error) {
        console.error('Error checking QuickBooks status:', error);
        status.value = {
          connected: false,
          message: 'Error checking connection status'
        };
      } finally {
        isLoading.value = false;
      }
    });
    
    const reconnect = () => {
      window.location.href = '/quickbooks/connect';
    };
    
    const formatExpiration = (dateString) => {
      if (!dateString) return '';
      
      try {
        const date = new Date(dateString);
        if (isNaN(date)) return 'Invalid date';
        
        const now = new Date();
        const diff = date - now;
        
        // Convert to days
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (days < 0) return 'Expired';
        if (days === 0) return 'Today';
        if (days === 1) return 'Tomorrow';
        if (days < 30) return `In ${days} days`;
        
        return date.toLocaleDateString();
      } catch (e) {
        console.error('Date formatting error:', e);
        return dateString;
      }
    };
    
    return {
      status,
      isLoading,
      reconnect,
      formatExpiration
    };
  }
};
</script>
