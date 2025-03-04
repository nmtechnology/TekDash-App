<template>
  <div>
    <button @click="openCreateModal" class="btn outline text-lime-400 rounded-md hover:bg-lime-400 dark:hover:bg-lime-400 hover:text-gray-900 transition z-30">Add Work Order</button>

    <div v-if="showModal" class="fixed inset-0 top-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="glossy-card rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full mt-16">
        <div class="glossy-header px-4 pt-5 pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <div class="flex justify-between items-center">
                <h3 class="text-lime-400 text-2xl leading-6 font-medium" id="modal-title">
                  {{ mode === 'create' ? 'Add Work Order Form' : 'Edit Work Order' }}
                </h3>
                <NetworkStatusIndicator />
              </div>
            </div>
          </div>
        </div>
        
        <div class="overflow-y-auto px-4 py-5 bg-gray-900 bg-opacity-90">
          <form @submit.prevent="submitForm">
            <div class="glossy-section mb-4">
              <label for="customer_id" class="text-green-400 block text-sm font-medium">Customer</label>
              <select v-model="form.customer_id" id="customer_id" class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                <option value="Advanced Project Solutions">Advanced Project Solutions</option>
                <option value="Barrister Global Service Network">Barrister Global Service Network</option>
                <option value="Bass-Security">Bass-Security</option>
                <option value="DarAlIslam">DarAlIslam</option>
                <option value="Field Nation">Field Nation</option>
                <option value="Navco">Navco</option>
                <option value="NuTech National">NuTech National</option>
                <option value="Telaid">Telaid</option>
              </select>
            </div>
            
            <!-- Work order title fields -->
            <div class="glossy-section mb-4">
              <div class="flex gap-2">
                <div class="mb-4 flex-1">
                  <label for="workType" class="block text-sm font-medium text-green-400">Work Type</label>
                  <input placeholder="Installation" type="text" v-model="workType" id="workType" class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                </div>
                <div class="mb-4 flex-1">
                  <label for="workOrderNumber" class="block text-sm font-medium text-green-400">WO Number</label>
                  <input placeholder="WO123456-01" type="text" v-model="workOrderNumber" id="workOrderNumber" class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                </div>
                <div class="mb-4 flex-1">
                  <label for="location" class="block text-sm font-medium text-green-400">Location</label>
                  <input placeholder="Boston, MA" type="text" v-model="location" id="location" class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                </div>
              </div>
              <!-- Preview of combined title -->
              <div>
                <label class="block text-sm font-medium text-green-400">Generated Title</label>
                <div class="text-lime-400 mt-1 px-3 py-2 rounded-md glossy-content">
                  {{ formattedTitle }}
                </div>
              </div>
            </div>
            
            <div class="glossy-section mb-4">
              <label for="description" class="block text-sm font-medium text-green-400">Description</label>
              <textarea v-model="form.description" id="description" placeholder="What is the Field Technician doing onsite?" class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required></textarea>
            </div>
            
            <div class="glossy-section mb-4">
              <label for="date_time" class="block text-sm font-medium text-green-400">Date Selection</label>
              
              <!-- Date selection type toggle -->
              <div class="flex space-x-4 text-sm mb-2">
                <label class="flex items-center">
                  <input 
                    type="radio" 
                    v-model="dateSelectionType" 
                    value="single" 
                    class="mr-2 focus:ring-lime-400 text-lime-500"
                  >
                  <span class="text-white">Single Date</span>
                </label>
                <label class="flex items-center">
                  <input 
                    type="radio" 
                    v-model="dateSelectionType" 
                    value="range" 
                    class="mr-2 focus:ring-lime-400 text-lime-500"
                  >
                  <span class="text-white">Date Range</span>
                </label>
                <label class="flex items-center">
                  <input 
                    type="radio" 
                    v-model="dateSelectionType" 
                    value="multiple" 
                    class="mr-2 focus:ring-lime-400 text-lime-500"
                  >
                  <span class="text-white">Multiple Dates</span>
                </label>
              </div>

              <!-- Single date selection -->
              <div v-if="dateSelectionType === 'single'" class="mb-2">
                <input 
                  type="datetime-local" 
                  v-model="form.date_time" 
                  id="date_time" 
                  class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" 
                  required
                />
              </div>

              <!-- Date range selection -->
              <div v-if="dateSelectionType === 'range'" class="space-y-2">
                <div class="flex items-center">
                  <span class="text-xs text-gray-400 mr-2 w-14">Start:</span>
                  <input 
                    type="datetime-local" 
                    v-model="form.date_time" 
                    class="glossy-content text-lime-400 mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" 
                    required
                  />
                </div>
                <div class="flex items-center">
                  <span class="text-xs text-gray-400 mr-2 w-14">End:</span>
                  <input 
                    type="datetime-local" 
                    v-model="form.end_date" 
                    class="glossy-content text-lime-400 mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" 
                    required
                  />
                </div>
              </div>

              <!-- Multiple date selection -->
              <div v-if="dateSelectionType === 'multiple'" class="space-y-2">
                <div v-for="(date, index) in selectedDates" :key="index" class="flex items-center">
                  <input 
                    type="datetime-local" 
                    v-model="selectedDates[index]" 
                    class="glossy-content text-lime-400 mt-1 flex-grow rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" 
                    required
                  />
                  <button 
                    @click="removeDate(index)" 
                    type="button"
                    class="ml-2 text-red-400 hover:text-red-300"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                <button 
                  @click="addNewDate" 
                  type="button"
                  class="text-sm text-lime-400 hover:text-lime-300 flex items-center"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Add Another Date
                </button>
              </div>
            </div>
            
            <div class="glossy-section mb-4">
              <label for="price" class="block text-sm font-medium text-green-400">Price</label>
              <input type="number" v-model="form.price" id="price" placeholder="Enter price" class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
            </div>
            
            <div class="glossy-section mb-4">
              <label for="status" class="block text-sm font-medium text-green-400">Status</label>
              <select v-model="form.status" id="status" class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                <option value="Scheduled">Scheduled</option>
                <option value="In Progress">In Progress</option>
                <option value="Part/Return">Part/Return</option>
                <option value="Complete">Complete</option>
                <option value="Cancelled">Cancelled</option>
              </select>
            </div>
            
            <div class="glossy-section mb-4">
              <label for="file_attachments" class="block text-sm font-medium text-green-400">Attachments</label>
              <input type="file" @change="handleFileUpload" id="file_attachments" class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" multiple>
            </div>
            
            <input type="hidden" v-model="form.user_id" />
            <progress v-if="form.progress" :value="form.progress.percentage" min="0" max="100" class="w-full rounded-md">
              {{ form.progress.percentage }}%
            </progress>
          </form>
        </div>
        
        <div class="glossy-footer px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse sm:gap-2">
          <button type="submit" @click="submitForm" class="w-full inline-flex justify-center rounded border shadow-sm px-4 py-2 bg-lime-400 bg-opacity-20 text-base font-medium text-lime-400 hover:bg-lime-400 hover:bg-opacity-30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 sm:text-sm transition-colors duration-200">
            {{ mode === 'create' ? 'Submit' : 'Update' }}
          </button>
          <button @click="showModal = false" type="button" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded border outline shadow-sm px-4 py-2 text-red-500 font-medium hover:bg-red-400 hover:bg-opacity-20 sm:text-sm transition-colors duration-200">
            Cancel
          </button>
          <button v-if="mode === 'edit'" @click="deleteWorkOrder" type="button" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded border shadow-sm px-4 py-2 bg-red-600 bg-opacity-20 text-base font-medium text-red-400 hover:bg-red-600 hover:bg-opacity-30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm transition-colors duration-200">
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, computed } from 'vue';
import NetworkStatusIndicator from '@/Components/NetworkStatusIndicator.vue';

export default {
  components: {
    NetworkStatusIndicator,
  },
  data() {
    return {
      showModal: false,
      mode: 'create',
      workType: '',
      workOrderNumber: '',
      location: '',
      dateSelectionType: 'single',
      selectedDates: [new Date().toISOString().slice(0, 16)],
      form: useForm({
        customer_id: '',
        title: '',
        description: '',
        date_time: '',
        end_date: '',
        visit_dates: [],
        price: '',
        status: 'Scheduled',
        file_attachments: [], // Initialize as an array
        notes: '',
        progress: null,
        user_id: '',
      }),
    };
  },
  computed: {
    formattedTitle() {
      // Combine the three fields into one title string
      if (!this.workType && !this.workOrderNumber && !this.location) return '';
      
      return `${this.workType || 'Unknown'} / ${this.workOrderNumber || 'No WO#'} / ${this.location || 'No Location'}`;
    }
  },
  methods: {
    openCreateModal() {
      this.mode = 'create';
      this.form.reset();
      this.workType = '';
      this.workOrderNumber = '';
      this.location = '';
      this.dateSelectionType = 'single';
      this.selectedDates = [new Date().toISOString().slice(0, 16)];
      this.form.user_id = this.$page.props.auth.user.id;
      this.showModal = true;
    },
    openEditModal(workOrder) {
      this.mode = 'edit';
      this.form = useForm({
        ...workOrder,
        file_attachments: [],
        progress: null,
        user_id: this.$page.props.auth.user.id,
      });
      
      // Initialize date selection type based on work order data
      if (workOrder.visit_dates && workOrder.visit_dates.length > 1) {
        this.dateSelectionType = 'multiple';
        this.selectedDates = [...workOrder.visit_dates];
      } else if (workOrder.end_date) {
        this.dateSelectionType = 'range';
      } else {
        this.dateSelectionType = 'single';
      }
      
      this.showModal = true;
    },
    submitForm() {
      // Set the combined title before submitting
      this.form.title = this.formattedTitle;
      
      // Handle date field based on selection type
      if (this.dateSelectionType === 'single') {
        this.form.end_date = null;
        this.form.visit_dates = [this.form.date_time];
      } 
      else if (this.dateSelectionType === 'range') {
        this.form.visit_dates = this.generateDateRange(this.form.date_time, this.form.end_date);
      } 
      else if (this.dateSelectionType === 'multiple') {
        const sortedDates = [...this.selectedDates].sort();
        this.form.date_time = sortedDates[0] || '';
        this.form.end_date = sortedDates[sortedDates.length - 1] || '';
        this.form.visit_dates = sortedDates;
      }
      
      // Create FormData object
      const formData = new FormData();
      for (const key in this.form) {
        if (key === 'file_attachments' && this.form.file_attachments.length > 0) {
          // Append each file
          for (let i = 0; i < this.form.file_attachments.length; i++) {
            formData.append('file_attachments[]', this.form.file_attachments[i]);
          }
        } else if (key !== 'file_attachments') {
          formData.append(key, this.form[key]);
        }
      }

      // Define onSuccess and onError callbacks
      const onSuccess = () => {
        this.showModal = false;
        this.resetForm();
        this.$emit('formSubmitted', `Work order ${this.mode === 'create' ? 'created' : 'updated'} successfully.`);
      };

      const onError = (errors) => {
        console.error('Validation errors:', errors);
      };

      // Submit with Inertia
      if (this.mode === 'create') {
        Inertia.post('/work-orders', formData, { onSuccess, onError });
      } else {
        Inertia.put(`/work-orders/${this.form.id}`, formData, { onSuccess, onError });
      }
    },
    handleFileUpload(event) {
      this.form.file_attachments = Array.from(event.target.files);
    },
    resetForm() {
      this.form.reset();
      this.form.clearErrors();
      this.workType = '';
      this.workOrderNumber = '';
      this.location = '';
      this.dateSelectionType = 'single';
      this.selectedDates = [new Date().toISOString().slice(0, 16)];
      this.form.file_attachments = null;
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
    // Functions for date selection
    addNewDate() {
      this.selectedDates.push(new Date().toISOString().slice(0, 16));
    },
    removeDate(index) {
      this.selectedDates.splice(index, 1);
      // Always keep at least one date
      if (this.selectedDates.length === 0) {
        this.addNewDate();
      }
    },
    // Generate array of dates between start and end for range selection
    generateDateRange(start, end) {
      if (!start || !end) return [];
      
      try {
        const startDate = new Date(start);
        const endDate = new Date(end);
        
        if (isNaN(startDate) || isNaN(endDate)) return [];
        
        const dateArray = [];
        let currentDate = new Date(startDate);
        
        // Add all dates between start and end
        while (currentDate <= endDate) {
          dateArray.push(new Date(currentDate).toISOString().slice(0, 16));
          currentDate.setDate(currentDate.getDate() + 1);
        }
        
        return dateArray;
      } catch (error) {
        console.error("Error generating date range:", error);
        return [];
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

/* Glass morphism styles */
.glossy-card {
  background: linear-gradient(135deg, rgba(17, 24, 39, 0.95), rgba(31, 41, 55, 0.85));
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  flex-direction: column;
  height: 65vh;
  max-height: 85vh;
  width: 90%;
  max-width: 600px;
}

.glossy-header {
  background: linear-gradient(180deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.85) 100%);
  position: relative;
  overflow: hidden;
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
  flex-shrink: 0;
  padding: 1rem;
}

.glossy-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(163, 230, 53, 0.3), transparent);
}

.glossy-footer {
  background: linear-gradient(0deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.85) 100%);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  position: relative;
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
  flex-shrink: 0;
}

.glossy-section {
  background: linear-gradient(145deg, rgba(17, 24, 39, 0.5), rgba(31, 41, 55, 0.3));
  border-radius: 8px;
  padding: 10px;
  position: relative;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.glossy-content {
  background: linear-gradient(145deg, rgba(31, 41, 55, 0.6), rgba(17, 24, 39, 0.4));
  border: 1px solid rgba(255, 255, 255, 0.05);
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
}

/* Scrollable content area */
.overflow-y-auto {
  flex-grow: 1;
  overflow-y: auto;
  scrollbar-color: rgba(75, 85, 99, 0.5) rgba(17, 24, 39, 0.3);
  scrollbar-width: thin;
  padding: 1rem;
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: rgba(17, 24, 39, 0.3);
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: rgba(75, 85, 99, 0.5);
  border-radius: 3px;
}

/* Progress bar styling */
progress {
  height: 8px;
  border-radius: 4px;
  overflow: hidden;
}

progress::-webkit-progress-bar {
  background-color: rgba(31, 41, 55, 0.6);
  border-radius: 4px;
}

progress::-webkit-progress-value {
  background: linear-gradient(90deg, #84cc16, #65a30d);
  border-radius: 4px;
}

progress::-moz-progress-bar {
  background: linear-gradient(90deg, #84cc16, #65a30d);
  border-radius: 4px;
}
</style>