<template>
  <div class="fixed inset-0 z-[9999] flex items-center justify-center">
    <!-- Semi-transparent overlay with blur -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close"></div>
    
    <!-- Modal container -->
    <div class="inline-block align-bottom bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl mx-4 sm:mx-auto border border-gray-700 relative z-[10000]">
      <!-- Modal header -->
      <div class="bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-700">
        <div class="sm:flex sm:items-start">
          <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
            <!-- Header with close button -->
            <div class="flex justify-between items-center mb-4">
              <div class="flex items-center space-x-3 flex-grow">
                <h3 class="text-lg leading-6 font-medium text-lime-400" id="modal-title">
                  <span class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ workOrder?.title || 'Work Order' }} - Archived
                  </span>
                </h3>
              </div>
              
              <button @click="close" class="text-red-500 outline-dotted hover:text-gray-900 hover:bg-red-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            
            <!-- Success message -->
            <div class="bg-green-800 text-green-100 p-4 rounded-md mb-4">
              <p class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ message || 'Work order successfully archived' }}
              </p>
            </div>
            
            <!-- Work order summary -->
            <div class="text-gray-300 space-y-4">
              <!-- Invoice ID (if available) -->
              <div v-if="invoiceId" class="flex items-center">
                <span class="text-sm text-gray-400 mr-2">Invoice ID:</span>
                <span class="text-white font-medium">{{ invoiceId }}</span>
              </div>
              
              <!-- Customer -->
              <div class="flex justify-between items-start">
                <div>
                  <p class="text-sm text-gray-400">Customer:</p>
                  <p class="text-white">{{ workOrder?.customer_id || 'N/A' }}</p>
                </div>
                
                <div>
                  <p class="text-sm text-gray-400">Price:</p>
                  <p class="text-white">${{ workOrder?.price || '0.00' }}</p>
                </div>
              </div>
              
              <!-- Description (shortened) -->
              <div>
                <p class="text-sm text-gray-400">Description:</p>
                <p class="whitespace-pre-line mt-1 text-sm bg-gray-800 p-3 rounded-md text-lime-400 overflow-y-auto max-h-[120px] scrollbar-thin scrollbar-thumb-gray-600 scrollbar-track-gray-800 touch-auto">
                  {{ workOrder?.description || 'No description' }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Modal footer -->
      <div class="bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <button @click="viewAllArchived" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 outline text-lime-400 font-medium hover:bg-lime-400 hover:text-gray-900 sm:ml-3 sm:w-auto sm:text-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
          </svg>
          View All Archived Work Orders
        </button>
        <button @click="close" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-700 shadow-sm px-4 py-2 outline text-gray-400 font-medium hover:bg-gray-700 hover:text-white sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { router } from '@inertiajs/vue3';

export default {
  name: 'ArchivedWorkOrderModal',
  props: {
    workOrder: {
      type: Object,
      required: true
    },
    invoiceId: {
      type: String,
      default: ''
    },
    message: {
      type: String,
      default: 'Work order successfully archived'
    }
  },
  setup(props, { emit }) {
    const close = () => {
      emit('close');
    };
    
    const viewAllArchived = () => {
      router.visit('/archived-work-orders');
    };
    
    return {
      close,
      viewAllArchived
    };
  }
};
</script>

<style scoped>
/* Custom scrollbar for webkit browsers */
.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
}

.scrollbar-thumb-gray-600::-webkit-scrollbar-thumb {
  background-color: #4B5563;
  border-radius: 3px;
}

.scrollbar-track-gray-800::-webkit-scrollbar-track {
  background-color: #1F2937;
}

/* For Firefox */
.scrollbar-thin {
  scrollbar-width: thin;
  scrollbar-color: #4B5563 #1F2937;
}

/* Ensure scrolling works well on touch devices */
.touch-auto {
  -webkit-overflow-scrolling: touch;
}
</style>
