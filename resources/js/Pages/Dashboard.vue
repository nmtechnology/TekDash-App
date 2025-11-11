<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import CurrentTime from '@/Components/CurrentTime.vue';
import TeamDropdown from '@/Components/TeamDropdown.vue';
import RevenueStats from '@/Components/RevenueStats.vue';
import Search from '@/Components/Search.vue';
import AddWorkOrder from '@/Pages/WorkOrders/AddWorkOrder.vue';
import AddCustomerButton from '@/Components/AddCustomerButton.vue';
import AddTechnicianButton from '@/Pages/Technicians/AddTechnicianButton.vue';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import axios from 'axios';

// Close search results when clicking outside
function handleClickOutside(event) {
    if (!event.target.closest('.search-container')) {
        showSearchResults.value = false;
    }
}

const props = defineProps({
    title: String,
    teams: {
        type: Array,
        default: () => []
    }
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
            const response = await axios.get('/api/search-work-orders', {
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

// User role-based actions
const user = usePage().props.auth.user;

const canManageWorkOrders = computed(() => user.isAdmin);
const canUpdateWorkOrders = computed(() => user.isAdmin || user.isTech);

// Add navigation methods
const navigateToWorkOrders = () => {
    router.visit(route('work-orders.index'))
}

// Update button click handlers
const createWorkOrder = () => {
    router.visit(route('work-orders.create'))
}

const viewWorkOrder = (id) => {
    router.visit(route('work-orders.show', id))
}

// Update openArchivedModal to use POST without a parameter
const openArchivedModal = () => {
    router.post(route('work-orders.archive', { workOrder: 0 }));
};

// Add this reference to track the AddWorkOrder component
const addWorkOrderRef = ref(null);

// Debug logs for the AddWorkOrder modal
watch(() => addWorkOrderRef.value?.showModal, (newValue) => {
  if (newValue === true) {
    console.log('Dashboard: AddWorkOrder modal opened');
  } else if (newValue === false) {
    console.log('Dashboard: AddWorkOrder modal closed');
  }
}, { deep: true });
</script>

<template>
  <AppLayout>
    <template #header>
      <div class="fixed mt-[60px] sm:mt-[80px] top-0 left-0 right-0 z-10 glass-header">
        <div class="max-w-7xl mx-auto py-3 sm:py-4 px-4 sm:px-6 lg:px-8">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
              <h2 class="font-bold text-xl sm:text-2xl bg-gradient-to-r from-lime-400 to-cyan-400 bg-clip-text text-transparent leading-tight">
                Dashboard
              </h2>
              <CurrentTime class="text-gray-300 text-sm hidden sm:block" />
            </div>
            
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
              <TeamDropdown :teams="props.teams" />
              <AddWorkOrder ref="addWorkOrderRef" />
              <AddCustomerButton />
              <AddTechnicianButton />
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Main dashboard content must be a single root element -->
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black relative overflow-hidden">
      <!-- Animated Background Elements -->
      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-20 left-20 w-96 h-96 bg-lime-400/8 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div class="absolute top-40 right-20 w-80 h-80 bg-cyan-400/8 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-20 left-1/3 w-72 h-72 bg-purple-400/8 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
      </div>

      <div class="relative z-10 py-6 sm:py-12 mt-[80px] sm:mt-[100px]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <!-- Modern Glass Cards Layout -->
          <div class="grid gap-6 lg:gap-8">
            
            <!-- Stats Section -->
            <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10 shadow-2xl">
              <div class="text-center mb-6">
                <h3 class="text-2xl font-bold bg-gradient-to-r from-lime-400 to-cyan-400 bg-clip-text text-transparent">
                  Revenue Dashboard
                </h3>
                <p class="text-gray-300 mt-2">Real-time business metrics and insights</p>
              </div>
              
              <!-- Loading state -->
              <div v-if="isLoading" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-lime-400"></div>
              </div>
              
              <!-- Stats component -->
              <div v-else class="flex justify-center">
                <RevenueStats />
              </div>
            </div>

            <!-- Welcome Section -->
            <div class="bg-white/5 backdrop-blur-xl rounded-2xl overflow-hidden border border-white/10 shadow-2xl">
              <div class="relative">
                <!-- Background image with overlay -->
                <div class="absolute inset-0 bg-gradient-to-br from-gray-900/80 via-gray-800/60 to-black/80"></div>
                <div class="relative z-10 p-6">
                  <Welcome />
                </div>
              </div>
            </div>

            <!-- Action Buttons Section -->
            <div class="grid md:grid-cols-2 gap-6">
              
              <!-- Admin Actions -->
              <div v-if="$page.props.auth.user.isAdmin" class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10 shadow-2xl">
                <div class="text-center mb-4">
                  <h4 class="text-xl font-semibold text-white">Admin Controls</h4>
                  <p class="text-gray-300 text-sm mt-1">Manage work orders and system</p>
                </div>
                <div class="space-y-3">
                  <button @click="createWorkOrder" 
                    class="w-full bg-gradient-to-r from-lime-400 to-lime-500 hover:from-lime-500 hover:to-lime-600 text-black font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105 hover:shadow-lg">
                    <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create Work Order
                  </button>
                  <button @click="navigateToWorkOrders" 
                    class="w-full bg-white/10 hover:bg-white/20 text-white font-semibold py-3 px-4 rounded-lg border border-white/20 hover:border-white/30 transition-all duration-200">
                    <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    View All Orders
                  </button>
                </div>
              </div>

              <!-- Technician Actions -->
              <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10 shadow-2xl">
                <div class="text-center mb-4">
                  <h4 class="text-xl font-semibold text-white">Quick Actions</h4>
                  <p class="text-gray-300 text-sm mt-1">Essential tools and functions</p>
                </div>
                <div class="grid grid-cols-1 gap-3">
                  <button @click="updateStatus" 
                    class="bg-purple-500/20 hover:bg-purple-500/30 border border-purple-400/30 hover:border-purple-400/50 text-purple-200 font-medium py-2.5 px-4 rounded-lg transition-all duration-200">
                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Update Status
                  </button>
                  <button @click="uploadImage" 
                    class="bg-blue-500/20 hover:bg-blue-500/30 border border-blue-400/30 hover:border-blue-400/50 text-blue-200 font-medium py-2.5 px-4 rounded-lg transition-all duration-200">
                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Upload Image
                  </button>
                  <button @click="getSignature" 
                    class="bg-orange-500/20 hover:bg-orange-500/30 border border-orange-400/30 hover:border-orange-400/50 text-orange-200 font-medium py-2.5 px-4 rounded-lg transition-all duration-200">
                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Get Signature
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Work Order Modal - Displays data from your actual database -->
      <div v-if="showWorkOrderModal && selectedWorkOrder" class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-2 sm:px-4 pb-16 sm:pb-20 text-center">
          <!-- Background overlay -->
          <div class="fixed inset-0 bg-gray-800 bg-opacity-75 transition-opacity" aria-hidden="true"
            @click="closeModal"></div>
          <!-- Modal panel -->
          <div
            class="inline-block bg-gray-900 rounded-lg overflow-hidden shadow-xl transform transition-all sm:my-8 w-full max-w-[90%] sm:max-w-lg relative">
            <div class="bg-gray-900 px-3 sm:px-4 pt-3 sm:pt-5 pb-3 sm:pb-4 text-center">
              <div class="flex flex-col items-center">
                <div class="mt-2 sm:mt-0 w-full">
                  <!-- Header with close button -->
                  <div class="flex justify-between items-center mb-3 sm:mb-4">
                    <h3 class="text-md sm:text-lg leading-6 font-medium text-white truncate pr-2" id="modal-title">
                      Work Order #{{ selectedWorkOrder.id }}
                    </h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-200 flex-shrink-0">
                      <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  <!-- Work order details from your database -->
                  <div class="text-gray-300 space-y-2 sm:space-y-3 text-sm sm:text-base">
                    <div v-if="selectedWorkOrder.title" class="mb-2 text-center">
                      <h4 class="text-md sm:text-lg font-medium text-white">{{ selectedWorkOrder.title }}</h4>
                    </div>
                    <div v-if="selectedWorkOrder.status" class="flex flex-wrap justify-center gap-x-8 gap-y-2">
                      <div class="text-center">
                        <p class="text-xs sm:text-sm text-gray-400">Status:</p>
                        <span class="inline-flex px-2 py-1 text-xs rounded" :class="{
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
                      <div v-if="selectedWorkOrder.price" class="text-center">
                        <p class="text-xs sm:text-sm text-gray-400">Price:</p>
                        <span>${{ selectedWorkOrder.price }}</span>
                      </div>
                    </div>
                    <div v-if="selectedWorkOrder.customer_id || selectedWorkOrder.customer_name" class="mt-3 sm:mt-4 text-center">
                      <p class="text-xs sm:text-sm text-gray-400">Customer:</p>
                      <p v-if="selectedWorkOrder.customer_name" class="truncate">{{ selectedWorkOrder.customer_name }}
                      </p>
                      <p v-if="selectedWorkOrder.customer_id" class="text-xs sm:text-sm">ID: {{
                        selectedWorkOrder.customer_id }}</p>
                    </div>
                    <div v-if="selectedWorkOrder.created_at || selectedWorkOrder.date" class="text-center">
                      <p class="text-xs sm:text-sm text-gray-400">Date:</p>
                      <p>{{ new Date(selectedWorkOrder.created_at || selectedWorkOrder.date).toLocaleString() }}</p>
                    </div>
                    <div v-if="selectedWorkOrder.description" class="mt-3 sm:mt-4">
                      <p class="text-xs sm:text-sm text-gray-400 text-center">Description:</p>
                      <p class="whitespace-pre-line mt-1 text-xs sm:text-sm bg-gray-800 p-2 sm:p-3 rounded-md">
                        {{ selectedWorkOrder.description }}
                      </p>
                    </div>
                    <!-- Display any additional fields from your database -->
                    <template v-for="(value, key) in selectedWorkOrder" :key="key">
                      <div
                        v-if="!['id', 'title', 'status', 'price', 'customer_id', 'customer_name', 'created_at', 'date', 'description'].includes(key) && value"
                        class="text-center">
                        <p class="text-xs sm:text-sm text-gray-400">{{ key.charAt(0).toUpperCase() + key.slice(1).replace('_', ' ')
                          }}:</p>
                        <p class="text-xs sm:text-sm">{{ value }}</p>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </div>
            <!-- Modal footer -->
            <div class="bg-gray-800 px-3 sm:px-6 py-3 flex flex-col sm:flex-row gap-2 justify-center">
              <Link :href="`/work-orders/${selectedWorkOrder.id}`"
                class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-3 sm:px-4 py-2 bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700">
              View Full Details
              </Link>
              <button @click="closeModal" type="button"
                class="w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-700 shadow-sm px-3 sm:px-4 py-2 bg-gray-700 text-sm font-medium text-gray-300 hover:bg-gray-600">
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Blob animations matching the homepage and login */
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

/* Glass morphism effects */
.backdrop-blur-xl {
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
}

/* Enhanced shadow effects */
.shadow-2xl {
    box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.5),
        0 0 0 1px rgba(255, 255, 255, 0.05),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

/* Glass header styling */
.glass-header {
  background: rgba(17, 24, 39, 0.95);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding-bottom: env(safe-area-inset-bottom);
}

/* Gradient text effect */
.bg-gradient-to-r.from-lime-400.to-cyan-400 {
    background-image: linear-gradient(to right, #a3e635, #22d3ee);
}

/* Button hover animations */
button {
    transition: all 0.2s ease;
}

button:hover {
    transform: translateY(-1px);
}

/* Card entrance animation */
.bg-white\/5 {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    0% {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Loading spinner styling */
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Additional styles for better mobile responsiveness */
.search-container {
    width: 200px;
    max-width: 100%;
}

/* Responsive adjustments for very small screens */
@media (max-width: 340px) {
    .search-container {
        width: 100%;
    }
    
    .admin-actions button,
    .tech-actions button {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}

/* Add specific styling for team dropdown */
.dark .dropdown-content {
    background-color: #1f2937;
    border-color: #374151;
}

/* Responsive modal adjustments */
@media (max-width: 480px) {
    .glass-header {
        padding: 0.5rem;
    }
    
    /* Smaller text for very small screens */
    h2 {
        font-size: 1rem;
    }
    
    /* Ensure buttons don't overflow */
    button {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
}

/* Touch-friendly adjustments for buttons on mobile */
@media (pointer: coarse) {
    button, 
    a[role="button"] {
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
}

/* Enhanced glass card styling */
.bg-white\/5 {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.bg-white\/5:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    transition: all 0.3s ease;
}</style>