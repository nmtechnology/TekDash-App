<template>
  <AppLayout :title="'Technician - ' + technician.first_name + ' ' + technician.last_name">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold leading-tight text-lime-400">
          Technician Details
        </h2>
        <Link
          :href="route('technicians.index')"
          class="px-4 py-2 bg-gray-800 text-lime-400 rounded-lg hover:bg-gray-700 transition-colors duration-200"
        >
          Back to List
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-gray-900/50 overflow-hidden shadow-xl sm:rounded-lg backdrop-blur-xl">
          <div class="p-6 border-b border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Basic Information -->
              <div class="space-y-4">
                <h3 class="text-lg font-medium text-lime-400">Basic Information</h3>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-400">First Name</label>
                    <input
                      v-model="form.first_name"
                      type="text"
                      class="mt-1 block w-full rounded-md bg-gray-800/50 border-gray-700 text-white focus:border-lime-500 focus:ring-lime-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-400">Last Name</label>
                    <input
                      v-model="form.last_name"
                      type="text"
                      class="mt-1 block w-full rounded-md bg-gray-800/50 border-gray-700 text-white focus:border-lime-500 focus:ring-lime-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-400">Email</label>
                    <input
                      v-model="form.email"
                      type="email"
                      class="mt-1 block w-full rounded-md bg-gray-800/50 border-gray-700 text-white focus:border-lime-500 focus:ring-lime-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-400">Phone Number</label>
                    <input
                      v-model="form.phone_number"
                      type="tel"
                      class="mt-1 block w-full rounded-md bg-gray-800/50 border-gray-700 text-white focus:border-lime-500 focus:ring-lime-500"
                    />
                  </div>
                </div>
              </div>

              <!-- Employment Information -->
              <div class="space-y-4">
                <h3 class="text-lg font-medium text-lime-400">Employment Information</h3>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-400">Employee ID</label>
                    <input
                      v-model="form.employee_id"
                      type="text"
                      class="mt-1 block w-full rounded-md bg-gray-800/50 border-gray-700 text-white focus:border-lime-500 focus:ring-lime-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-400">Hire Date</label>
                    <input
                      v-model="form.hire_date"
                      type="date"
                      class="mt-1 block w-full rounded-md bg-gray-800/50 border-gray-700 text-white focus:border-lime-500 focus:ring-lime-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-400">Pay Rate</label>
                    <input
                      v-model="form.pay_rate"
                      type="number"
                      step="0.01"
                      class="mt-1 block w-full rounded-md bg-gray-800/50 border-gray-700 text-white focus:border-lime-500 focus:ring-lime-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-400">Status</label>
                    <select
                      v-model="form.is_active"
                      class="mt-1 block w-full rounded-md bg-gray-800/50 border-gray-700 text-white focus:border-lime-500 focus:ring-lime-500"
                    >
                      <option :value="true">Active</option>
                      <option :value="false">Inactive</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Additional Information -->
              <div class="space-y-4">
                <h3 class="text-lg font-medium text-lime-400">Additional Information</h3>
                <div>
                  <label class="block text-sm font-medium text-gray-400">Address</label>
                  <textarea
                    v-model="form.address"
                    rows="3"
                    class="mt-1 block w-full rounded-md bg-gray-800/50 border-gray-700 text-white focus:border-lime-500 focus:ring-lime-500"
                  ></textarea>
                </div>
              </div>

              <!-- Skills and Certifications -->
              <div class="space-y-4">
                <h3 class="text-lg font-medium text-lime-400">Skills and Certifications</h3>
                <div>
                  <label class="block text-sm font-medium text-gray-400">Certifications</label>
                  <textarea
                    v-model="form.certifications"
                    placeholder="Enter certifications (comma-separated)"
                    rows="2"
                    class="mt-1 block w-full rounded-md bg-gray-800/50 border-gray-700 text-white focus:border-lime-500 focus:ring-lime-500"
                  ></textarea>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-400">Specializations</label>
                  <textarea
                    v-model="form.specializations"
                    placeholder="Enter specializations (comma-separated)"
                    rows="2"
                    class="mt-1 block w-full rounded-md bg-gray-800/50 border-gray-700 text-white focus:border-lime-500 focus:ring-lime-500"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Save Button -->
            <div class="mt-6 flex justify-end">
              <button
                @click="saveTechnician"
                class="px-4 py-2 bg-lime-600 text-white rounded-lg hover:bg-lime-700 transition-colors duration-200"
              >
                Save Changes
              </button>
            </div>
          </div>
        </div>
        
        <!-- Recent Work Orders Section -->
        <div class="mt-8 bg-gray-900/50 overflow-hidden shadow-xl sm:rounded-lg backdrop-blur-xl">
          <div class="p-6">
            <h3 class="text-lg font-medium text-lime-400 mb-4">Recent Work Orders</h3>
            
            <div v-if="recentWorkOrders && recentWorkOrders.length > 0" class="overflow-x-auto">
              <table class="min-w-full bg-gray-800/50 text-white rounded-lg overflow-hidden">
                <thead class="bg-gray-800">
                  <tr>
                    <th class="px-4 py-3 text-left text-sm font-medium text-lime-400">Title</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-lime-400">Customer</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-lime-400">Status</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-lime-400">Date</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-lime-400">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="order in recentWorkOrders" :key="order.id" class="border-t border-gray-700">
                    <td class="px-4 py-3">{{ order.title }}</td>
                    <td class="px-4 py-3">{{ order.customer ? order.customer.business_name : 'N/A' }}</td>
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
                    <td class="px-4 py-3">
                      <Link :href="route('work-orders.show', order.id)" class="text-blue-400 hover:underline">View</Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="bg-gray-800/50 p-4 rounded-lg">
              <p class="text-gray-400">No recent work orders found for this technician.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { format, parseISO } from 'date-fns'

const props = defineProps({
  technician: {
    type: Object,
    required: true
  },
  recentWorkOrders: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  first_name: props.technician.first_name,
  last_name: props.technician.last_name,
  email: props.technician.email,
  phone_number: props.technician.phone_number,
  address: props.technician.address,
  employee_id: props.technician.employee_id,
  hire_date: props.technician.hire_date,
  certifications: props.technician.certifications?.join(', ') || '',
  specializations: props.technician.specializations?.join(', ') || '',
  is_active: props.technician.is_active,
  pay_rate: props.technician.pay_rate,
})

const saveTechnician = () => {
  form.put(route('technicians.update', props.technician.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Show success message
    },
  })
}

// Format date for work order display
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  try {
    return format(parseISO(dateString), 'MMM d, yyyy h:mm a')
  } catch (error) {
    return dateString
  }
}
</script>
