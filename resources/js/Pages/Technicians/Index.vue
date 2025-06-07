<template>
  <AppLayout title="Technicians">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-lime-400">
        Technicians
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-gray-900/50 shadow-xl backdrop-blur-xl sm:rounded-lg">
          <!-- Search and Add New section -->
          <div class="p-6 flex justify-between items-center border-b border-gray-700">
            <div class="relative">
              <input
                v-model="search"
                type="text"
                placeholder="Search technicians..."
                class="pl-10 pr-4 py-2 rounded-lg bg-gray-800/50 border border-gray-700 text-white placeholder-gray-400 focus:outline-none focus:border-lime-500"
              />
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
            <AddTechnicianButton @technician-added="handleTechnicianAdded" />
          </div>

          <!-- Technicians Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
              <thead>
                <tr>
                  <th class="px-6 py-3 text-left text-sm font-medium text-lime-400 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-lime-400 uppercase tracking-wider">Employee ID</th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-lime-400 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-lime-400 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-lime-400 uppercase tracking-wider">Phone</th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-lime-400 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-700">
                <tr v-for="technician in filteredTechnicians" :key="technician.id" class="hover:bg-gray-800/30">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div v-if="technician.profile_picture" class="flex-shrink-0 h-10 w-10">
                        <img :src="technician.profile_picture" class="h-10 w-10 rounded-full" />
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-white">
                          {{ technician.first_name }} {{ technician.last_name }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                    {{ technician.employee_id }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                        technician.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      ]"
                    >
                      {{ technician.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                    {{ technician.email }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                    {{ technician.phone_number }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                    <Link
                      :href="route('technicians.show', technician.id)"
                      class="text-lime-400 hover:text-lime-500"
                    >
                      View Details
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import AddTechnicianButton from '@/Components/AddTechnicianButton.vue'

const search = ref('')
const technicians = ref([])

const fetchTechnicians = async () => {
  try {
    const response = await axios.get('/api/technicians')
    technicians.value = response.data
  } catch (e) {
    // Optionally handle error
    technicians.value = []
  }
}

onMounted(fetchTechnicians)

const filteredTechnicians = computed(() => {
  const searchTerm = search.value.toLowerCase()
  if (!searchTerm) return technicians.value
  return technicians.value.filter(tech =>
    (tech.first_name || '').toLowerCase().includes(searchTerm) ||
    (tech.last_name || '').toLowerCase().includes(searchTerm) ||
    (tech.email || '').toLowerCase().includes(searchTerm) ||
    (tech.employee_id || '').toLowerCase().includes(searchTerm)
  )
})

function handleTechnicianAdded() {
  fetchTechnicians()
}
</script>
