<template>
  <div class="glass-container p-4 rounded-xl mb-4">
    <div class="flex justify-between items-center mb-4">
      <div class="flex items-center gap-2">
        <button
          v-if="collapsable"
          @click="toggleCollapse"
          class="glass-button flex items-center justify-center w-6 h-6 rounded-full transition"
          :title="isCollapsed ? 'Expand' : 'Collapse'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': isCollapsed }" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
        <h2 class="text-xl font-semibold text-gray-800 dark:text-purple-300" v-if="title">{{ title }}</h2>
      </div>
      
      <div v-if="refreshFunction" class="flex gap-2">
        <button
          @click="refreshData"
          :disabled="isLoading"
          class="glass-button flex items-center gap-1 px-2 py-1 text-sm rounded transition"
        >
          <svg v-if="!isLoading" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span v-if="isLoading" class="animate-spin h-3 w-3 border-2 border-white border-t-purple-400 rounded-full"></span>
          {{ isLoading ? 'Loading...' : 'Refresh' }}
        </button>
      </div>
    </div>

    <!-- Error message -->
    <div v-if="errorMessage" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded dark:bg-red-900/20 dark:text-red-300">
      <div class="flex items-center">
        <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        <p>{{ errorMessage }}</p>
      </div>
      <button @click="errorMessage = ''" class="text-sm underline mt-1">Dismiss</button>
    </div>

    <!-- Summary when collapsed -->
    <div v-if="isCollapsed && collapsable" class="glass-card p-3 rounded-lg">
      <div class="flex justify-between items-center">
        <div class="flex items-center gap-4">
          <div>
            <span class="text-gray-400 text-xs">{{ stats[0]?.name || 'Stats' }}</span>
            <p class="text-purple-400 text-lg font-bold">{{ stats[0]?.value || '0' }}</p>
          </div>
          <div v-if="stats.length > 1">
            <span class="text-gray-400 text-xs">{{ stats[1]?.name || 'Stats' }}</span>
            <p class="text-lg font-bold" :class="stats[1]?.changeType === 'negative' ? 'text-red-500' : 'text-green-500'">
              {{ stats[1]?.value || '0' }}
            </p>
          </div>
        </div>
        <p class="text-gray-400 text-xs">Click to expand for full details</p>
      </div>
    </div>
    
    <!-- Content section with transition -->
    <transition
      name="collapse"
      @enter="el => el.style.height = el.scrollHeight + 'px'"
      @leave="el => el.style.height = '0'"
    >
      <div v-if="!isCollapsed || !collapsable" class="content-wrapper">
        <!-- Loading indicator -->
        <div v-if="isLoading" class="flex justify-center items-center py-10">
          <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-purple-400"></div>
        </div>
        
        <dl v-else class="mx-auto grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div 
            v-for="stat in combinedStats" 
            :key="stat.name" 
            class="glass-card flex flex-wrap items-baseline justify-between gap-x-4 gap-y-2 px-6 py-8 rounded-xl transition-all duration-300 hover:shadow-lg"
            :title="stat.tooltip"  
          >
            <dt class="text-sm font-medium text-gray-600 dark:text-purple-300 flex items-center">
              {{ stat.name }}
            </dt>
            <dd :class="[stat.changeType === 'negative' ? 'text-rose-500 dark:text-rose-400' : 'text-emerald-500 dark:text-emerald-400', 'text-xs font-medium']">{{ stat.change }}</dd>
            <dd class="w-full flex-none text-3xl font-medium tracking-tight text-gray-900 dark:text-white">{{ stat.value }}</dd>
          </div>
        </dl>
      </div>
    </transition>
  </div>
</template>
    
<script setup>
import { ref, computed } from 'vue';

// Define props with new collapsable and refresh options
const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  stats: {
    type: Array,
    required: true,
    default: () => [
      { name: 'Revenue', value: '$0.00', change: '0%', changeType: 'neutral' },
      { name: 'Completed Work Orders', value: '0', change: '0%', changeType: 'neutral' },
      { name: 'Pending Work Orders', value: '0', change: '0%', changeType: 'neutral' },
      { name: 'Archived Work Orders', value: '0', change: '0%', changeType: 'neutral' },
      { name: 'Archived Orders Value', value: '$0.00', change: '0%', changeType: 'neutral' },
      { name: 'Average Price', value: '$0.00', change: '0%', changeType: 'neutral' },
    ]
  },
  collapsable: {
    type: Boolean,
    default: false
  },
  refreshFunction: {
    type: Function,
    default: null
  }
});

// State for collapse functionality
const isCollapsed = ref(false);
const isLoading = ref(false);
const errorMessage = ref('');

// Function to toggle collapsed state
function toggleCollapse() {
  isCollapsed.value = !isCollapsed.value;
}

// Function to handle refresh if provided
async function refreshData() {
  if (!props.refreshFunction) return;
  
  try {
    isLoading.value = true;
    errorMessage.value = '';
    await props.refreshFunction();
  } catch (error) {
    console.error('Error refreshing stats:', error);
    // Handle authentication errors specifically
    if (error.response?.status === 401 || error.response?.status === 403) {
      errorMessage.value = error.response?.data?.message || 
                         'Authentication error. Please check your credentials or try logging in again.';
    } else if (error.message?.includes('redirect_uri')) {
      errorMessage.value = 'Invalid redirect URI. Please check your application configuration.';
    } else {
      errorMessage.value = error.message || 'An error occurred while refreshing data.';
    }
  } finally {
    isLoading.value = false;
  }
}

// Computed property that combines revenue with archived orders value
const combinedStats = computed(() => {
  try {
    // Create a deep copy of stats to avoid mutation issues
    let modifiedStats = JSON.parse(JSON.stringify(props.stats));
    
    const extractValue = (str) => {
      if (!str) return 0;
      // Remove currency symbols and separators
      const numericValue = str.toString()
        .replace(/[$€£¥]/g, '')
        .replace(/,/g, '')
        .replace(/\s/g, '')
        .trim();
      
      const parsed = parseFloat(numericValue);
      return isNaN(parsed) ? 0 : parsed;
    };
    
    // Update pending work orders count
    const pendingItem = modifiedStats.find(s => s.name === 'Pending Work Orders');
    if (pendingItem) {
      // Find work orders with relevant statuses
      const scheduledItem = props.stats.find(s => s.name === 'Scheduled Work Orders')?.value || '0';
      const inProgressItem = props.stats.find(s => s.name === 'In Progress Work Orders')?.value || '0';
      const partNeededItem = props.stats.find(s => s.name === 'Part Needed Work Orders')?.value || '0';
      
      // Sum all pending statuses
      const totalPending = 
        extractValue(scheduledItem) +
        extractValue(inProgressItem) +
        extractValue(partNeededItem);
      
      // Update the pending work orders value
      const pendingIndex = modifiedStats.findIndex(s => s.name === 'Pending Work Orders');
      if (pendingIndex !== -1) {
        modifiedStats[pendingIndex] = {
          ...modifiedStats[pendingIndex],
          value: totalPending.toString(),
          tooltip: `Scheduled: ${scheduledItem}, In Progress: ${inProgressItem}, Part Needed: ${partNeededItem}`
        };
      }
    }
    
    // Handle revenue calculations as before
    const revenueItem = modifiedStats.find(s => s.name === 'Revenue' || s.name.includes('Revenue'));
    const archivedValueItem = modifiedStats.find(s => s.name === 'Archived Orders Value');
    
    if (revenueItem && archivedValueItem) {
      const revenueValue = extractValue(revenueItem.value);
      const archivedValue = extractValue(archivedValueItem.value);
      const totalValue = revenueValue + archivedValue;
      
      const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      });
      
      const revenueIndex = modifiedStats.findIndex(s => s.name === revenueItem.name);
      if (revenueIndex !== -1) {
        modifiedStats[revenueIndex] = {
          ...modifiedStats[revenueIndex],
          name: 'Total Revenue',
          value: formatter.format(totalValue),
          originalValue: revenueItem.value,
          tooltip: `Active: ${revenueItem.value} + Archived: ${archivedValueItem.value}`
        };
      }
    }
    
    console.log('Modified stats:', modifiedStats);
    return modifiedStats;
    
  } catch (error) {
    console.error("Error calculating combined stats:", error);
    return props.stats;
  }
});
</script>

<style scoped>
.glass-container {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.18);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
}

.glass-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.18);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
}

.glass-card:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-3px);
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

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  .glass-container {
    background: rgba(30, 30, 30, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.25);
  }
  
  .glass-card {
    background: rgba(30, 30, 30, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.08);
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