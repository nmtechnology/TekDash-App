<template>
  <!-- Modal backdrop -->
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-70 z-40 flex justify-center items-center overflow-y-auto">
      <!-- Modal container -->
      <div class="bg-gray-800 rounded-lg shadow-xl w-full max-w-6xl mx-4 my-6 max-h-[90vh] flex flex-col text-gray-200">
          <!-- Modal header -->
          <div class="flex items-center justify-between p-4 border-b border-gray-700">
              <div class="flex items-center space-x-4 flex-1">
                  <h2 class="font-semibold text-xl text-green-400">
                      Archived Work Orders
                  </h2>
                  <!-- Added search bar next to title -->
                  <div class="flex-1 max-w-md">
                      <div class="relative">
                          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                              </svg>
                          </div>
                          <input
                              type="text"
                              v-model="search"
                              placeholder="Search work orders..."
                              class="pl-10 w-full px-4 py-2 border rounded-lg bg-gray-700 border-gray-600 text-gray-200 focus:border-lime-400 focus:ring focus:ring-lime-300 focus:ring-opacity-50"
                          >
                      </div>
                  </div>
              </div>
              <button @click="closeModal" class="text-gray-400 hover:text-lime-400 transition">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
              </button>
          </div>

          <!-- Modal body -->
          <div class="flex-1 overflow-y-auto p-6">
              <!-- Error Message -->
              <div v-if="error" class="mb-4 bg-red-900 border border-red-500 text-red-100 px-4 py-3 rounded relative" role="alert">
                  <strong class="font-bold">Error! </strong>
                  <span class="block sm:inline">{{ error }}</span>
                  <button @click="error = null" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                      <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                  </button>
              </div>

              <!-- Work Orders Table -->
              <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-700">
                      <thead class="bg-gray-700">
                          <tr>
                              <th class="px-6 py-3 text-left text-xs font-medium text-lime-400 uppercase tracking-wider">
                                  Actions
                              </th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-lime-400 uppercase tracking-wider">
                                  ID
                              </th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-lime-400 uppercase tracking-wider">
                                  Date
                              </th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-lime-400 uppercase tracking-wider">
                                  Title
                              </th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-lime-400 uppercase tracking-wider">
                                  Customer
                              </th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-lime-400 uppercase tracking-wider">
                                  Price
                              </th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-lime-400 uppercase tracking-wider">
                                  Status
                              </th>
                              <th class="px-6 py-3 text-left text-xs font-medium text-lime-400 uppercase tracking-wider">
                                  Archived Date
                              </th>
                          </tr>
                      </thead>
                      <tbody class="bg-gray-800 divide-y divide-gray-700">
                          <tr v-if="loading">
                              <td colspan="8" class="px-6 py-10 text-center">
                                  <div class="flex justify-center items-center">
                                      <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-lime-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                      </svg>
                                      <span>Loading work orders...</span>
                                  </div>
                              </td>
                          </tr>
                          <tr v-else-if="filteredWorkOrders.length === 0">
                              <td colspan="8" class="px-6 py-4 text-center text-gray-400">
                                  No archived work orders found
                              </td>
                          </tr>
                          <tr v-for="order in filteredWorkOrders" 
                              :key="order.id" 
                              class="hover:bg-gray-700 cursor-pointer"
                              @click="viewWorkOrder(order)">
                              <td class="px-6 py-4 whitespace-nowrap">
                                  <div class="flex space-x-2">
                                      <button 
                                          @click.stop="restoreWorkOrder(order.id)" 
                                          class="text-lime-400 hover:text-lime-300 focus:outline-none flex items-center"
                                          :disabled="restoringId === order.id">
                                          <span v-if="restoringId === order.id" class="flex items-center">
                                              <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                              </svg>
                                              Restoring...
                                          </span>
                                          <span v-else class="flex items-center">
                                              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                              </svg>
                                              Restore
                                          </span>
                                      </button>
                                      <button 
                                          @click.stop="createInvoice(order)"
                                          class="text-blue-400 hover:text-blue-300 focus:outline-none flex items-center"
                                          :disabled="invocingId === order.id">
                                          <span v-if="invocingId === order.id" class="flex items-center">
                                              <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                              </svg>
                                              Creating Invoice...
                                          </span>
                                          <span v-else class="flex items-center">
                                              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                              </svg>
                                              Invoice
                                          </span>
                                      </button>
                                  </div>
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap">
                                  {{ order.id }}
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap">
                                  {{ order.formatted_date }}
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap">
                                  {{ order.title }}
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap">
                                  {{ order.customer?.name || 'N/A' }}
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap">
                                  {{ order.formatted_price }}
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap">
                                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="{
                                            'bg-green-900 text-green-200': order.status === 'Completed',
                                            'bg-blue-900 text-blue-200': order.status === 'In Progress',
                                            'bg-yellow-900 text-yellow-200': order.status === 'Pending',
                                            'bg-red-900 text-red-200': order.status === 'Cancelled',
                                            'bg-gray-700 text-gray-200': !order.status
                                        }">
                                      {{ order.status || 'Unknown' }}
                                  </span>
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap">
                                  {{ order.formatted_archived_at }}
                              </td>
                          </tr>
                      </tbody>
                  </table>
              </div>
          </div>

          <!-- Modal footer -->
          <div class="px-4 py-3 border-t border-gray-700 bg-gray-900 flex justify-end">
              <button @click="closeModal" class="px-4 py-2 bg-gray-700 text-gray-200 rounded hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-opacity-50 transition">
                  Close
              </button>
          </div>
      </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import axios from 'axios';
import { format, parseISO, isValid } from 'date-fns';

export default {
  name: 'ArchivedWorkOrders',
  
  props: {
    isOpen: {
      type: Boolean,
      default: false
    }
  },
  
  emits: ['close', 'view-work-order', 'invoice-created', 'update-archived-count'],
  
  setup(props, { emit }) {
    const mounted = ref(false);
    const archivedOrders = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const search = ref('');
    const restoringId = ref(null);
    const invocingId = ref(null);

    const formatDate = (dateString) => {
      if (!dateString) return 'N/A';
      try {
        const date = parseISO(dateString);
        return isValid(date) ? format(date, 'MMM d, yyyy h:mm a') : 'Invalid date';
      } catch {
        return 'Invalid date';
      }
    };

    const formatPrice = (price) => {
      try {
        // Convert string prices to numbers and handle null/undefined
        const numPrice = price ? parseFloat(price) : 0;
        if (isNaN(numPrice)) return '$0.00';
        
        return new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: 'USD',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(numPrice);
      } catch {
        return '$0.00';
      }
    };

    const processWorkOrder = (order) => {
      try {
        return {
          ...order,
          id: order.id || 'N/A',
          title: order.title || 'Untitled',
          status: order.status || 'Unknown',
          // Update customer handling
          customer: {
            name: order.customer?.name || order.customer_id || 'N/A',
            id: order.customer?.id || order.customer_id || 'N/A'
          },
          formatted_date: formatDate(order.created_at),
          formatted_archived_at: formatDate(order.archived_at),
          formatted_price: formatPrice(order.price || 0)
        };
      } catch (err) {
        console.error('Error processing work order:', err);
        return null;
      }
    };

    const loadArchivedOrders = async () => {
      if (!mounted.value) return;
      
      loading.value = true;
      error.value = null;
      
      try {
        const response = await axios.get('/archived-work-orders');
        await nextTick();
        // Process orders and emit total count
        archivedOrders.value = (response.data || [])
          .map(order => {
            const processed = processWorkOrder(order);
            return processed;
          })
          .filter(Boolean);
        
        // Emit the count immediately after loading
        emit('update-archived-count', archivedOrders.value.length);
      } catch (err) {
        error.value = 'Failed to load archived orders. Please try again.';
        console.error('Failed to load archived orders:', err);
        archivedOrders.value = [];
        emit('update-archived-count', 0);
      } finally {
        loading.value = false;
      }
    };

    const filteredWorkOrders = computed(() => {
      if (!mounted.value) return [];
      try {
        if (!search.value) return archivedOrders.value;
        const searchTerm = search.value.toLowerCase().trim();
        return archivedOrders.value.filter(order => {
          if (!order) return false;
          try {
            return order.id.toString().includes(searchTerm) ||
                   (order.title || '').toLowerCase().includes(searchTerm) ||
                   (order.customer?.name || '').toLowerCase().includes(searchTerm);
          } catch {
            return false;
          }
        });
      } catch {
        return [];
      }
    });

    watch(() => props.isOpen, async (newValue) => {
      if (newValue && mounted.value) {
        await loadArchivedOrders();
      }
    });

    onMounted(() => {
      mounted.value = true;
      if (props.isOpen) {
        loadArchivedOrders();
      }
    });

    const closeModal = () => {
      if (mounted.value) {
        emit('close');
      }
    };

    const restoreWorkOrder = async (id) => {
      restoringId.value = id;
      try {
        await axios.post(`/work-orders/${id}/restore`);
        archivedOrders.value = archivedOrders.value.filter(order => order.id !== id);
      } catch (err) {
        error.value = 'Failed to restore work order';
      } finally {
        restoringId.value = null;
      }
    };

    const createInvoice = async (order) => {
      if (invocingId.value || !mounted.value) return;
      
      invocingId.value = order.id;
      error.value = null;
      
      try {
        const response = await axios.post(`/work-orders/${order.id}/create-invoice`);
        if (response.data.success) {
          // Remove from archived orders if needed
          archivedOrders.value = archivedOrders.value.filter(o => o.id !== order.id);
          emit('invoice-created', {
            workOrderId: order.id,
            invoiceId: response.data.invoiceId,
            message: response.data.message
          });
        }
      } catch (err) {
        console.error('Failed to create invoice:', err);
        error.value = 'Failed to create QuickBooks invoice. Please try again.';
      } finally {
        invocingId.value = null;
      }
    };

    const viewWorkOrder = (order) => {
      if (mounted.value && !restoringId.value) {
        emit('view-work-order', order);
      }
    };

    return {
      archivedOrders,
      loading,
      closeModal,
      error,
      search,
      filteredWorkOrders,
      restoringId,
      restoreWorkOrder,
      viewWorkOrder,
      invocingId,
      createInvoice
    };
  }
};
</script>

<style scoped>
.glossy-section {
  background: linear-gradient(145deg, rgba(17, 24, 39, 0.5), rgba(31, 41, 55, 0.3));
  border: 1px solid rgba(255, 255, 255, 0.05);
}
</style>
