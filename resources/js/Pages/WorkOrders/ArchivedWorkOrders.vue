<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import { format } from 'date-fns'

const archivedWorkOrders = ref([])
const loading = ref(true)
const search = ref('')

const fetchArchivedWorkOrders = async () => {
    try {
        loading.value = true
        const response = await axios.get('/api/work-orders/archived')
        archivedWorkOrders.value = response.data.map(order => ({
            ...order,
            formatted_date: format(new Date(order.date_time), 'MM/dd/yyyy'),
            formatted_archived_at: format(new Date(order.archived_at), 'MM/dd/yyyy HH:mm'),
            formatted_price: new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(order.price || 0)
        }))
    } catch (error) {
        console.error('Error fetching archived work orders:', error)
    } finally {
        loading.value = false
    }
}

const filteredWorkOrders = computed(() => {
    if (!search.value) return archivedWorkOrders.value
    
    const searchLower = search.value.toLowerCase()
    return archivedWorkOrders.value.filter(order => 
        order.title.toLowerCase().includes(searchLower) ||
        order.customer?.name.toLowerCase().includes(searchLower) ||
        order.id.toString().includes(searchLower)
    )
})

onMounted(() => {
    fetchArchivedWorkOrders()
})
</script>

<template>
    <AppLayout title="Archived Work Orders">
        <Head title="Archived Work Orders" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Archived Work Orders
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <!-- Search Input -->
                    <div class="mb-4">
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search work orders..."
                            class="w-full px-4 py-2 border rounded-lg"
                        >
                    </div>

                    <!-- Work Orders Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ID
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Title
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Customer
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Price
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Archived Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="loading" class="text-center">
                                    <td colspan="7" class="px-6 py-4">Loading...</td>
                                </tr>
                                <tr v-else-if="filteredWorkOrders.length === 0" class="text-center">
                                    <td colspan="7" class="px-6 py-4">No archived work orders found</td>
                                </tr>
                                <tr v-for="order in filteredWorkOrders" :key="order.id" class="hover:bg-gray-50">
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
                                        {{ order.customer?.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ order.formatted_price }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ order.status }}
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
            </div>
        </div>
    </AppLayout>
</template>
