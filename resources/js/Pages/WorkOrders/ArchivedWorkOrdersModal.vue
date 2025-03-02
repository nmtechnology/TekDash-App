<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import { format } from 'date-fns'

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close', 'workOrderRestored'])

const archivedWorkOrders = ref([])
const loading = ref(true)
const search = ref('')
const error = ref(null)
const restoringId = ref(null)

const fetchArchivedWorkOrders = async () => {
    try {
        loading.value = true
        error.value = null
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
        error.value = 'Failed to load archived work orders. Please try again.'
    } finally {
        loading.value = false
    }
}

const restoreWorkOrder = async (workOrderId) => {
    try {
        restoringId.value = workOrderId
        await axios.post(`/api/work-orders/${workOrderId}/restore`)
        archivedWorkOrders.value = archivedWorkOrders.value.filter(order => order.id !== workOrderId)
        emit('workOrderRestored', workOrderId)
    } catch (error) {
        console.error('Error restoring work order:', error)
        error.value = 'Failed to restore work order. Please try again.'
    } finally {
        restoringId.value = null
    }
}

const filteredWorkOrders = computed(() => {
    if (!search.value) return archivedWorkOrders.value
    
    const searchLower = search.value.toLowerCase()
    return archivedWorkOrders.value.filter(order => 
        (order.title?.toLowerCase().includes(searchLower)) ||
        (order.customer?.name?.toLowerCase().includes(searchLower)) ||
        (order.id.toString().includes(searchLower)) ||
        (order.status?.toLowerCase().includes(searchLower))
    )
})

const closeModal = () => {
    emit('close')
    search.value = ''
    error.value = null
}

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        fetchArchivedWorkOrders()
    }
})

onMounted(() => {
    if (props.isOpen) {
        fetchArchivedWorkOrders()
    }
})
</script>

<template>
    <!-- Modal backdrop -->
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-70 z-40 flex justify-center items-center overflow-y-auto">
        <!-- Modal container -->
        <div class="bg-gray-800 rounded-lg shadow-xl w-full max-w-6xl mx-4 my-6 max-h-[90vh] flex flex-col text-gray-200">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-700">
                <div class="flex items-center space-x-4 flex-1">
                    <h2 class="font-semibold text-xl text-green-400 leading-tight">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-lime-400 uppercase tracking-wider">
                                    Actions
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
                            <tr v-for="order in filteredWorkOrders" :key="order.id" class="hover:bg-gray-700">
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button 
                                        @click="restoreWorkOrder(order.id)" 
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
