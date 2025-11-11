<script setup>
import { ref, computed, onErrorCaptured } from 'vue';
import format from 'date-fns/format';
import { usePage, router } from '@inertiajs/vue3';
import AddWorkOrder from '@/Pages/WorkOrders/AddWorkOrder.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import WorkOrder from './WorkOrder.vue';
import axios from 'axios';
import CurrentTime from '@/Components/CurrentTime.vue';
import TeamDropdown from '@/Components/TeamDropdown.vue';
import ArchivedWorkOrders from './ArchivedWorkOrders.vue';
import Stats from '@/Components/Stats.vue';
import { useToast } from '@/Composables/useToast';

// Define props from Inertia
const props = defineProps({
  workOrders: {
    type: [Object, Array],
    required: true,
    default: () => ({ data: [] })
  },
  users: {
    type: Array,
    default: () => []
  },
  errors: {
    type: Object,
    default: () => ({})
  }
});

// Initialize refs with props data
const setupError = ref(null);
const workOrders = ref([]);
const users = ref([]);

// Update the getUserName function and related code
try {
  workOrders.value = Array.isArray(props.workOrders.data) 
    ? props.workOrders.data 
    : Array.isArray(props.workOrders) 
      ? props.workOrders 
      : [];
  
  // Log the first work order to check its user_id structure
  console.log('Sample work order:', workOrders.value[0]);
  
  // Initialize users directly from props
  users.value = Array.isArray(props.users) ? props.users : [];
  console.log('Users:', users.value);
} catch (error) {
  console.error('Setup error:', error);
  setupError.value = error;
}

// Update getUserName function to use the users array directly
const getUserName = (userId) => {
  // Look up the user in the users array by matching IDs (as strings)
  return users.value.find(u => String(u.id) === String(userId))?.name || '';
};

// Add this function to get the user's first name by userId
const getUserFirstName = (userId) => {
  const user = users.value.find(u => String(u.id) === String(userId));
  if (!user || !user.name) return '';
  return user.name.split(' ')[0];
};

// Error boundary
onErrorCaptured((err) => {
  console.error('Captured error:', err);
  setupError.value = err;
  return false; // Prevent error from propagating
});

// Add sorted work orders as computed property
const sortedWorkOrders = computed(() => {
  if (!Array.isArray(workOrders.value)) return [];
  return [...workOrders.value].sort((a, b) => 
    new Date(b.created_at) - new Date(a.created_at)
  );
});

const searchQuery = ref('');
const showArchivedModal = ref(false); // Add this inside setup

const selectedWorkOrder = ref(null);
const showWorkOrderModal = ref(false);

const openModal = (workOrder) => {
  selectedWorkOrder.value = workOrder;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedWorkOrder.value = null;
};

const formatDate = (date) => {
  if (!date) return 'Invalid date';
  try {
    const parsedDate = new Date(date);
    if (isNaN(parsedDate)) {
      throw new Error('Invalid date');
    }
    return format(parsedDate, 'MMMM dd, yyyy hh:mm a');
  } catch (error) {
    console.error('Invalid date:', date);
    return 'Invalid date';
  }
};

function deleteWorkOrder(id) {
  if (confirm('Are you sure you want to delete this work order?')) {
    // Get CSRF token from meta tag
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    axios.delete(`/work-orders/${id}`, {
      headers: {
        'X-CSRF-TOKEN': token,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })
    .then(response => {
      // Remove from the UI
      workOrders.value = workOrders.value.filter(wo => wo.id !== id);
      // toastSuccess('Work order deleted successfully');
    })
    .catch(error => {
      console.error('Error deleting work order:', error);
      // toastError('Failed to delete work order: ' + (error.response?.data?.error || 'Unknown error'));
    });
  }
}

// Pagination logic
const currentPage = ref(1);
const itemsPerPage = 10;

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(filteredWorkOrders.value.length / itemsPerPage));
});

const paginatedWorkOrders = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredWorkOrders.value.slice(start, end);
});

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

// Search logic
const selectedStatFilter = ref(null);

// filteredWorkOrders is now defined further down in the code
// where it combines both status filters

function handleFilterStats(statName) {
  selectedStatFilter.value = statName === 'Total' ? null : statName.toLowerCase();
}

// Archived work order modal state
const showArchivedWorkOrderModal = ref(false);
const archivedWorkOrder = ref(null);
const archivedInvoiceId = ref('');
const archivedMessage = ref('');

// Handle showing the work order modal
const showWorkOrder = (workOrder) => {
  selectedWorkOrder.value = workOrder;
  showWorkOrderModal.value = true;
};

// Handle closing the work order modal
const closeWorkOrderModal = () => {
  showWorkOrderModal.value = false;
  selectedWorkOrder.value = null; // Clear the selected work order
};

// Handle invoice created event from WorkOrder component
const handleInvoiceCreated = async (data) => {
  try {
    // Get the archived work order data from the server
    const response = await axios.get(`/work-orders/${data.workOrderId}/details`);
    
    // Set up the archived work order modal
    archivedWorkOrder.value = response.data;
    archivedInvoiceId.value = data.invoiceId;
    archivedMessage.value = data.message;
    
    // Show the archived work order modal
    showArchivedWorkOrderModal.value = true;
  } catch (error) {
    console.error('Failed to load archived work order:', error);
    // toastSuccess('Invoice created successfully, but work order details could not be loaded.');
  }
};

// Handle closing the archived work order modal
const closeArchivedWorkOrderModal = () => {
  showArchivedWorkOrderModal.value = false;
  archivedWorkOrder.value = null;
  
  // Use router.visit instead of reload
  router.visit(window.location.pathname, {
    preserveState: true,
    preserveScroll: true,
    only: ['workOrders']
  });
};

// Add this helper function to determine progress percentage based on status
const getStatusColor = (status) => {
  switch (status.toLowerCase()) {
    case 'scheduled': return 'bg-blue-500';
    case 'in progress': return 'bg-yellow-500';
    case 'part needed': return 'bg-purple-500';
    case 'complete': return 'bg-green-500';
    case 'cancelled': return 'bg-red-500';
    case 'archived': return 'bg-gray-500';
    default: return 'bg-gray-500';
  }
};

const getStatusProgress = (status) => {
  switch (status.toLowerCase()) {
    case 'scheduled': return 20;
    case 'in progress': return 40;
    case 'part needed': return 60;
    case 'complete': return 100;
    case 'cancelled': return 0;
    case 'archived': return 100;
    default: return 0;
  }
};

const showArchived = ref(false);

const viewArchivedWorkOrder = (workOrder) => {
  selectedWorkOrder.value = workOrder;
  showWorkOrderModal.value = true;
  showArchived.value = false;
};

const openArchivedModal = () => {
  showArchived.value = true;
};

const closeArchivedModal = () => {
  showArchived.value = false;
};

// Update the filteredData computed property
const filteredData = computed(() => {
  const data = workOrders.value || [];
  return [
    {
      name: 'Total',
      value: data.length || 0,
      status: 'total',
      change: '',
      changeType: 'neutral'
    },
    {
      name: 'Invoiced',
      value: data.filter(wo => wo?.status?.toLowerCase() === 'invoiced').length || 0,
      status: 'invoiced',
      change: '',
      changeType: 'neutral'
    },
    {
      name: 'Archived',
      value: data.filter(wo => wo?.status?.toLowerCase() === 'archived').length || 0,
      status: 'archived',
      change: '',
      changeType: 'neutral'
    },
    {
      name: 'Scheduled',
      value: data.filter(wo => wo?.status?.toLowerCase() === 'scheduled').length || 0,
      status: 'scheduled',
      change: '',
      changeType: 'neutral'
    },
    {
      name: 'In Progress',
      value: data.filter(wo => wo?.status?.toLowerCase() === 'in progress').length || 0,
      status: 'in_progress',
      change: '',
      changeType: 'neutral'
    },
    {
      name: 'Part Needed',
      value: data.filter(wo => wo?.status?.toLowerCase() === 'part needed').length || 0,
      status: 'part_needed',
      change: '',
      changeType: 'neutral'
    },
    {
      name: 'Complete',
      value: data.filter(wo => wo?.status?.toLowerCase() === 'complete').length || 0,
      status: 'complete',
      change: '',
      changeType: 'neutral'
    },
    {
      name: 'Cancelled',
      value: data.filter(wo => wo?.status?.toLowerCase() === 'cancelled').length || 0,
      status: 'cancelled',
      change: '',
      changeType: 'neutral'
    }
  ];
});

// Group work orders by base title (removing date information from parentheses)
const groupedWorkOrders = computed(() => {
  const grouped = {};
  
  workOrders.value.forEach(workOrder => {
    // Extract base title without date part
    let baseTitle = workOrder.title;
    const dateMatch = baseTitle.match(/^(.+) \(\d{1,2}\/\d{1,2}\/\d{2,4}\)$/);
    
    if (dateMatch) {
      baseTitle = dateMatch[1];
    }
    
    if (!grouped[baseTitle]) {
      grouped[baseTitle] = [];
    }
    
    grouped[baseTitle].push(workOrder);
  });
  
  return grouped;
});

const formatDateTime = (dateTime) => {
  return new Date(dateTime).toLocaleString();
};

const openWorkOrder = async (workOrder) => {
  try {
    console.log('Opening work order:', workOrder.id);
    // Get full work order details
    const response = await axios.get(`/work-orders/${workOrder.id}/details`);
    console.log('Work order details response:', response.data);
    selectedWorkOrder.value = response.data;
    showWorkOrderModal.value = true;
    console.log('Modal state:', { selectedWorkOrder: selectedWorkOrder.value, showWorkOrderModal: showWorkOrderModal.value });
  } catch (error) {
    console.error('Error loading work order details:', error);
    // toastError('Unable to load work order details. Please try again.');
  }
};

const statusOptions = [
  { label: 'All', value: '' },
  { label: 'Scheduled', value: 'scheduled' },
  { label: 'In Progress', value: 'in progress' },
  { label: 'Part Needed', value: 'part needed' },
  { label: 'Complete', value: 'complete' },
  { label: 'Cancelled', value: 'cancelled' },
  { label: 'Archived', value: 'archived' },
  { label: 'Invoiced', value: 'invoiced' }
];

const selectedStatus = ref('');

// Merge the two filteredWorkOrders computeds into one
const filteredWorkOrders = computed(() => {
  if (!Array.isArray(sortedWorkOrders.value)) return [];
  
  let filtered = sortedWorkOrders.value;

  try {
    if (selectedStatus.value) {
      filtered = filtered.filter(workOrder => 
        workOrder?.status?.toLowerCase() === selectedStatus.value.toLowerCase()
      );
    } else if (selectedStatFilter.value) {
      filtered = filtered.filter(workOrder => 
        workOrder?.status?.toLowerCase() === selectedStatFilter.value.toLowerCase()
      );
    }

    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase();
      filtered = filtered.filter(workOrder => {
        if (!workOrder) return false;
        return (
          workOrder.title?.toLowerCase().includes(query) ||
          workOrder.description?.toLowerCase().includes(query) ||
          workOrder.customer_id?.toLowerCase().includes(query) ||
          getUserName(workOrder.user_id)?.toLowerCase().includes(query)
        );
      });
    }

    return filtered;
  } catch (error) {
    console.error('Filter error:', error);
    return [];
  }
});

// Add clearFilters function
const clearFilters = () => {
  selectedStatus.value = '';
  selectedStatFilter.value = null;
  searchQuery.value = '';
  currentPage.value = 1;
};

// Add this computed property after the existing filteredWorkOrders computed
const filteredGroupedWorkOrders = computed(() => {
  const grouped = {};
  
  // Use filteredWorkOrders instead of workOrders.value
  filteredWorkOrders.value.forEach(workOrder => {
    let baseTitle = workOrder.title;
    const dateMatch = baseTitle.match(/^(.+) \(\d{1,2}\/\d{1,2}\/\d{2,4}\)$/);
    
    if (dateMatch) {
      baseTitle = dateMatch[1];
    }
    
    if (!grouped[baseTitle]) {
      grouped[baseTitle] = [];
    }
    
    grouped[baseTitle].push(workOrder);
  });
  
  return grouped;
});

// Add helper function to check for multi-day work orders
const isPartOfMultiDayWorkOrder = (workOrder) => {
  const baseTitle = workOrder.title.replace(/\s*\(\d{1,2}\/\d{1,2}\/\d{2,4}\)$/, '');
  return Object.values(filteredGroupedWorkOrders.value)
    .find(group => group.length > 1 && group.some(wo => wo.id === workOrder.id));
};

// Add a ref for the toast system (if not already present)
const { success: toastSuccess, error: toastError } = useToast();

// Call this function after a work order is created successfully
function showWorkOrderCreatedToast(workOrder) {
  const firstName = getUserFirstName(workOrder.user_id);
  toastSuccess(`Work order created successfully! Welcome, ${firstName ? firstName : 'User'}!`);
}
</script>

<template>
  <AppLayout>
    <!-- Main Container with Glass Morphism Background -->
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black relative overflow-hidden">
      <!-- Animated Background Elements -->
      <div class="absolute inset-0 overflow-hidden">
        <!-- Desktop blobs -->
        <div class="hidden md:block absolute top-10 left-10 w-96 h-96 bg-lime-400/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div class="hidden md:block absolute top-20 right-20 w-96 h-96 bg-cyan-400/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        <div class="hidden md:block absolute bottom-20 left-1/3 w-96 h-96 bg-purple-400/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
        
        <!-- Mobile-optimized blobs -->
        <div class="md:hidden absolute top-20 left-5 w-64 h-64 bg-lime-400/8 rounded-full mix-blend-multiply filter blur-xl opacity-60 animate-blob"></div>
        <div class="md:hidden absolute top-32 right-5 w-48 h-48 bg-cyan-400/8 rounded-full mix-blend-multiply filter blur-xl opacity-60 animate-blob animation-delay-2000"></div>
        <div class="md:hidden absolute bottom-32 left-1/4 w-56 h-56 bg-purple-400/8 rounded-full mix-blend-multiply filter blur-xl opacity-60 animate-blob animation-delay-4000"></div>
      </div>

      <!-- Fixed Header -->
      <div class="fixed top-0 left-0 right-0 z-50 backdrop-blur-xl bg-gray-900/90 border-b border-white/10">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
              <h2 class="text-xl sm:text-2xl font-bold">
                <span class="bg-gradient-to-r from-lime-400 to-cyan-400 bg-clip-text text-transparent">
                  Active Work Orders
                </span>
              </h2>
              <CurrentTime />
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:gap-4">
              <TeamDropdown :teams="props.teams" />
              <AddWorkOrder />
              <button @click="openArchivedModal"
                class="glass-btn flex items-center gap-2 text-purple-400 border-purple-400/30 hover:bg-purple-400/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
                <span class="hidden sm:inline">View Archive</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Area -->
      <div class="relative z-10 pt-24 px-4 sm:px-6 lg:px-8 pb-8">
        <div class="max-w-7xl mx-auto">
          <!-- Search and Filters Section -->
          <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-4 sm:p-6 border border-white/10 shadow-2xl mb-6">
            <div class="flex flex-col lg:flex-row items-start lg:items-center gap-4">
              <!-- Search Input -->
              <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
                <input 
                  v-model="searchQuery" 
                  type="text" 
                  placeholder="Search work orders..."
                  class="modern-input pl-10 w-full"
                />
              </div>

              <!-- Status Filter -->
              <div class="flex flex-wrap items-center gap-2">
                <select v-model="selectedStatus" class="modern-select">
                  <option value="">All Statuses</option>
                  <option v-for="option in statusOptions.slice(1)" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
                
                <!-- Clear Filters Button -->
                <button 
                  v-if="searchQuery || selectedStatus"
                  @click="clearFilters"
                  class="glass-btn text-red-400 border-red-400/30 hover:bg-red-400/20"
                >
                  Clear
                </button>
              </div>
            </div>
          </div>

          <!-- Stats Section -->
          <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-4 sm:p-6 border border-white/10 shadow-2xl mb-6">
            <Stats :stats="filteredData" @filter="handleFilterStats" />
          </div>

          <!-- Work Orders Grid/Table -->
          <div class="bg-white/5 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
            <!-- Mobile Card View -->
            <div class="block lg:hidden">
              <div class="p-4 space-y-4">
                <div 
                  v-for="workOrder in paginatedWorkOrders" 
                  :key="workOrder.id"
                  class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:bg-white/10 transition-all cursor-pointer"
                  @click="openWorkOrder(workOrder)"
                >
                  <!-- Work Order Card Header -->
                  <div class="flex items-start justify-between mb-3">
                    <h3 class="font-semibold text-white text-sm line-clamp-2">{{ workOrder.title }}</h3>
                    <span :class="['px-2 py-1 text-xs font-semibold rounded-full', getStatusColor(workOrder.status)]">
                      {{ workOrder.status }}
                    </span>
                  </div>
                  
                  <!-- Progress Bar -->
                  <div class="mb-3">
                    <div class="flex items-center justify-between text-xs text-gray-300 mb-1">
                      <span>Progress</span>
                      <span>{{ getStatusProgress(workOrder.status) }}%</span>
                    </div>
                    <div class="w-full bg-white/10 rounded-full h-2">
                      <div 
                        :style="{ width: getStatusProgress(workOrder.status) + '%' }"
                        class="h-2 rounded-full transition-all duration-300"
                        :class="getStatusColor(workOrder.status)"
                      ></div>
                    </div>
                  </div>
                  
                  <!-- Work Order Details -->
                  <div class="space-y-2 text-xs text-gray-300">
                    <div class="flex items-center justify-between">
                      <span>Created:</span>
                      <span>{{ formatDate(workOrder.date_time || workOrder.created_at) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                      <span>Technician:</span>
                      <span>{{ getUserName(workOrder.user_id) || 'Unassigned' }}</span>
                    </div>
                  </div>
                  
                  <!-- Action Button -->
                  <div class="mt-3 pt-3 border-t border-white/10">
                    <button 
                      @click.stop="openWorkOrder(workOrder)"
                      class="text-lime-400 hover:text-lime-300 text-sm font-medium flex items-center gap-1"
                    >
                      <span>View Details</span>
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </button>
                  </div>
                </div>
                
                <!-- Empty State for Mobile -->
                <div v-if="paginatedWorkOrders.length === 0" class="text-center py-8">
                  <svg class="h-12 w-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                  <p class="text-gray-400">No work orders found</p>
                </div>
              </div>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden lg:block overflow-x-auto">
              <table class="min-w-full divide-y divide-white/10">
                <thead class="bg-white/5">
                  <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Work Order
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Status
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Progress
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Technician
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Created
                    </th>
                    <th class="px-6 py-4 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                  <tr 
                    v-for="workOrder in paginatedWorkOrders" 
                    :key="workOrder.id"
                    class="hover:bg-white/5 cursor-pointer transition-colors group"
                    @click="openWorkOrder(workOrder)"
                  >
                    <td class="px-6 py-4">
                      <div class="flex items-start">
                        <div>
                          <div class="text-sm font-medium text-white group-hover:text-lime-400 transition-colors">
                            {{ workOrder.title }}
                          </div>
                          <div class="text-xs text-gray-400 mt-1">
                            ID: #{{ workOrder.id }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <span :class="['px-3 py-1 text-xs font-semibold rounded-full', getStatusColor(workOrder.status)]">
                        {{ workOrder.status }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex items-center">
                        <div class="w-24 bg-white/10 rounded-full h-2 mr-3">
                          <div 
                            :style="{ width: getStatusProgress(workOrder.status) + '%' }"
                            class="h-2 rounded-full transition-all duration-300"
                            :class="getStatusColor(workOrder.status)"
                          ></div>
                        </div>
                        <span class="text-xs text-gray-300 min-w-[3rem]">{{ getStatusProgress(workOrder.status) }}%</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300">
                      {{ getUserName(workOrder.user_id) || 'Unassigned' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300">
                      {{ formatDate(workOrder.date_time || workOrder.created_at) }}
                    </td>
                    <td class="px-6 py-4 text-right">
                      <button 
                        @click.stop="openWorkOrder(workOrder)"
                        class="text-lime-400 hover:text-lime-300 text-sm font-medium"
                      >
                        View Details
                      </button>
                    </td>
                  </tr>
                  
                  <!-- Empty State for Desktop -->
                  <tr v-if="paginatedWorkOrders.length === 0">
                    <td colspan="6" class="text-center py-8">
                      <div class="flex flex-col items-center">
                        <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-gray-400">No work orders found</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white/5 px-4 py-3 border-t border-white/10 sm:px-6">
              <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-300">
                  Showing {{ ((currentPage - 1) * itemsPerPage) + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredWorkOrders.length) }} of {{ filteredWorkOrders.length }} results
                </div>
                <div class="flex items-center gap-2">
                  <button 
                    @click="prevPage" 
                    :disabled="currentPage === 1"
                    class="glass-btn text-gray-300 border-gray-600/30 hover:bg-white/10 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden sm:inline ml-1">Previous</span>
                  </button>
                  
                  <span class="px-3 py-1 text-sm text-white bg-white/10 rounded-lg">
                    {{ currentPage }} of {{ totalPages }}
                  </span>
                  
                  <button 
                    @click="nextPage" 
                    :disabled="currentPage === totalPages"
                    class="glass-btn text-gray-300 border-gray-600/30 hover:bg-white/10 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <span class="hidden sm:inline mr-1">Next</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <WorkOrder 
      v-if="selectedWorkOrder" 
      :workOrder="selectedWorkOrder" 
      :showModal="showWorkOrderModal" 
      :users="users"
      @close="closeWorkOrderModal" 
    />

    <ArchivedWorkOrders 
      v-if="showArchived"
      @close="closeArchivedModal"
      @view-archived="viewArchivedWorkOrder"
    />
  </AppLayout>
</template>

<style scoped>
/* Blob animations matching the login page */
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

/* Modern input styling with responsive padding */
.modern-input {
    width: 100%;
    padding: 0.75rem 1rem;
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.5rem;
    color: white;
    backdrop-filter: blur(4px);
    transition: all 0.3s ease;
    font-size: 0.875rem;
}

.modern-input::placeholder {
    color: rgba(156, 163, 175, 1);
}

.modern-input:focus {
    outline: none;
    background-color: rgba(255, 255, 255, 0.08);
    border-color: transparent;
    box-shadow: 0 0 0 2px rgba(163, 230, 53, 0.3);
}

/* Modern select styling */
.modern-select {
    padding: 0.75rem 1rem;
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.5rem;
    color: white;
    backdrop-filter: blur(4px);
    transition: all 0.3s ease;
    font-size: 0.875rem;
    min-width: 150px;
}

.modern-select:focus {
    outline: none;
    background-color: rgba(255, 255, 255, 0.08);
    border-color: transparent;
    box-shadow: 0 0 0 2px rgba(163, 230, 53, 0.3);
}

.modern-select option {
    background-color: #1f2937;
    color: white;
}

/* Glass button styling */
.glass-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(4px);
    transition: all 0.2s ease;
    font-size: 0.875rem;
    cursor: pointer;
}

.glass-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
}

.glass-btn:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(163, 230, 53, 0.4);
}

.glass-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
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

/* Gradient text effect */
.bg-gradient-to-r.from-lime-400.to-cyan-400 {
    background-image: linear-gradient(to right, #a3e635, #22d3ee);
}

/* Status badge colors */
.bg-blue-500 {
    background-color: #3b82f6;
    color: white;
}

.bg-yellow-500 {
    background-color: #eab308;
    color: black;
}

.bg-purple-500 {
    background-color: #a855f7;
    color: white;
}

.bg-green-500 {
    background-color: #22c55e;
    color: white;
}

.bg-red-500 {
    background-color: #ef4444;
    color: white;
}

.bg-gray-500 {
    background-color: #6b7280;
    color: white;
}

/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Custom scrollbar for better aesthetics */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* Responsive design improvements */
@media (max-width: 640px) {
    .modern-input,
    .modern-select {
        font-size: 1rem; /* Prevent iOS zoom */
        padding: 0.875rem 1rem;
    }
    
    .glass-btn {
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        min-height: 44px; /* Better touch targets */
    }
    
    /* Ensure better mobile spacing */
    .bg-white\/5 {
        padding: 1rem;
        border-radius: 1rem;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .modern-input,
    .modern-select,
    .glass-btn {
        border-color: rgba(255, 255, 255, 0.5);
        background-color: rgba(0, 0, 0, 0.8);
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .animate-blob {
        animation: none;
    }
    
    * {
        transition: none !important;
    }
}

/* Focus improvements for accessibility */
.modern-input:focus-visible,
.modern-select:focus-visible,
.glass-btn:focus-visible {
    outline: 2px solid #a3e635;
    outline-offset: 2px;
}

/* Card entrance animations */
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

/* Hover effects for interactive elements */
.group:hover .group-hover\:text-lime-400 {
    color: #a3e635;
}

/* Table improvements */
table {
    border-collapse: separate;
    border-spacing: 0;
}

td, th {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

tr:last-child td {
    border-bottom: none;
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Better button states */
button:active {
    transform: translateY(1px);
}

button:disabled:active {
    transform: none;
}

/* Improved spacing for mobile */
@media (max-width: 1024px) {
    .pt-24 {
        padding-top: 6rem; /* More space for fixed header on mobile */
    }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
    .bg-white\/5 {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    
    .modern-input,
    .modern-select {
        background-color: rgba(0, 0, 0, 0.3);
        border-color: rgba(255, 255, 255, 0.1);
    }
}

/* Print styles */
@media print {
    .animate-blob,
    .backdrop-blur-xl,
    .shadow-2xl {
        animation: none;
        backdrop-filter: none;
        box-shadow: none;
    }
    
    .bg-gradient-to-br {
        background: white;
        color: black;
    }
}
</style>