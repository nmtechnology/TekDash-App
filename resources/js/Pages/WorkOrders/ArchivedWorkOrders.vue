<template>
  <div class="fixed inset-0 z-[9999] flex items-center justify-center">
    <!-- Semi-transparent overlay -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md"></div>

    <!-- Modal container -->
    <div class="mt-10 glossy-card w-full max-w-4xl mx-4 sm:mx-auto rounded-lg text-left shadow-xl transform transition-all relative z-[10000] max-h-[80vh]">
      <!-- Header -->
      <div class="glossy-header px-4 pt-3 pb-3 sm:p-4 border-b border-gray-700/50">
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-medium text-lime-400">Archived Work Orders</h3>
          <button @click="$emit('close')" class="text-red-500 btn hover:text-black hover:bg-red-500">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="overflow-y-auto p-4">
        <div v-if="loading" class="text-center text-gray-400">Loading...</div>
        <div v-else-if="archivedOrders.length === 0" class="text-center text-gray-400">
          No archived work orders found
        </div>
        <div v-else class="space-y-4">
          <div v-for="order in archivedOrders" :key="order.id" 
               class="glossy-section p-4 rounded-lg hover:bg-gray-800/50 transition-colors">
            <div class="flex justify-between items-start">
              <div>
                <h4 class="text-lime-400 font-medium">{{ order.title }}</h4>
                <p class="text-sm text-gray-400">{{ order.customer_id }}</p>
                <p class="text-xs text-gray-500">
                  Archived: {{ formatDate(order.archived_at) }}
                </p>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-400">${{ order.price }}</span>
                <button 
                  @click="viewWorkOrder(order)"
                  class="text-lime-400 btn hover:bg-lime-400 hover:text-gray-900"
                >
                  View Details
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { format } from 'date-fns';

export default {
  name: 'ArchivedWorkOrders',
  
  emits: ['close', 'view-work-order'],
  
  setup(props, { emit }) {
    const archivedOrders = ref([]);
    const loading = ref(true);

    const loadArchivedOrders = async () => {
      try {
        const response = await axios.get('/archived-work-orders');
        archivedOrders.value = response.data;
      } catch (error) {
        console.error('Failed to load archived orders:', error);
      } finally {
        loading.value = false;
      }
    };

    const formatDate = (date) => {
      return format(new Date(date), 'MMM d, yyyy h:mm a');
    };

    const viewWorkOrder = (order) => {
      emit('view-work-order', order);
    };

    onMounted(loadArchivedOrders);

    return {
      archivedOrders,
      loading,
      formatDate,
      viewWorkOrder
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
