<script setup>
import { ref, computed, onErrorCaptured, nextTick } from 'vue';
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
      alert('Work order deleted successfully');
    })
    .catch(error => {
      console.error('Error deleting work order:', error);
      alert('Failed to delete work order: ' + (error.response?.data?.error || 'Unknown error'));
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
  console.log('Closing modal');
  showWorkOrderModal.value = false;
  selectedWorkOrder.value = null;
  console.log('Modal state after closing:', {
    showModal: showWorkOrderModal.value,
    selectedWorkOrder: selectedWorkOrder.value
  });
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
    alert('Invoice created successfully, but work order details could not be loaded.');
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
      stat: data.length || 0,
      status: 'total'
    },
    {
      name: 'Invoiced',
      stat: data.filter(wo => wo?.status?.toLowerCase() === 'invoiced').length || 0,
      status: 'invoiced'
    },
    {
      name: 'Archived',
      stat: data.filter(wo => wo?.status?.toLowerCase() === 'archived').length || 0, // Just count archived status
      status: 'archived'
    },
    {
      name: 'Scheduled',
      stat: data.filter(wo => wo?.status?.toLowerCase() === 'scheduled').length || 0,
      status: 'scheduled'
    },
    {
      name: 'In Progress',
      stat: data.filter(wo => wo?.status?.toLowerCase() === 'in progress').length || 0,
      status: 'in_progress'
    },
    {
      name: 'Part Needed',
      stat: data.filter(wo => wo?.status?.toLowerCase() === 'part needed').length || 0,
      status: 'part_needed'
    },
    {
      name: 'Complete',
      stat: data.filter(wo => wo?.status?.toLowerCase() === 'complete').length || 0,
      status: 'complete'
    },
    {
      name: 'Cancelled',
      stat: data.filter(wo => wo?.status?.toLowerCase() === 'cancelled').length || 0,
      status: 'cancelled'
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

    // Ensure the data is valid before setting it
    if (!response.data) {
      throw new Error('No data received from server');
    }

    // Set the modal state
    showWorkOrderModal.value = true;
    selectedWorkOrder.value = response.data;
    
    // Debug logs
    console.log('Modal state after setting:', {
      showModal: showWorkOrderModal.value,
      selectedWorkOrder: selectedWorkOrder.value,
      workOrderId: selectedWorkOrder.value?.id,
      title: selectedWorkOrder.value?.title
    });

    // Force an update if needed
    nextTick(() => {
      console.log('After nextTick - Modal visible:', showWorkOrderModal.value);
    });

  } catch (error) {
    console.error('Error loading work order details:', error);
    alert('Unable to load work order details. Please try again.');
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
</script>

<template>
  <AppLayout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 mt-40">
        <div class="overflow-hidden bg-gray-900/50 shadow-xl backdrop-blur-xl sm:rounded-lg">
          <!-- Search and Add New section -->
          <div class="p-6 flex justify-between items-center border-b border-gray-700">
            <div class="relative">
              <input v-model="searchQuery" type="text" placeholder="Search work orders..."
                class="pl-10 pr-4 py-2 rounded-lg bg-gray-800/50 border border-gray-700 text-white placeholder-gray-400 focus:outline-none focus:border-lime-500" />
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
          </div>

          <Stats :stats="filteredData" />

          <!-- Work Orders Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
              <thead>
                <tr>
                  <th class="px-6 py-3 text-left text-sm font-medium text-purple-400 uppercase tracking-wider">Title
                  </th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-purple-400 uppercase tracking-wider">Status
                  </th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-purple-400 uppercase tracking-wider">Customer
                  </th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-purple-400 uppercase tracking-wider">
                    Technician
                  </th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-purple-400 uppercase tracking-wider">Date</th>

                </tr>
              </thead>
              <tbody class="divide-y divide-gray-700">
                <tr v-for="workOrder in paginatedWorkOrders" :key="workOrder.id"
                  class="hover:bg-gray-800/30 cursor-pointer group" @click="openWorkOrder(workOrder)" tabindex="0"
                  @keydown.enter="openWorkOrder(workOrder)" aria-label="Open work order details" role="button">
                  <td class="px-6 py-4 whitespace-nowrap text-white group-hover:underline">{{ workOrder.title }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getStatusColor(workOrder.status)]">
                      {{ workOrder.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-white">{{ workOrder.customer_id }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-white">{{ getUserName(workOrder.user_id) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-white">{{ formatDate(workOrder.date_time ||
                    workOrder.created_at) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <span class="text-xs text-lime-400 hover:underline cursor-pointer"
                      @click.stop="openWorkOrder(workOrder)" tabindex="0" role="button" aria-label="View details">View
                      Details</span>
                  </td>
                </tr>
                <tr v-if="paginatedWorkOrders.length === 0">
                  <td colspan="6" class="text-center py-4 text-gray-400">No work orders found</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="mt-4">
            <button @click="prevPage" :disabled="currentPage === 1"
              class="px-4 py-2 mr-2 rounded bg-gray-800 text-lime-400 disabled:opacity-50">Previous</button>
            <span class="text-white">Page {{ currentPage }} of {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages"
              class="px-4 py-2 ml-2 rounded bg-gray-800 text-lime-400 disabled:opacity-50">Next</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add the WorkOrder modal component -->
    <WorkOrder v-if="showWorkOrderModal && selectedWorkOrder" :workOrder="selectedWorkOrder" :showModal="showWorkOrderModal" :users="users"
      @close="closeWorkOrderModal" />

    <div
      class="fixed mt-[80px] top-0 left-0 right-0 z-10 backdrop-blur-md bg-white/50 dark:bg-gray-800/60 glass-header">
      <div class="max-w-7xl mx-auto py-2 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <h2 class="font-semibold text-xl text-gray-800 dark:text-lime-400 leading-tight">
            Active Work Orders
            <CurrentTime />
          </h2>
          <div class="flex space-x-4">
            <TeamDropdown :teams="props.teams" />
            <AddWorkOrder />
            <button @click="openArchivedModal"
              class="text-purple-400 btn hover:bg-purple-400 hover:text-gray-900 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
              </svg>
              View Archive
            </button>
          </div>
        </div>
      </div>
    </div>

  </AppLayout>
</template>

<style scoped>
.scrollable-container {
  max-height: calc(100vh - 300px);
  overflow-y: auto;
  overflow-x: hidden;
}

.glass-container {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.18);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
}

.glass-header {
  background: rgba(17, 24, 39, 0.95);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.glass-row {
  background: rgba(255, 255, 255, 0.02);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

.table-wrapper {
  overflow-y: auto;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  position: relative;
}

thead th {
  position: sticky;
  top: 0;
  z-index: 1;
}

td {
  word-break: normal;
  white-space: normal;
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  .glass-container {
    background: rgba(30, 30, 30, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.25);
  }
  
  .glass-header {
    background: rgba(17, 24, 39, 0.95);
  }
  
  .glass-row {
    background: rgba(30, 30, 30, 0.2);
  }
  
 
}
</style>