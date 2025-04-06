<script setup>
import { ref, computed } from 'vue';
import format from 'date-fns/format';
import { usePage, router } from '@inertiajs/vue3';
import AddWorkorder from '@/Pages/WorkOrders/AddWorkOrder.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import WorkOrder from './WorkOrder.vue';
import axios from 'axios';
import CurrentTime from '@/Components/CurrentTime.vue';
import TeamDropdown from '@/Components/TeamDropdown.vue';
import ArchivedWorkOrders from './ArchivedWorkOrders.vue';
import Stats from '@/Components/Stats.vue';

const { props } = usePage();
const workOrders = ref(props.workOrders || []);
const users = ref(props.users || []);
const searchQuery = ref('');
const showArchivedModal = ref(false); // Add this inside setup

// Sort workOrders by creation date in descending order
workOrders.value.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

const selectedWorkOrder = ref(null);
const showModal = ref(false);

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

const getUserName = (userId) => {
  const user = users.value.find(user => user.id === userId);
  return user ? user.name : 'N/A';
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

// Regular work order modal state
const showWorkOrderModal = ref(false);

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
  
  // Refresh the work order list
  router.reload({ only: ['workOrders'] });
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

const openWorkOrder = (workOrder) => {
  selectedWorkOrder.value = workOrder;
  showModal.value = true;
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
  let filtered = workOrders.value;

  // Status filtering from dropdown
  if (selectedStatus.value) {
    filtered = filtered.filter(workOrder => 
      workOrder.status?.toLowerCase() === selectedStatus.value.toLowerCase()
    );
  }
  // Status filtering from stats component
  else if (selectedStatFilter.value) {
    filtered = filtered.filter(workOrder => 
      workOrder.status?.toLowerCase() === selectedStatFilter.value.toLowerCase()
    );
  }

  // Search query filtering
  if (searchQuery.value) {
    filtered = filtered.filter(workOrder => 
      workOrder.title?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      workOrder.description?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      workOrder.customer_id?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      getUserName(workOrder.user_id)?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  return filtered;
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
    <div class="bg-gray-900/55 min-h-screen opacity-70 py-10 flex justify-center">
      <div class="w-full px-4">
        <div class="mt-40 flow-root">
          <div class="overflow-x-auto">
            <div class="inline-block min-w-full py-2 align-middle px-2">
              <div class="overflow-hidden border-b border-accent shadow sm:rounded-lg glass-container">
                <div class="sm:mt-0">
                  <Stats 
                    :stats="[
                      { name: 'Total', value: filteredData[0].stat.toString(), change: '', changeType: 'neutral' },
                      { name: 'Invoiced', value: filteredData[1].stat.toString(), change: '', changeType: 'neutral' },
                      { name: 'Archived', value: filteredData[2].stat.toString(), change: '', changeType: 'neutral' },
                      { name: 'Scheduled', value: filteredData[3].stat.toString(), change: '', changeType: 'neutral' },
                      { name: 'In Progress', value: filteredData[4].stat.toString(), change: '', changeType: 'neutral' },
                      { name: 'Part Needed', value: filteredData[5].stat.toString(), change: '', changeType: 'neutral' },
                      { name: 'Complete', value: filteredData[6].stat.toString(), change: '', changeType: 'neutral' },
                      { name: 'Cancelled', value: filteredData[7].stat.toString(), change: '', changeType: 'neutral' }
                    ]"
                    :collapsable="true"
                    title="Work Order Statistics"
                    @filterStats="handleFilterStats"
                  />
                </div>
                <div class="min-w-full">
                  <div class="px-4 py-3 flex items-center justify-between glass-header">
                    <div class="flex items-center space-x-4">
                      <!-- Search Input -->
                      <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search work orders..."
                        class="px-3 py-2 border border-gray-600 rounded-md bg-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-lime-500"
                      />
                      
                      <!-- Status Filter Dropdown -->
                      <select
                        v-model="selectedStatus"
                        class="px-3 py-2 border border-gray-600 rounded-md bg-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-lime-500"
                      >
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                          {{ option.label }}
                        </option>
                      </select>

                      <!-- Clear Filters Button -->
                      <button
                        v-if="selectedStatus || searchQuery"
                        @click="clearFilters"
                        class="px-3 py-2 text-red-400 hover:text-red-300 focus:outline-none"
                      >
                        Clear Filters
                      </button>
                    </div>

                    <!-- Results Counter -->
                    <div class="text-white">
                      {{ filteredWorkOrders.length }} work order(s) found
                    </div>
                  </div>
                  <table class="w-full divide-y divide-accent/10">
                    <thead class="glass-header">
                      <tr>
                        <!-- Removed Actions column -->
                        <th scope="col" class="sticky top-0 z-10 w-[25%] px-4 py-3.5 text-left text-xl font-semibold text-lime-400">Title</th>
                        <th scope="col" class="sticky top-0 z-10 w-[15%] px-4 py-3.5 text-left text-xl font-semibold text-lime-400">Status</th>
                        <th scope="col" class="sticky top-0 z-10 w-[20%] px-4 py-3.5 text-left text-xl font-semibold text-lime-400">Scheduled Time</th>
                        <th scope="col" class="sticky top-0 z-10 w-[15%] px-4 py-3.5 text-left text-xl font-semibold text-lime-400">Customer</th>
                        <th scope="col" class="sticky top-0 z-10 w-[10%] px-4 py-3.5 text-left text-xl font-semibold text-lime-400">User</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-accent/10 bg-transparent">
                      <tr v-for="workOrder in paginatedWorkOrders" :key="workOrder.id" @click="openWorkOrder(workOrder)" class="cursor-pointer hover:bg-gray-700">
                        <td class="py-4 px-4 text-sm">
                          <div class="font-medium text-white" :title="workOrder.title">{{ workOrder.title }}</div>
                          <div v-if="isPartOfMultiDayWorkOrder(workOrder)" class="text-xs text-gray-500">
                            Part of a multi-day work order
                          </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-accent">
                          <div class="flex flex-col">
                            <div class="text-white mb-1">{{ workOrder.status }}</div>
                            <div class="w-full bg-gray-700 rounded-full h-2.5">
                              <div class="h-2.5 rounded-full" 
                                   :class="getStatusColor(workOrder.status)"
                                   :style="{ width: getStatusProgress(workOrder.status) + '%' }">
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-accent">
                          <div class="text-white">{{ formatDate(workOrder.date_time) }}</div>
                        </td>
                        <td class="px-4 py-4 text-sm text-accent">
                          <div class="text-white" :title="workOrder.customer_id">{{ workOrder.customer_id }}</div>
                        </td>
                        <td class="px-4 py-4 text-sm text-accent">
                          <div class="text-white" :title="getUserName(workOrder.user_id)">{{ getUserName(workOrder.user_id) }}</div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="mt-4 flex justify-between fixed bottom-0 left-0 right-0 bg-gray-900 p-4">
          <button @click="prevPage" :disabled="currentPage === 1" class="px-4 py-2 bg-gray-700 text-white rounded disabled:opacity-50">Previous</button>
          <span class="text-white">Page {{ currentPage }} of {{ totalPages }}</span>
          <button @click="nextPage" :disabled="currentPage === totalPages" class="px-4 py-2 bg-gray-700 text-white rounded disabled:opacity-50">Next</button>
        </div>
        <WorkOrder
          v-if="selectedWorkOrder"
          :workOrder="selectedWorkOrder"
          :showModal="showModal"
          :users="users"
          @close="closeModal"
        />
        <!-- Remove or modify the ArchivedWorkOrderModal component usage -->
        <ArchivedWorkOrders 
          :is-open="showArchived"
          @close="closeArchivedModal"
          @view-work-order="viewArchivedWorkOrder"
        />
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