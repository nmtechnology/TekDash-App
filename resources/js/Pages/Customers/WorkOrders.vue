<template>
  <AppLayout :title="'Work Orders - ' + customer.business_name">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-lime-400 leading-tight">
          Work Orders for {{ customer.business_name }}
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
          <!-- Customer Details Card -->
          <div class="p-6 border-b border-gray-700">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center">
              <div>
                <h3 class="text-xl font-bold text-lime-400">{{ customer.business_name }}</h3>
                <p class="text-gray-400 mt-1">{{ customer.address }}</p>
              </div>
              <div class="mt-4 md:mt-0">
                <p class="text-white"><span class="text-gray-400">Contact:</span> {{ customer.poc_name }}</p>
                <p class="text-white"><span class="text-gray-400">Email:</span> {{ customer.poc_email }}</p>
              </div>
            </div>
          </div>
          
          <!-- Work Orders Table -->
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-medium text-lime-400">All Work Orders</h3>
              <AddWorkOrder :customer-id="customer.id" :customer-name="customer.business_name" />
            </div>
            
            <div v-if="workOrders.data.length > 0" class="overflow-x-auto">
              <table class="min-w-full bg-gray-800 text-white rounded-lg overflow-hidden">
                <thead class="bg-gray-700">
                  <tr>
                    <th class="px-4 py-3 text-left text-sm font-medium text-lime-400">ID</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-lime-400">Title</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-lime-400">Status</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-lime-400">Date</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-lime-400">Technician</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-lime-400">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="order in workOrders.data" :key="order.id" class="border-t border-gray-700">
                    <td class="px-4 py-3">{{ order.id }}</td>
                    <td class="px-4 py-3">{{ order.title }}</td>
                    <td class="px-4 py-3">
                      <span 
                        :class="{
                          'bg-green-800 text-green-100': order.status === 'Complete',
                          'bg-blue-800 text-blue-100': order.status === 'Scheduled',
                          'bg-yellow-800 text-yellow-100': order.status === 'In Progress',
                          'bg-red-800 text-red-100': order.status === 'Cancelled',
                          'bg-purple-800 text-purple-100': order.status === 'Part Needed'
                        }"
                        class="px-2 py-1 rounded-full text-xs">
                        {{ order.status }}
                      </span>
                    </td>
                    <td class="px-4 py-3">{{ formatDate(order.date_time || order.created_at) }}</td>
                    <td class="px-4 py-3">{{ order.user ? order.user.name : 'N/A' }}</td>
                    <td class="px-4 py-3">
                      <Link :href="route('work-orders.show', order.id)" class="text-blue-400 hover:underline">View</Link>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <div class="mt-4">
                <Pagination :links="workOrders.links" />
              </div>
            </div>
            <div v-else class="bg-gray-800 p-6 rounded-lg text-center">
              <p class="text-gray-400">No work orders found for this customer.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { defineComponent } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import AddWorkOrder from '@/Pages/WorkOrders/AddWorkOrder.vue'
import { format, parseISO } from 'date-fns'

export default defineComponent({
  components: {
    AppLayout,
    Link,
    Pagination,
    AddWorkOrder
  },
  
  props: {
    customer: {
      type: Object,
      required: true
    },
    workOrders: {
      type: Object,
      required: true
    }
  },
  
  methods: {
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      try {
        return format(parseISO(dateString), 'MMM d, yyyy h:mm a')
      } catch (error) {
        return dateString
      }
    }
  }
})
</script>
