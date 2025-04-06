<template>
  <div>
    <button @click="openCreateModal" class="btn flex items-center gap-2 px-4 py-2 font-bold text-sm text-blue-400 transition-all duration-300">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Add Work Order
    </button>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70">
      <div class="glossy-card rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full mt-4">
        <div class="glossy-header px-4 pt-5 pb-4">
          
          <div class="flex justify-between items-center">
            <h3 class="text-lime-400 text-2xl leading-6 font-medium" id="modal-title">
              {{ mode === 'create' ? 'Add Work Order Form' : 'Edit Work Order' }}
            </h3>
            <div class="flex items-center">
              <NetworkStatusIndicator />
              <button 
                @click="showModal = false" 
                class="ml-4 text-gray-900 hover:text-lime-400 transition-colors duration-200 focus:outline-none"
                aria-label="Close modal"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>
        
        <div class="overflow-y-auto px-4 py-5 bg-gray-900 bg-opacity-90">
          <form @submit.prevent="submitForm">
            <div class="glossy-section mb-4">
              <label for="customer_id" class="text-green-400 block text-sm font-medium">Customer</label><p class="text-sm text-white">Choose a customer from the dropdown menu below.</p>
              <select v-model="form.customer_id" id="customer_id" class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                <option value="Advanced Project Solutions">Advanced Project Solutions</option>
                <option value="Barrister Global Service Network">Barrister Global Service Network</option>
                <option value="Bass-Security">Bass-Security</option>
                <option value="Actron Security">Actron Security</option>
                <option value="Field Nation">Field Nation</option>
                <option value="Navco">Navco</option>
                <option value="NuTech National">NuTech National</option>
                <option value="Telaid">Telaid</option>
              </select>
            </div>
            
            <h3 class="text-lime-400 text-lg font-medium mb-2">Title</h3>
            <!-- Work order title fields -->
            <div class="glossy-section mb-4"><p class="text-sm text-white">This section will generate a title for TekDash to find it in our system. Work order #'s cannot be used twice and the work order should have been duplicated if this is a return trip instead of creating a new work order. Location name is not an address, it is the name of the business the technician will be at, so for example if it is for Wal-Mart, then the tech knows to look for a WalMart when driving. </p>
              <div class="flex flex-col md:flex-row md:gap-4">
                
                <div class="mb-4 p-2 w-full">
                  <label for="workOrderNumber" class="block text-sm font-medium text-green-400">Work Order Number</label>
                  <div class="relative">
                    <input 
                      placeholder="WO123456-01" 
                      type="text" 
                      v-model="workOrderNumber" 
                      @blur="checkExistingWorkOrder" 
                      id="workOrderNumber" 
                      class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" 
                      :class="{'border-red-500': duplicateWorkOrderFound}"
                      required
                    >
                    <div v-if="checkingWorkOrder" class="absolute right-3 top-2">
                      <span class="text-yellow-400 text-xs">Checking...</span>
                    </div>
                    <div v-if="duplicateWorkOrderFound" class="text-red-500 text-xs mt-1">
                      Work order with this number already exists.
                    </div>
                  </div>
                </div>
                
                <div class="mb-4 p-2 w-full">
                  <label for="workType" class="block text-sm font-medium text-green-400">Work Type</label>
                  <select v-model="workType" id="workType" class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                    <option value="" disabled>Select work type</option>
                    <option value="CCTV">CCTV</option>
                    <option value="ALARM">ALARM</option>
                    <option value="CABLING">CABLING</option>
                    <option value="POS">POS</option>
                    <option value="INTERCOM">INTERCOM</option>
                    <option value="FIRE">FIRE</option>
                    <option value="ACCESS-CONTROL">ACCESS-CONTROL</option>
                    <option value="TS">TS</option>
                    <option value="ESTIMATE">ESTIMATE</option>
                    <option value="WALK-THRU">WALK-THRU</option>
                    <option value="MEETING">MEETING</option>
                    <option value="APPT">APPT</option>
                    <option value="PERSONAL-LEAVE">PERSONAL-LEAVE</option>
                  </select>
                </div>
              </div>
                <div class="mb-4 p-2 w-full mr-1">
                <label for="location" class="block text-sm font-medium text-green-400">Location/Business name</label>
                <select v-model="location" id="location" class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                  <option value="" disabled>Select business name</option>
                  <option value="Chili's">Chili's</option>
                  <option value="Brinks">Brinks</option>
                  <option value="Chase">Chase</option>
                  <option value="Dillard's">Dillard's</option>
                  <option value="DutchBros">DutchBros</option>
                  <option value="RaisingCaines">RaisingCaines</option>
                  <option value="ChaseATM">ChaseATM</option>
                  <option value="WalMart">WalMart</option>
                  <option value="Target">Target</option>
                  <option value="Whataburger">Whataburger</option>
                  <option value="MisterCarwash">MisterCarwash</option>
                  <option value="WellsFargo">WellsFargo</option>
                  <option value="NewSite">NewSite</option>
                </select>
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
              <label for="description" class="block text-sm font-medium text-green-400">Service Description</label><p class="text-sm text-white">Enter the details of what was requested on the work order sent by the customer, here's a hint, you can copy and paste the description from the work orders into here which saves you time.</p>
              <textarea v-model="form.description" id="description" placeholder="What is the Field Technician doing onsite?" class="glossy-content text-lime-400 inline-block mt-1 p-2 mr-3 rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required></textarea>
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
                <div v-for="(dateObj, index) in selectedDates" :key="index" class="flex items-center gap-2">
                  <input 
                    type="datetime-local" 
                    v-model="dateObj.date"
                    class="glossy-content text-lime-400 mt-1 flex-grow rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" 
                    required
                  />
                  <button 
                    @click="removeDate(index)" 
                    type="button"
                    class="text-red-400 hover:text-red-300 p-1"
                    :disabled="selectedDates.length === 1"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                <div class="text-yellow-400 text-xs mt-2 p-2 bg-yellow-900 bg-opacity-30 rounded">
                  <strong>Note:</strong> Selecting multiple dates will create separate work orders for each date.
                </div>
                <button 
                  @click="addNewDate" 
                  type="button"
                  class="text-sm text-lime-400 hover:text-lime-300 flex items-center mt-2"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Add Another Date
                </button>
              </div>
            </div>

            <!-- New address field -->
            <div class="glossy-section mb-4">
              <label for="address" class="block text-sm font-medium text-green-400">Work Site Address</label><p class="text-sm text-white">Enter the address for the site where the technician needs to be ON TIME!</p>
              <input 
                type="text" 
                v-model="form.address" 
                id="address" 
                placeholder="Enter complete work site address" 
                class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" 
                required
              >
            </div>

            <!-- New hours field -->
            <div class="glossy-section mb-4">
              <label for="hours" class="block text-sm font-medium text-green-400">Approved Hours</label><p class="text-sm text-white">Enter the amount of hours approved by the customer, if we need more time onstie then we will call at that time to request the estimated hours to complete the WO. This is usually abour 2-4 hours for our first trip.</p>
              <input 
                type="number" 
                v-model="form.hours" 
                id="hours" 
                step="0.5"
                min="0"
                placeholder="Enter number of hours" 
                class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" 
                required
              >
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
              <div v-if="form.errors.file_attachments" class="text-red-500 text-xs mt-1">
                {{ form.errors.file_attachments }}
              </div>
            </div>

            <!-- Add error message for user_id -->
            <div v-if="form.errors.user_id" class="text-red-500 text-xs mt-1 mb-4">
              {{ form.errors.user_id }}
            </div>

            <input type="hidden" v-model="form.user_id" />
            <progress v-if="form.progress" :value="form.progress.percentage" min="0" max="100" class="w-full rounded-md">
              {{ form.progress.percentage }}%
            </progress>
          </form>
        </div>
        
        <div class="glossy-footer px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse sm:gap-2">
          <button 
            type="submit" 
            @click="submitForm" 
            :disabled="duplicateWorkOrderFound || checkingWorkOrder"
            class="w-full inline-flex justify-center rounded border shadow-sm px-4 py-2 bg-lime-400 bg-opacity-20 text-base font-medium text-lime-400 hover:bg-lime-400 hover:bg-opacity-30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 sm:text-sm transition-colors duration-200"
            :class="{'opacity-50 cursor-not-allowed': duplicateWorkOrderFound || checkingWorkOrder}"
          >
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
import { useForm, router } from '@inertiajs/vue3';
import NetworkStatusIndicator from '@/Components/NetworkStatusIndicator.vue';
import axios from 'axios';

export default {
  components: {
    NetworkStatusIndicator,
  },
  props: {
    auth: Object,
  },
  data() {
    return {
      showModal: false,
      mode: 'create',
      workType: '',
      workOrderNumber: '',
      location: '',
      dateSelectionType: 'single',
      selectedDates: [{ date: new Date().toISOString().slice(0, 16) }],
      isSubmitting: false,
      checkingWorkOrder: false,
      duplicateWorkOrderFound: false,
      debounceTimer: null,
      form: useForm({
        customer_id: '',
        title: '',
        description: '',
        date_time: '',
        end_date: '',
        visit_dates: [],
        price: '120.00',
        status: 'Scheduled',
        file_attachments: [],
        notes: '',
        progress: null,
        user_id: '',
        users_name: '',
        address: '', // Add address field
        hours: '', // Add hours field
      }),
    };
  },
  watch: {
    workOrderNumber(newValue) {
      // Clear previous validation
      this.duplicateWorkOrderFound = false;
      
      // Debounce the check to avoid too many requests
      clearTimeout(this.debounceTimer);
      
      // Only check if there's a value and it's at least 3 characters
      if (newValue && newValue.length >= 3) {
        this.debounceTimer = setTimeout(() => {
          this.checkExistingWorkOrder();
        }, 500);
      }
    }
  },
  computed: {
    formattedTitle() {
      // Combine the three fields into one title string
      if (!this.workType && !this.workOrderNumber && !this.location) return '';
      
      return `${this.workType || 'Unknown'} / ${this.workOrderNumber || 'No WO#'} / ${this.location || 'No Location'}`;
    }
  },
  methods: {
    async checkExistingWorkOrder() {
      // Don't check if work order number is empty or too short
      if (!this.workOrderNumber || this.workOrderNumber.length < 3) return;
      
      // Skip this check in edit mode for the current work order
      if (this.mode === 'edit' && this.form.id) {
        const currentWorkOrderNumber = this.extractWorkOrderNumber(this.form.title);
        if (currentWorkOrderNumber === this.workOrderNumber) {
          this.duplicateWorkOrderFound = false;
          return;
        }
      }
      
      this.checkingWorkOrder = true;
      
      try {
        const response = await axios.get(`/api/check-work-order-exists`, {
          params: { workOrderNumber: this.workOrderNumber }
        });
        
        this.duplicateWorkOrderFound = response.data.exists;
        
      } catch (error) {
        console.error('Error checking work order:', error);
        // Don't block submission on API error
        this.duplicateWorkOrderFound = false;
      } finally {
        this.checkingWorkOrder = false;
      }
    },
    
    extractWorkOrderNumber(title) {
      // Extract work order number from title (format: "Type / WO# / Location")
      if (!title) return '';
      
      const parts = title.split('/');
      return parts.length > 1 ? parts[1].trim() : '';
    },
    
    openCreateModal() {
      this.mode = 'create';
      this.form.reset();
      this.workType = '';
      this.workOrderNumber = '';
      this.location = '';
      this.dateSelectionType = 'single';
      this.selectedDates = [{ date: new Date().toISOString().slice(0, 16) }];
      // Set user ID from auth immediately when opening modal
      this.form.user_id = this.$page.props.auth.user.id;
      this.form.file_attachments = []; // Reset to empty array
      this.showModal = true;
      
      // Clear the file input
      setTimeout(() => {
        const fileInput = document.getElementById('file_attachments');
        if (fileInput) fileInput.value = '';
      }, 50);
      this.duplicateWorkOrderFound = false;
      this.checkingWorkOrder = false;
    },
    openEditModal(workOrder) {
      this.mode = 'edit';
      this.form = useForm({
        ...workOrder,
        file_attachments: [],
        progress: null,
        user_id: this.$page.props.auth.user.id,
      });
      
      // Initialize date selection type and dates
      if (workOrder.visit_dates && workOrder.visit_dates.length > 0) {
        this.dateSelectionType = 'multiple';
        this.selectedDates = workOrder.visit_dates.map(date => ({ date }));
      } else if (workOrder.end_date) {
        this.dateSelectionType = 'range';
      } else {
        this.dateSelectionType = 'single';
        this.selectedDates = [{ date: workOrder.date_time || new Date().toISOString().slice(0, 16) }];
      }
      
      this.showModal = true;
      this.duplicateWorkOrderFound = false;
      this.checkingWorkOrder = false;
      
      // Extract work order number, type and location from title
      if (workOrder.title) {
        const parts = workOrder.title.split('/');
        if (parts.length >= 3) {
          this.workType = parts[0].trim();
          this.workOrderNumber = parts[1].trim();
          this.location = parts[2].trim();
        }
      }
    },
    submitForm() {
      // Prevent submission if duplicate work order is found
      if (this.duplicateWorkOrderFound || this.checkingWorkOrder) {
        return;
      }
      
      // Check for duplicates one more time before submission
      this.checkExistingWorkOrder().then(() => {
        if (this.duplicateWorkOrderFound) return;
        
        // Set the combined title before submitting
        this.form.title = this.formattedTitle;
        
        // Explicitly set the user ID again before submission to ensure it's present
        this.form.user_id = this.$page.props.auth.user.id;
        
        // Handle date field based on selection type
        if (this.dateSelectionType === 'single') {
          this.form.end_date = null;
          this.form.visit_dates = [this.form.date_time];
          this.submitSingleWorkOrder();
        } 
        else if (this.dateSelectionType === 'range') {
          this.form.visit_dates = this.generateDateRange(this.form.date_time, this.form.end_date);
          this.submitSingleWorkOrder();
        } 
        else if (this.dateSelectionType === 'multiple') {
          // Filter out any empty dates
          const validDates = this.selectedDates
            .map(d => d.date)
            .filter(date => date && date.trim() !== '')
            .sort();

          if (validDates.length === 0) {
            alert('Please select at least one valid date');
            return;
          }

          // Handle multiple dates as separate work orders
          this.submitMultipleWorkOrders(validDates);
        }
      });
    },
    
    // New method to submit a single work order
    submitSingleWorkOrder() {
      if (this.isSubmitting) return; // Prevent multiple submissions
      this.isSubmitting = true;
      
      // Always use post() directly with the form instance for proper file handling
      if (this.mode === 'create') {
        this.form.post('/work-orders', {
          onSuccess: () => {
            this.showModal = false;
            this.resetForm();
            const userName = this.$page.props.auth.user.name;
            const timestamp = new Date().toLocaleString();
            this.$emit('formSubmitted', `${userName} successfully created work order '${this.formattedTitle}' at ${timestamp}`);
            this.isSubmitting = false;
          },
          onError: (errors) => {
            console.error('Validation errors:', errors);
            this.isSubmitting = false;
            
            // Check if we have a CSRF token error (419)
            if (errors.response && errors.response.status === 419) {
              this.refreshCsrfTokenAndRetry();
            }
          },
          forceFormData: true
        });
      } else {
        this.form.post(`/work-orders/${this.form.id}?_method=PUT`, {
          onSuccess: () => {
            this.showModal = false;
            const userName = this.$page.props.auth.user.name;
            const timestamp = new Date().toLocaleString();
            this.$emit('formSubmitted', `${userName} successfully updated work order '${this.formattedTitle}' at ${timestamp}`);
            this.isSubmitting = false;
          },
          onError: (errors) => {
            console.error('Validation errors:', errors);
            this.isSubmitting = false;
            
            // Check if we have a CSRF token error (419)
            if (errors.response && errors.response.status === 419) {
              this.refreshCsrfTokenAndRetry();
            }
          },
          forceFormData: true
        });
      }
    },
    
    // New method to submit multiple work orders
    async submitMultipleWorkOrders(dates) {
      if (this.isSubmitting) return;
      this.isSubmitting = true;

      try {
        const totalDates = dates.length;
        let successCount = 0;

        for (let i = 0; i < dates.length; i++) {
          const date = dates[i];

          // Prepare the form data for this work order
          const formData = {
            customer_id: this.form.customer_id,
            title: `${this.formattedTitle} (${new Date(date).toLocaleDateString()})`,
            description: this.form.description,
            date_time: date,
            end_date: null, // Ensure end_date is null for single-date work orders
            address: this.form.address,
            hours: this.form.hours,
            price: this.form.price,
            status: this.form.status,
            user_id: this.form.user_id,
          };

          // Send the request to create the work order
          await axios.post('/work-orders', formData);

          successCount++;
        }

        // Close the modal and reset the form
        this.showModal = false;
        this.resetForm();
        const userName = this.$page.props.auth.user.name;
        const timestamp = new Date().toLocaleString();
        this.$emit('formSubmitted', `${userName} successfully created ${successCount} work orders for '${this.formattedTitle}' at ${timestamp}`);
      } catch (error) {
        console.error('Error creating multiple work orders:', error);
        alert('There was an error creating some work orders. Please check the input data and try again.');
      } finally {
        this.isSubmitting = false;
      }
    },
    
    // Add a method to refresh CSRF token
    async refreshCsrfTokenAndRetry() {
      try {
        // Get a fresh CSRF token
        const response = await axios.get('/csrf-token');
        if (response.data && response.data.csrfToken) {
          // Update the token in meta tag
          const tokenElement = document.querySelector('meta[name="csrf-token"]');
          if (tokenElement) {
            tokenElement.setAttribute('content', response.data.csrfToken);
          }
          
          // Retry submission after a short delay
          setTimeout(() => {
            this.isSubmitting = false;
            this.submitForm();
          }, 500);
        }
      } catch (error) {
        console.error('Failed to refresh CSRF token:', error);
        this.isSubmitting = false;
      }
    },
    handleFileUpload(event) {
      // Get file objects directly from the event
      const files = event.target.files;
      
      if (files && files.length > 0) {
        // Convert FileList to array and set to form.file_attachments
        this.form.file_attachments = Array.from(files);
      } else {
        // Reset to empty array if no files
        this.form.file_attachments = [];
      }
    },
    resetForm() {
      // Clear the file input separately since form.reset() doesn't clear it
      const fileInput = document.getElementById('file_attachments');
      if (fileInput) fileInput.value = '';
      
      this.form.reset();
      this.form.clearErrors();
      this.workType = '';
      this.workOrderNumber = '';
      this.location = '';
      this.dateSelectionType = 'single';
      this.selectedDates = [{ date: new Date().toISOString().slice(0, 16) }];
      this.form.file_attachments = []; // Reset to empty array
    },
    duplicateWorkOrder() {
      router.post(`/work-orders/${this.form.id}/duplicate`, {}, {
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
        router.delete(`/work-orders/${this.form.id}`, {
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
      this.selectedDates.push({ date: new Date().toISOString().slice(0, 16) });
    },
    removeDate(index) {
      if (this.selectedDates.length > 1) {
        this.selectedDates.splice(index, 1);
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
  background-color: hsl(262, 69%, 44%);
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
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.7);
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
  width: 90%;
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
  height: auto; 
  max-height: 60vh;
  width: 95%;
  max-width: 650px;
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
  padding: 0.75rem;
  max-height: calc(75vh - 120px); /* Adjust space for header and footer */
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

/* Responsive form fields */
@media (max-width: 768px) {
  .glossy-section .flex {
    flex-direction: column;
  }
  
  .glossy-card {
    height: auto;
    max-height: 70vh;
    width: 95%;
  }
}

input, select, textarea {
  padding: 0.5rem;
  margin: 0.25rem 0;
}

/* Additional styles for validation */
.border-red-500 {
  border-color: #ef4444 !important;
  border-width: 2px !important;
}

.opacity-50 {
  opacity: 0.5;
}

.cursor-not-allowed {
  cursor: not-allowed;
}

/* Update existing styles or add at the end */

.glass-button {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.8);
  font-weight: 500;
}

.glass-button:hover {
  background: rgba(139, 92, 246, 0.3); /* Purple color with transparency */
  border-color: rgba(139, 92, 246, 0.5);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
  color: rgb(255, 255, 255); /* Matching purple text color */
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  .glass-button {
    background: rgba(30, 30, 30, 0.4);
    border-color: rgba(255, 255, 255, 0.1);
  }
  
  .glass-button:hover {
    background: rgba(139, 92, 246, 0.25);
    border-color: rgba(139, 92, 246, 0.4);
  }
}
</style>