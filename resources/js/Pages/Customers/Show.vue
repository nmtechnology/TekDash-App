<template>
  <AppLayout :title="'Customer Details - ' + customer.business_name">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-lime-400 leading-tight">
          {{ customer.business_name }} Details
        </h2>
        <Link
          :href="route('customers.index')"
          class="px-4 py-2 bg-gray-800 text-lime-400 rounded-lg hover:bg-gray-700 transition-colors duration-200"
        >
          Back to Customers
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-gray-900/50 overflow-hidden shadow-xl sm:rounded-lg backdrop-blur-xl">
          <!-- Customer Details Section -->
          <div class="p-6 border-b border-gray-700">
            <div class="flex flex-col md:flex-row md:justify-between md:items-start">
              <!-- Main Customer Info -->
              <div class="md:w-1/2">
                <h3 class="text-xl font-bold text-lime-400 mb-4">Customer Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <p class="text-gray-400">Business Name</p>
                    <p class="text-white font-semibold">{{ customer.business_name }}</p>
                  </div>
                  <div>
                    <p class="text-gray-400">Address</p>
                    <p class="text-white">{{ customer.address }}</p>
                  </div>
                  <div>
                    <p class="text-gray-400">Contact Person</p>
                    <p class="text-white">{{ customer.poc_name }}</p>
                  </div>
                  <div>
                    <p class="text-gray-400">Contact Email</p>
                    <p class="text-white">{{ customer.poc_email }}</p>
                  </div>
                  <div>
                    <p class="text-gray-400">Pay Rate</p>
                    <p class="text-white">${{ customer.pay_rate }}/hr</p>
                  </div>
                  <div>
                    <p class="text-gray-400">Net Terms</p>
                    <p class="text-white">{{ customer.net_terms }}</p>
                  </div>
                  <div v-if="customer.fax">
                    <p class="text-gray-400">Fax</p>
                    <p class="text-white">{{ customer.fax }}</p>
                  </div>
                </div>
              </div>
              
              <!-- Quick Actions -->
              <div class="md:w-1/2 mt-6 md:mt-0">
                <h3 class="text-xl font-bold text-lime-400 mb-4">Quick Actions</h3>
                <div class="flex flex-wrap gap-3">
                  <Link
                    :href="route('customers.work-orders', { customer: customer.id })"
                    class="px-4 py-2 bg-gray-800 text-lime-400 rounded-lg hover:bg-gray-700 transition-colors duration-200"
                  >
                    <span class="flex items-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 1h6v4H7V6zm6 6H7v2h6v-2z" clip-rule="evenodd" />
                      </svg>
                      View Work Orders
                    </span>
                  </Link>
                  <AddWorkOrder :customerId="customer.id" :customerName="customer.business_name" />
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Work Orders Section -->
          <div class="p-6">
            <h3 class="text-xl font-bold text-lime-400 mb-4">Recent Work Orders</h3>
            
            <div v-if="recentWorkOrders && recentWorkOrders.length > 0">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                  <thead>
                    <tr>
                      <th class="px-6 py-3 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">ID</th>
                      <th class="px-6 py-3 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Title</th>
                      <th class="px-6 py-3 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                      <th class="px-6 py-3 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Technician</th>
                      <th class="px-6 py-3 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date</th>
                      <th class="px-6 py-3 bg-gray-800 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-gray-900 divide-y divide-gray-800">
                    <tr v-for="workOrder in recentWorkOrders" :key="workOrder.id">
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">#{{ workOrder.id }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ workOrder.title }}</td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                          :class="getStatusClass(workOrder.status)">
                          {{ workOrder.status }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ workOrder.technician ? workOrder.technician.name : 'Unassigned' }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ formatDate(workOrder.created_at) }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <Link :href="route('work-orders.show', { workOrder: workOrder.id })" class="text-lime-400 hover:text-lime-500">
                          View
                        </Link>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <div class="mt-4 flex justify-center">
                <Link 
                  :href="route('customers.work-orders', { customer: customer.id })"
                  class="px-4 py-2 bg-gray-800 text-lime-400 rounded-lg hover:bg-gray-700 transition-colors duration-200"
                >
                  View All Work Orders
                </Link>
              </div>
            </div>
            
            <div v-else class="bg-gray-800 rounded-lg p-6 text-center">
              <p class="text-gray-300">No work orders found for this customer.</p>
              <AddWorkOrder :customerId="customer.id" :customerName="customer.business_name" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AddWorkOrder from '@/Pages/WorkOrders/AddWorkOrder.vue';

const props = defineProps({
  customer: Object,
  recentWorkOrders: Array
});

// Format date to a readable format
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  }).format(date);
};

// Return appropriate class for work order status
const getStatusClass = (status) => {
  switch (status) {
    case 'Completed':
      return 'bg-green-900 text-green-200';
    case 'In Progress':
      return 'bg-blue-900 text-blue-200';
    case 'Scheduled':
      return 'bg-yellow-900 text-yellow-200';
    case 'Pending':
      return 'bg-orange-900 text-orange-200';
    case 'Cancelled':
      return 'bg-red-900 text-red-200';
    default:
      return 'bg-gray-900 text-gray-200';
  }
};
</script>
