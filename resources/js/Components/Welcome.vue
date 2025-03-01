<template>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-16">
        <div class="p-6 lg:p-8 bg-opacity-75 border-b border-blue-400">
            <h1 class="mt-8 mb-20 text-2xl font-medium text-white">
                Welcome to TekDash {{ $page.props.auth.user.name }}! 
            </h1>
            <p class="mt-6 mb-10 text-lime-400 leading-relaxed">
               <Calendar @workOrderSelected="openWorkOrderModal" />
               <WorkOrder 
      v-if="selectedWorkOrder" 
      :workOrder="selectedWorkOrder" 
      :showModal="showWorkOrderModal"
      @close="closeWorkOrderModal" 
    /><!-- Add any additional welcome text here -->
            </p>
        </div>

        <div class="bg-gray-800 mb-10 bg-opacity-65 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8 rounded-lg top-1">
            <div>
                <div class="flex items-center">
                   <GroqQuery class="mb-5"/> <!-- Add any additional content here -->
                   </div>
               
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import GroqQuery from '@/Pages/GroqQuery.vue';
import Calendar from './Calendar.vue';
import CurrentTime from '@/Components/CurrentTime.vue';
import { usePage } from '@inertiajs/vue3';
import WorkOrder from '@/Pages/WorkOrders/WorkOrder.vue';
import axios from 'axios';

// State for the work order modal
const showWorkOrderModal = ref(false);
const selectedWorkOrder = ref(null);
const isLoading = ref(false);

// Function to open the work order modal with complete data
async function openWorkOrderModal(workOrderId) {
  try {
    console.log('Opening work order with ID:', workOrderId);
    
    // Fetch the work order details with error handling
    const response = await axios.get(`/work-orders/${workOrderId}/details`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    console.log('Work order data received:', response.data);
    
    // Set the selected work order data
    selectedWorkOrder.value = response.data;
    
    // Show the modal
    showWorkOrderModal.value = true;
  } catch (error) {
    console.error('Error loading work order:', error);
    
    // Show more detailed error information
    if (error.response) {
      console.error('Response status:', error.response.status);
      console.error('Response data:', error.response.data);
    }
    
    // Fall back to a minimal work order object if fetch fails
    selectedWorkOrder.value = {
      id: workOrderId,
      title: 'Work Order ' + workOrderId,
      description: 'Could not load full details',
      user_id: null,
      status: 'Unknown',
      date_time: new Date().toISOString(),
      customer_id: 'Unknown',
      price: 0,
      notes: []
    };
    
    // Still show the modal with limited data
    showWorkOrderModal.value = true;
  }
}

// Function to close the work order modal
function closeWorkOrderModal() {
  showWorkOrderModal.value = false;
  selectedWorkOrder.value = null; // Clear the data when closing
}

const { props } = usePage();

</script>

