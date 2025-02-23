<template>
  <div>
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-gray-700 rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-lg mx-4 sm:mx-auto">
        <div class="bg-gray-700 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-2xl leading-6 font-medium text-green-400" id="modal-title">
                Work Order Details
              </h3>
              <div class="mt-2">
                <div v-if="workOrder">
                  <div v-if="!isEditing">
                    <h1>{{ workOrder.title }}</h1>
                    <p>{{ workOrder.description }}</p>
                    <p>{{ formatDate(workOrder.scheduled_at) }}</p>
                    <p>{{ workOrder.status }}</p>
                    <p>{{ workOrder.price }}</p>
                    <p>{{ workOrder.customer_id }}</p>
                    <p>{{ workOrder.user_id }}</p>
                    <p>{{ workOrder.notes }}</p>
                    <div v-if="workOrder.images && workOrder.images.length">
                      <h4>Images:</h4>
                      <ul>
                        <li v-for="(image, index) in workOrder.images" :key="index">
                          <img :src="`/storage/${image}`" alt="Work Order Image" class="max-w-full h-auto" />
                        </li>
                      </ul>
                    </div>
                  </div>
                  <div v-else>
                    <form @submit.prevent="updateWorkOrder">
                      <div class="mb-4">
                        <label for="customer_id" class="text-green-400 block text-sm font-medium text-accent">Customer</label>
                        <select v-model="form.customer_id" id="customer_id" class="text-lime-400 mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                          <option value="Advanced Project Solutions">Advanced Project Solutions</option>
                          <option value="Barrister Global Service Network">Barrister Global Service Network</option>
                          <option value="DarAlIslam">DarAlIslam</option>
                          <option value="Field Nation">Field Nation</option>
                          <option value="Navco">Navco</option>
                          <option value="NEW CUSTOMER">NEW CUSTOMER</option>
                          <option value="NuTech National">NuTech National</option>
                          <option value="Telaid">Telaid</option>
                        </select>
                      </div>
                      <div class="mb-4">
                        <label for="user_id" class="block text-sm font-medium text-green-400">User ID</label>
                        <input type="text" v-model="form.user_id" id="user_id" class="mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                      </div>
                      <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-green-400">Title</label>
                        <input type="text" v-model="form.title" id="title" class="mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                      </div>
                      <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-green-400">Description</label>
                        <textarea v-model="form.description" id="description" class="mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required></textarea>
                      </div>
                      <div class="mb-4">
                        <label for="date_time" class="block text-sm font-medium text-green-400">Scheduled Date and Time</label>
                        <input type="datetime-local" v-model="form.date_time" id="date_time" class="mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                      </div>
                      <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-green-400">Status</label>
                        <select v-model="form.status" id="status" class="mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                          <option value="Scheduled">Scheduled</option>
                          <option value="In Progress">In Progress</option>
                          <option value="Part/Return">Part/Return</option>
                          <option value="Complete">Complete</option>
                          <option value="Cancelled">Cancelled</option>
                        </select>
                      </div>
                      <div class="mb-4">
                        <label for="price" class="block text-sm font-medium text-green-400">Price</label>
                        <input type="number" v-model="form.price" id="price" class="mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                      </div>
                      <div class="mb-4">
                        <label for="notes" class="block text-sm font-medium text-green-400">Notes</label>
                        <textarea v-model="form.notes" id="notes" class="mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm"></textarea>
                      </div>
                      <div class="px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse sm:mt-6 rounded">
                        <button type="submit" class="mt-6 w-full inline-flex justify-center rounded border border-gray-300 shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success sm:text-sm">
                          Save
                        </button>
                        <button @click="cancelEdit" type="button" class="mt-6 w-full inline-flex justify-center rounded border border-gray-300 shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success sm:text-sm">
                          Cancel
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
                <div v-else>
                  <p>Loading...</p>
                </div>
              </div>
              <div v-if="!isEditing" class="px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse sm:mt-6 rounded">
                <button @click="closeModal" type="button" class="mt-6 w-full inline-flex justify-center rounded border border-gray-300 shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success sm:text-sm">
                  Close
                </button>
                <button @click="enableEdit" type="button" class="mt-6 w-full inline-flex justify-center rounded border border-gray-300 shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success sm:text-sm">
                  Update
                </button>
                <button @click="duplicateWorkOrder" type="button" class="mt-6 w-full inline-flex justify-center rounded border border-gray-300 shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success sm:text-sm">
                  Duplicate
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
import { ref, watch } from 'vue';
import { format } from 'date-fns';
import { useForm } from '@inertiajs/vue3';

export default {
  name: 'WorkOrder',
  props: {
    workOrder: {
      type: Object,
      required: true,
    },
    showModal: {
      type: Boolean,
      required: true,
    },
  },
  setup(props, { emit }) {
    const isEditing = ref(false);
    const form = useForm({
      customer_id: props.workOrder.customer_id,
      user_id: props.workOrder.user_id,
      title: props.workOrder.title,
      description: props.workOrder.description,
      date_time: props.workOrder.scheduled_at,
      status: props.workOrder.status,
      price: props.workOrder.price,
      notes: props.workOrder.notes,
    });

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

    const closeModal = () => {
      emit('close');
    };

    const enableEdit = () => {
      isEditing.value = true;
    };

    const cancelEdit = () => {
      isEditing.value = false;
    };

    const updateWorkOrder = () => {
      form.put(`/work-orders/${props.workOrder.id}`, {
        onSuccess: () => {
          isEditing.value = false;
          emit('close');
        },
        onError: (errors) => {
          console.error('Error updating work order:', errors);
        },
      });
    };

    const duplicateWorkOrder = () => {
      form.post(`/work-orders/${props.workOrder.id}/duplicate`, {
        onSuccess: () => {
          emit('close');
        },
        onError: (errors) => {
          console.error('Error duplicating work order:', errors);
        },
      });
    };

    watch(() => props.showModal, (newVal) => {
      if (!newVal) {
        closeModal();
      }
    });

    return {
      isEditing,
      form,
      formatDate,
      closeModal,
      enableEdit,
      cancelEdit,
      updateWorkOrder,
      duplicateWorkOrder,
    };
  },
};
</script>

<style scoped>
.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
}

.btn-primary {
  background-color: hsl(90, 100%, 50%);
  color: white;
}

.fixed {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.5);
}

.bg-white {
  background-color: white;
}

.p-6 {
  padding: 1.5rem;
}

.rounded-lg {
  border-radius: 0.5rem;
}

.shadow-lg {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.mb-4 {
  margin-bottom: 1rem;
}

.text-xl {
  font-size: 1.25rem;
}

.font-bold {
  font-weight: 700;
}

.block {
  display: block;
}

.text-sm {
  font-size: 0.875rem;
}

.font-medium {
  font-weight: 500;
}

.text-gray-700 {
  color: #4a5568;
}

.mt-1 {
  margin-top: 0.25rem;
}

.w-full {
  width: 100%;
}

.border {
  border-width: 1px;
}

.border-gray-300 {
  border-color: #d2d6dc;
}

.rounded-md {
  border-radius: 0.375rem;
}

.shadow-sm {
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.focus\:ring-indigo-500:focus {
  --tw-ring-color: #6cae00;
}

.focus\:border-indigo-500:focus {
  border-color: #6cae00
}

.sm\:text-sm {
  font-size: 0.875rem;
}

.flex {
  display: flex;
}

.justify-end {
  justify-content: flex-end;
}

.mr-2 {
  margin-right: 0.5rem;
}
</style>