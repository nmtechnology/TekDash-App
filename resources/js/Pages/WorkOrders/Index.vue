<template>
  <div class="bg-gray-900 min-h-screen opacity-70 py-10 flex justify-center">
    <div class="w-full max-w-6xl">
      <h2 class="px-4 text-4xl font-semibold text-white sm:px-6 lg:px-8">Active Work Orders</h2>
      <CurrentTime class="p-4"></CurrentTime>
      <div class="mt-6 w-full whitespace-nowrap text-left">
        <div class="sm:flex sm:items-center text-green-400 rounded-lg">
          <div class="sm:flex-auto">
            <p class="mt-2 text-sm text-green-400 p-4">Here you can add, update, duplicate and delete work orders.</p>
          </div>
          <div class="mt-4 sm:ml-16 sm:mt-0">
            <div class="p-4">
              <AddWorkorder class="justify-end" />
            </div>
          </div>
        </div>
        <div class="mt-8 flow-root">
          <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
              <div class="overflow-hidden border-b border-accent shadow sm:rounded-lg">
                <div class="table-wrapper">
                  <table class="min-w-full divide-y divide-accent table-fixed outline outline-green-400">
                    <thead class="bg-gray-900 backdrop-blur-md opacity-95">
                      <tr>
                        <th scope="col" class="sticky top-0 z-10 py-3.5 pl-4 pr-3 text-left text-xl font-semibold text-white sm:pl-0"></th>
                        <th scope="col" class="sticky top-0 z-10 py-3.5 pl-4 pr-3 text-left text-xl font-semibold text-white sm:pl-0">Title</th>
                        <th scope="col" class="sticky top-0 z-10 px-3 py-3.5 text-left text-xl font-semibold text-white">Description</th>
                        <th scope="col" class="sticky top-0 z-10 px-3 py-3.5 text-left text-xl font-semibold text-white">Scheduled Time</th>
                        <th scope="col" class="sticky top-0 z-10 px-3 py-3.5 text-left text-xl font-semibold text-white">Status</th>
                        <th scope="col" class="sticky top-0 z-10 px-3 py-3.5 text-left text-xl font-semibold text-white customer-column">Customer</th>
                        <th scope="col" class="sticky top-0 z-10 px-3 py-3.5 text-left text-xl font-semibold text-white">User</th>
                        <th scope="col" class="sticky top-0 z-10 px-3 py-3.5 text-left text-xl font-semibold text-white">Images</th>
                        <th scope="col" class="sticky top-0 z-10 px-3 py-3.5 text-left text-xl font-semibold text-white">Notes</th>
                        <th scope="col" class="sticky top-0 z-10 px-3 py-3.5 text-left text-xl font-semibold text-white">Price</th>
                        <th scope="col" class="sticky top-0 z-10 px-3 py-3.5 text-left text-xl font-semibold text-white">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-accent bg-transparent">
                      <tr v-for="(workOrder, index) in filteredWorkOrders" :key="index">
                        <td class="relative whitespace-nowrap py-5 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                          <button @click="openModal(workOrder)" class="text-yellow-400 hover:text-yellow-500">
                            View<span class="sr-only">, {{ workOrder.title }}</span>
                          </button>
                          <button @click="deleteWorkOrder(workOrder.id)" class="text-red-400 hover:text-red-500 ml-2">
                            Delete<span class="sr-only">, {{ workOrder.title }}</span>
                          </button>
                        </td>
                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="font-medium text-green-400">{{ workOrder.title }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-5 text-sm text-accent description-column">
                          <div class="text-green-400 truncate">{{ workOrder.description }}</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-5 text-sm text-accent">
                          <div class="text-green-400">{{ formatDate(workOrder.date_time) }}</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                          <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">{{ workOrder.status }}</span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-5 text-sm text-accent customer-column">
                          <div class="text-green-400">{{ workOrder.customer_id }}</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-5 text-sm text-accent">
                          <div class="text-green-400">{{ getUserName(workOrder.user_id) }}</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-5 text-sm text-accent">
                          <div class="flex space-x-2">
                            <img v-for="(image, index) in getImages(workOrder.images)" :key="index" :src="`/storage/${image}`" alt="Work Order Image" class="w-10 h-10 object-cover rounded">
                          </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-5 text-sm text-accent">
                          <div class="text-green-400 truncate">{{ workOrder.notes }}</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-5 text-sm text-accent">
                          <div class="text-green-400">{{ workOrder.price }}</div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="mt-4 flex justify-between">
          <button @click="prevPage" :disabled="currentPage === 1" class="px-4 py-2 bg-gray-700 text-white rounded disabled:opacity-50">Previous</button>
          <span class="text-white">Page {{ currentPage }} of {{ totalPages }}</span>
          <button @click="nextPage" :disabled="currentPage === totalPages" class="px-4 py-2 bg-gray-700 text-white rounded disabled:opacity-50">Next</button>
        </div>
        <WorkOrder v-if="selectedWorkOrder" :workOrder="selectedWorkOrder" :showModal="showModal" @close="closeModal" />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import format from 'date-fns/format';
import { usePage } from '@inertiajs/vue3';
import AddWorkorder from '@/Components/AddWorkOrder.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Alert from '@/Components/Alert.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import WorkOrder from './WorkOrder.vue';
import GroqQuery from '../GroqQuery.vue';
import axios from 'axios';
import CurrentTime from '@/Components/CurrentTime.vue';

export default {
  layout: AppLayout,
  name: 'WorkOrdersIndex',
  components: {
    AddWorkorder,
    Alert,
    ApplicationLogo,
    WorkOrder,
    GroqQuery,
    CurrentTime,
  },
  setup() {
    const { props } = usePage();
    const workOrders = ref(props.workOrders || []);
    const users = ref(props.users || []);
    const searchQuery = ref('');

    // Sort workOrders by creation date in descending order
    workOrders.value.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

    const selectedWorkOrder = ref(null);
    const showModal = ref(false);

    const openModal = (workOrder) => {
      selectedWorkOrder.value = workOrder;
      showModal.value = true;
    };

    const closeModal = () => {
      showModal.value = false;
      selectedWorkOrder.value = null;
    };

    const formatDate = (date) => {
      if (!date) return 'Invalid date';
      try {
        const parsedDate = new Date(date);
        if (isNaN(parsedDate)) {
          throw new Error('Invalid date');
        }
        return format(parsedDate, 'MMMM dd, yyyy hh:mm a');
      } catch (error) {
        console.error('Invalid date:', date);
        return 'Invalid date';
      }
    };

    const getUserName = (userId) => {
      const user = users.value.find(user => user.id === userId);
      return user ? user.name : 'N/A';
    };

    const getImages = (images) => {
      return Array.isArray(images) ? images : [];
    };

    const deleteWorkOrder = (id) => {
      if (confirm('Are you sure you want to delete this work order?')) {
        axios.delete(`/work-orders/${id}`)
          .then(response => {
            workOrders.value = workOrders.value.filter(workOrder => workOrder.id !== id);
            alert('Work order deleted successfully.');
          })
          .catch(error => {
            console.error('Error deleting work order:', error);
            alert('Failed to delete work order.');
          });
      }
    };

    // Pagination logic
    const currentPage = ref(1);
    const itemsPerPage = 10;

    const totalPages = computed(() => {
      return Math.ceil(filteredWorkOrders.value.length / itemsPerPage);
    });

    const paginatedWorkOrders = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage;
      const end = start + itemsPerPage;
      return filteredWorkOrders.value.slice(start, end);
    });

    const nextPage = () => {
      if (currentPage.value < totalPages.value) {
        currentPage.value++;
      }
    };

    const prevPage = () => {
      if (currentPage.value > 1) {
        currentPage.value--;
      }
    };

    // Search logic
    const filteredWorkOrders = computed(() => {
      if (!searchQuery.value) {
        return workOrders.value;
      }
      return workOrders.value.filter(workOrder => {
        return (
          workOrder.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          workOrder.description.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          workOrder.status.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          workOrder.customer_id.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          getUserName(workOrder.user_id).toLowerCase().includes(searchQuery.value.toLowerCase())
        );
      });
    });

    return {
      workOrders,
      users,
      selectedWorkOrder,
      showModal,
      openModal,
      closeModal,
      formatDate,
      getUserName,
      getImages,
      deleteWorkOrder,
      currentPage,
      totalPages,
      paginatedWorkOrders,
      nextPage,
      prevPage,
      searchQuery,
      filteredWorkOrders,
    };
  },
};
</script>

<style scoped>
.scrollable-container {
  max-height: 400px; /* Adjust the height as needed */
  overflow-y: auto;
}

.table-wrapper {
  max-height: 400px; /* Adjust the height as needed */
  overflow-y: auto;
  position: relative;
}

thead th {
  position: sticky;
  top: 0;
  background: #1f2937; /* Match the background color of the thead */
  z-index: 1;
}

.description-column {
  max-width: 150px; /* Adjust the max-width as needed */
  word-wrap: break-word;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.customer-column {
  max-width: 100px; /* Adjust the max-width as needed */
  word-wrap: break-word;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

td {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

img {
  display: inline-block;
  margin-right: 0.5rem;
}
</style>