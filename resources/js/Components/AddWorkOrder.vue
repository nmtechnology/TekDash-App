<template>
  <div>
    <button @click="openCreateModal" class="btn btn-green-400 outline hover:btn-green-600">Add Workorder</button>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-gray-900 rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
        <div class="bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lime-400 text-2xl leading-6 font-medium text-accent" id="modal-title">
                {{ mode === 'create' ? 'New Work Order Form' : 'Edit Work Order' }}
              </h3>
              <div class="mt-2">
                <form @submit.prevent="submitForm">
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
                    <label for="title" class="block text-sm font-medium text-green-400 bg-slate-900">Title</label>
                    <input placeholder="WorkType / Location / WO######-## " type="text" v-model="form.title" id="title" class="text-lime-400 mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                  </div>
                  <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-green-400">Description</label>
                    <textarea v-model="form.description" id="description" placeholder="What is the Field Technician doing onsite?" class="text-lime-400 mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required></textarea>
                  </div>
                  <div class="mb-4">
                    <label for="date_time" class="block text-sm font-medium text-green-400">Scheduled Date and Time</label>
                    <input type="datetime-local" v-model="form.date_time" id="date_time" class="text-lime-400 mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                  </div>
                  <div class="mb-4">
                    <label for="price" class="block text-sm font-medium text-green-400">Price</label>
                    <input type="number" v-model="form.price" id="price" placeholder="Enter price" class="text-lime-400 mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                  </div>
                  <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-green-400">Status</label>
                    <select v-model="form.status" id="status" class="text-lime-400 mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                      <option value="Scheduled">Scheduled</option>
                      <option value="In Progress">In Progress</option>
                      <option value="Part/Return">Part/Return</option>
                      <option value="Complete">Complete</option>
                      <option value="Cancelled">Cancelled</option>
                    </select>
                  </div>
                  <div class="mb-4">
                    <label for="file_attachments" class="block text-sm font-medium text-green-400">Attachments</label>
                    <input type="file" @change="handleFileUpload" id="file_attachments" class="text-lime-400 mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" multiple>
                  </div>
                  <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-green-400">Notes</label>
                    <textarea v-model="form.notes" id="notes" placeholder="Field Technician Notes Go Here..." class="text-lime-400 mt-1 block w-full rounded-md bg-slate-800 border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm"></textarea>
                  </div>
                  <input type="hidden" v-model="form.user_id" />
                  <progress v-if="form.progress" :value="form.progress.percentage" min="0" max="100">
                    {{ form.progress.percentage }}%
                  </progress>
                  <div class="px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse sm:mt-6 rounded">
                    <button @click="showModal = false" type="button" class="mt-6 w-full inline-flex justify-center rounded border border-gray-300 shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success sm:text-sm">
                      Cancel
                    </button>
                    <button type="submit" class="mt-6 w-full inline-flex justify-center rounded border border-gray-300 shadow-sm px-4 py-2 bg-success text-base font-medium text-white hover:bg-success focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success sm:text-sm">
                      {{ mode === 'create' ? 'Submit' : 'Update' }}
                    </button>
                    <button v-if="mode === 'edit'" @click="deleteWorkOrder" type="button" class="mt-6 w-full inline-flex justify-center rounded border border-gray-300 shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success sm:text-sm">
                      Delete
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';

export default {
  data() {
    return {
      showModal: false,
      mode: 'create',
      form: useForm({
        customer_id: '',
        title: '',
        description: '',
        date_time: '',
        price: '',
        status: 'Scheduled',
        file_attachments: [],
        notes: '',
        progress: null,
        user_id: '',
      }),
    };
  },
  methods: {
    openCreateModal() {
      this.mode = 'create';
      this.form.reset();
      this.form.user_id = this.$page.props.auth.user.id; // Set the user_id field
      this.showModal = true;
    },
    openEditModal(workOrder) {
      this.mode = 'edit';
      this.form = useForm({
        ...workOrder,
        file_attachments: [],
        progress: null,
        user_id: this.$page.props.auth.user.id, // Set the user_id field
      });
      this.showModal = true;
    },
    submitForm() {
      if (this.mode === 'create') {
        this.form.post('/work-orders', {
          onSuccess: () => {
            this.showModal = false;
            this.resetForm();
            this.$emit('formSubmitted', 'Work order created successfully.');
          },
          onError: (errors) => {
            console.error('Error creating work order. Please try again:', errors);
          },
        });
      } else {
        this.form.put(`/work-orders/${this.form.id}`, {
          onSuccess: () => {
            this.showModal = false;
            this.resetForm();
            this.$emit('formSubmitted', 'Work order updated successfully.');
          },
          onError: (errors) => {
            console.error('Error updating work order. Please try again:', errors);
          },
        });
      }
    },
    handleFileUpload(event) {
      this.form.file_attachments = Array.from(event.target.files);
    },
    resetForm() {
      this.form.reset();
    },
    duplicateWorkOrder() {
      Inertia.post(`/work-orders/${this.form.id}/duplicate`, {}, {
        onSuccess: () => {
          this.showModal = false;
          this.$emit('formSubmitted', 'Work order duplicated successfully.');
        },
        onError: (errors) => {
          console.error('Error duplicating work order. Please try again:', errors);
        },
      });
    },
    deleteWorkOrder() {
      if (confirm('Are you sure you want to delete this work order?')) {
        Inertia.delete(`/work-orders/${this.form.id}`, {
          onSuccess: () => {
            this.showModal = false;
            this.$emit('formSubmitted', 'Work order deleted successfully.');
          },
          onError: (errors) => {
            console.error('Error deleting work order. Please try again:', errors);
          },
        });
      }
    },
  }
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

.btn-secondary {
  background-color: #e5e7eb;
  color: #374151;
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
  --tw-ring-color: outline-green-400;
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