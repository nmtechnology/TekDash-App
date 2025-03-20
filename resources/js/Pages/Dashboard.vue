<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import Stats from '@/Components/Stats.vue';
import RevenueStats from '@/Components/RevenueStats.vue';
import CurrentTime from '@/Components/CurrentTime.vue';
import TeamDropdown from '@/Components/TeamDropdown.vue';
import Search from '@/Components/Search.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

// Close search results when clicking outside
function handleClickOutside(event) {
    if (!event.target.closest('.search-container')) {
        showSearchResults.value = false;
    }
}

defineProps({
    title: String,
});

// Get page object using the usePage composable
const page = usePage();

// Search functionality variables
const searchQuery = ref('');
const workOrders = ref([]);
const isSearching = ref(false);
const showSearchResults = ref(false);
const selectedWorkOrder = ref(null);
const showWorkOrderModal = ref(false);
const showingNavigationDropdown = ref(false);

// Work order search functionality with better error handling
let searchTimeout;
function handleSearch(query) {
    searchQuery.value = query;
    clearTimeout(searchTimeout);
    
    if (query.length < 2) {
        workOrders.value = [];
        showSearchResults.value = false;
        return;
    }
    
    isSearching.value = true;
    showSearchResults.value = true;
    
    // Use a debounce to avoid too many API calls while typing
    searchTimeout = setTimeout(async () => {
        try {
            // Connect to your actual Laravel backend endpoint for searching work orders
            const response = await axios.get('/search-work-orders', {
                params: { query }
            });
            
            console.log('Search response:', response.data);
            
            // Update the workOrders with the data from your actual database
            workOrders.value = response.data;
            
            // If it's an error response, handle it appropriately
            if (response.data.error) {
                console.error('Search error:', response.data.error);
                workOrders.value = [];
            }
        } catch (error) {
            console.error('Error searching work orders:', error);
            workOrders.value = [];
            
            // Show a user-friendly error message
            alert('There was an error performing your search. Please try again later.');
        } finally {
            isSearching.value = false;
        }
    }, 300);
}

// View work order details with better error handling
function openWorkOrderModal(workOrderId) {
    showSearchResults.value = false;
    isSearching.value = true;
    
    // Get the complete work order details from your backend
    axios.get(`/work-orders/${workOrderId}/details`)
        .then(response => {
            console.log('Work order details response:', response.data);
            selectedWorkOrder.value = response.data;
            showWorkOrderModal.value = true;
        })
        .catch(error => {
            console.error('Error fetching work order details:', error);
            
            if (error.response && error.response.status === 404) {
                alert(`Work order #${workOrderId} could not be found.`);
            } else {
                alert('Unable to load work order details. Please try again.');
            }
        })
        .finally(() => {
            isSearching.value = false;
        });
}

function closeModal() {
    showWorkOrderModal.value = false;
    selectedWorkOrder.value = null;
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Define reactive state
const statsData = ref([]);
const isLoading = ref(true);
const filteredData = ref([]);

// Fetch stats from the backend
async function fetchStats() {
  try {
    isLoading.value = true;
    const response = await axios.get('/work-order-stats');
    
    // Check that the data is valid
    if (response.data && Array.isArray(response.data) && response.data.length > 0) {
      statsData.value = response.data;
      filteredData.value = response.data;
      console.log('Stats data loaded successfully:', response.data);
    } else {
      console.error('Stats data format is incorrect:', response.data);
      // Use fallback data
      const fallback = getFallbackStats();
      statsData.value = fallback;
      filteredData.value = fallback;
    }
  } catch (error) {
    console.error('Error fetching stats:', error);
    // Provide fallback data if there's an error
    const fallback = getFallbackStats();
    statsData.value = fallback;
    filteredData.value = fallback;
  } finally {
    isLoading.value = false;
  }
}

// Function to provide fallback stats data
function getFallbackStats() {
  return [
    { name: 'Total Revenue', value: '$0.00', change: '0%', changeType: 'neutral' },
    { name: 'Completed Orders', value: '0', change: '0%', changeType: 'neutral' },
    { name: 'Pending Orders', value: '0', change: '0%', changeType: 'neutral' },
    { name: 'Average Price', value: '$0.00', change: '0%', changeType: 'neutral' },
  ];
}

// Load stats when component mounts
onMounted(() => {
  fetchStats();
});
</script>

<template>
  <AppLayout title="Dashboard">
    <template #header>
      <div class="fixed mt-16 top-0 left-0 right-0 z-10 backdrop-blur-md bg-white/50 dark:bg-gray-800/60 shadow">
        <div class="max-w-7xl mx-auto py-2 px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-lime-400 leading-tight">
              Dashboard <CurrentTime />
              
              <!-- Search Component with error handling -->
                        <div class="p-3 search-container relative">
                            <Search placeholder="Search work orders..." @search="handleSearch" />
                            
                            <!-- Search Results Dropdown with error state -->
                            <div v-if="showSearchResults" 
                                 class="absolute mt-1 w-full bg-white dark:bg-gray-800 shadow-lg rounded-md overflow-hidden z-50">
                                <div v-if="isSearching" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                    Searching work orders...
                                </div>
                                <ul v-else-if="workOrders.length > 0" class="max-h-80 overflow-y-auto">
                                    <li v-for="order in workOrders" :key="order.id" 
                                        class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b dark:border-gray-700 last:border-b-0"
                                        @click="openWorkOrderModal(order.id)">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">
                                                    {{ order.title || `Work Order #${order.id}` }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ order.customer_name || order.customer_id || 'No customer info' }}
                                                </div>
                                            </div>
                                            <div>
                                                <span v-if="order.status" class="text-xs px-2 py-1 rounded" 
                                                    :class="{
                                                        'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100': 
                                                            order.status.toLowerCase().includes('complete'),
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100': 
                                                            order.status.toLowerCase().includes('scheduled'),
                                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100': 
                                                            order.status.toLowerCase().includes('progress'),
                                                        'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100': 
                                                            order.status.toLowerCase().includes('cancel'),
                                                        'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100': 
                                                            order.status.toLowerCase().includes('part') || order.status.toLowerCase().includes('return')
                                                    }">
                                                    {{ order.status }}
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <div v-else-if="searchQuery.length >= 2" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    No work orders found matching "{{ searchQuery }}"
                                </div>
                            </div>
                        </div>
            </h2>
            <TeamDropdown />  <!-- Add this line -->
          </div>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white/70 dark:bg-gray-800/70 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <!-- Other dashboard content -->
          <div class="pt-16"> <!-- Adjusted padding to account for the fixed nav -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
              <!-- Loading state -->
              <div v-if="isLoading" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500 dark:border-lime-400"></div>
              </div>
              
              <!-- Stats component -->
              <div v-else>
                <Stats :stats="filteredData" />
                <RevenueStats />
              </div>
              
              <div class="flex justify-between items-start mb-3 mt-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white"></h1>
              </div>
              
              <div class="relative bg-transparent opacity-75 overflow-hidden shadow-xl sm:rounded-lg bg-cover bg-center bg-no-repeat" 
                  style="background-image: url('https://images.unsplash.com/photo-1553152531-b98a2fc8d3bf?q=80&w=3731&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');">
                <div class="absolute inset-0 bg-black opacity-50"></div> <!-- Overlay -->
                <div class="relative z-10">
                  <Welcome />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>

   <!-- Work Order Modal - Displays data from your actual database -->
   <div v-if="showWorkOrderModal && selectedWorkOrder" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-800 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <!-- Header with close button -->
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                                        Work Order #{{ selectedWorkOrder.id }}
                                    </h3>
                                    <button @click="closeModal" class="text-gray-400 hover:text-gray-200">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <!-- Work order details from your database -->
                                <div class="text-gray-300 space-y-3">
                                    <div v-if="selectedWorkOrder.title" class="mb-2">
                                        <h4 class="text-lg font-medium text-white">{{ selectedWorkOrder.title }}</h4>
                                    </div>
                                    
                                    <div v-if="selectedWorkOrder.status" class="flex justify-between">
                                        <div>
                                            <p class="text-sm text-gray-400">Status:</p>
                                            <span class="inline-flex px-2 py-1 text-xs rounded" 
                                                  :class="{
                                                    'bg-green-800 text-green-100': selectedWorkOrder.status.toLowerCase().includes('complete'),
                                                    'bg-blue-800 text-blue-100': selectedWorkOrder.status.toLowerCase().includes('scheduled'),
                                                    'bg-yellow-800 text-yellow-100': selectedWorkOrder.status.toLowerCase().includes('progress'),
                                                    'bg-red-800 text-red-100': selectedWorkOrder.status.toLowerCase().includes('cancel'),
                                                    'bg-purple-800 text-purple-100': selectedWorkOrder.status.toLowerCase().includes('part') || 
                                                                                    selectedWorkOrder.status.toLowerCase().includes('return')
                                                  }">
                                                {{ selectedWorkOrder.status }}
                                            </span>
                                        </div>
                                        
                                        <div v-if="selectedWorkOrder.price">
                                            <p class="text-sm text-gray-400">Price:</p>
                                            <span>${{ selectedWorkOrder.price }}</span>
                                        </div>
                                    </div>
                                    
                                    <div v-if="selectedWorkOrder.customer_id || selectedWorkOrder.customer_name" class="mt-4">
                                        <p class="text-sm text-gray-400">Customer:</p>
                                        <p v-if="selectedWorkOrder.customer_name">{{ selectedWorkOrder.customer_name }}</p>
                                        <p v-if="selectedWorkOrder.customer_id" class="text-sm">ID: {{ selectedWorkOrder.customer_id }}</p>
                                    </div>
                                    
                                    <div v-if="selectedWorkOrder.created_at || selectedWorkOrder.date">
                                        <p class="text-sm text-gray-400">Date:</p>
                                        <p>{{ new Date(selectedWorkOrder.created_at || selectedWorkOrder.date).toLocaleString() }}</p>
                                    </div>
                                    
                                    <div v-if="selectedWorkOrder.description" class="mt-4">
                                        <p class="text-sm text-gray-400">Description:</p>
                                        <p class="whitespace-pre-line mt-1 text-sm bg-gray-800 p-3 rounded-md">
                                            {{ selectedWorkOrder.description }}
                                        </p>
                                    </div>
                                    
                                    <!-- Display any additional fields from your database -->
                                    <template v-for="(value, key) in selectedWorkOrder" :key="key">
                                        <div v-if="!['id', 'title', 'status', 'price', 'customer_id', 'customer_name', 'created_at', 'date', 'description'].includes(key) && value">
                                            <p class="text-sm text-gray-400">{{ key.charAt(0).toUpperCase() + key.slice(1).replace('_', ' ') }}:</p>
                                            <p>{{ value }}</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal footer -->
                    <div class="bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <Link :href="`/work-orders/${selectedWorkOrder.id}`" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                            View Full Details
                        </Link>
                        <button @click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-700 shadow-sm px-4 py-2 bg-gray-700 text-base font-medium text-gray-300 hover:bg-gray-600 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
</template>

<style scoped>
/* Additional styles if needed */
.search-container {
    width: 300px;
    max-width: 100%;
}

@media (max-width: 640px) {
    .search-container {
        width: 100%;
    }
}

/* Add specific styling for team dropdown */
.dark .dropdown-content {
    background-color: #1f2937;
    border-color: #374151;
}
</style>