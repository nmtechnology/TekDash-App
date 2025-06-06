<template>
  <div class="bg-gray-900 text-white min-h-screen">
    <!-- Error Alert -->
    <div v-if="uploadError" class="p-4 mb-4 bg-red-800 text-red-100 rounded-lg">
      <p class="font-medium">Error:</p>
      <p class="whitespace-pre-line">{{ uploadError }}</p>
    </div>

    <!-- Header Section -->
    <header class="p-6 border-b border-gray-700">
      <div class="flex items-center justify-between">
        <div class="flex-1">
          <div v-if="editingField.title" class="flex items-center space-x-2">
            <input 
              v-model="form.title" 
              class="bg-gray-800 text-white px-3 py-2 rounded-lg border border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 w-full"
              @keyup.enter="saveField('title')"
              placeholder="Enter work order title"
            />
            <button 
              @click="saveField('title')"
              class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              Save
            </button>
          </div>
          <h1 
            v-else 
            @click="editingField.title = true"
            class="text-2xl font-bold cursor-pointer hover:text-blue-400"
          >
            {{ workOrderData.title }}
          </h1>

          <div class="mt-2 flex items-center space-x-4">
            <span class="text-gray-400">Created: {{ formatDate(workOrderData.created_at) }}</span>
            <span class="text-gray-400">Updated: {{ formatDate(workOrderData.updated_at) }}</span>
          </div>
        </div>

        <div class="flex items-center space-x-4">
          <div v-if="editingField.status" class="flex items-center space-x-2">
            <select 
              v-model="form.status"
              class="bg-gray-800 text-white px-3 py-2 rounded-lg border border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              @change="saveField('status')"
            >
              <option v-for="status in VALID_STATUSES" :key="status" :value="status">
                {{ status }}
              </option>
            </select>
          </div>
          <div 
            v-else 
            @click="editingField.status = true"
            :class="getStatusClasses(workOrderData.status)"
            class="px-3 py-1 rounded-full text-sm font-medium cursor-pointer ring-1 hover:ring-2"
          >
            {{ workOrderData.status }}
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center space-x-2">
            <button 
              v-if="workOrderData.status === 'Part Needed'"
              @click="duplicateWorkOrder"
              class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
            >
              Duplicate
            </button>
            <button
              @click="archiveWorkOrder"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
            >
              Archive
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Loading State -->
    <div v-if="isUploading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-gray-800 p-6 rounded-lg shadow-xl">
        <div class="flex items-center space-x-4">
          <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <div>
            <p class="text-lg font-medium">Uploading...</p>
            <p class="text-gray-400">{{ uploadProgress }}%</p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Duplicate Modal -->
    <div v-if="showDuplicateDateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-gray-800 p-6 rounded-lg shadow-xl max-w-md w-full">
        <h3 class="text-xl font-bold mb-4">Select Date for Duplicate Work Order</h3>
        <input
          type="datetime-local"
          v-model="duplicateDate"
          class="w-full bg-gray-700 text-white px-3 py-2 rounded-lg border border-gray-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 mb-4"
        >
        <div class="flex justify-end space-x-3">
          <button
            @click="cancelDuplicate"
            class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600"
          >
            Cancel
          </button>
          <button
            @click="confirmDuplicate"
            :disabled="isDuplicating"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ isDuplicating ? 'Creating...' : 'Create Duplicate' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 space-y-6">
      <NetworkStatusIndicator />
      
      <!-- Attachments Section -->
      <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">Attachments</h2>
        <div class="space-y-4">
          <input
            type="file"
            @change="handleImageUpload"
            multiple
            accept="image/*,application/pdf,.doc,.docx"
            class="hidden"
            ref="fileInput"
          >
          <button
            @click="$refs.fileInput.click()"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Upload Files
          </button>

          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
            <div
              v-for="attachment in getAllAttachments()"
              :key="attachment"
              class="relative group"
            >
              <div 
                @click="handlePreviewAttachment(attachment)"
                class="cursor-pointer bg-gray-700 rounded-lg p-2 hover:bg-gray-600"
              >
                <div v-if="isPdfFile(attachment)" class="flex items-center space-x-2">
                  <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 18h12a2 2 0 002-2V6a2 2 0 00-2-2h-3.93a2 2 0 01-1.66-.89l-.812-1.22A2 2 0 008.93 1H4a2 2 0 00-2 2v13a2 2 0 002 2z"></path>
                  </svg>
                  <span class="text-sm truncate">{{ getFileName(attachment) }}</span>
                </div>
                <img
                  v-else-if="isImageFile(attachment)"
                  :src="`/storage/${attachment}`"
                  :alt="getFileName(attachment)"
                  class="w-full h-32 object-cover rounded"
                >
                <div v-else class="flex items-center space-x-2">
                  <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 18h12a2 2 0 002-2V6a2 2 0 00-2-2h-3.93a2 2 0 01-1.66-.89l-.812-1.22A2 2 0 008.93 1H4a2 2 0 00-2 2v13a2 2 0 002 2z"></path>
                  </svg>
                  <span class="text-sm truncate">{{ getFileName(attachment) }}</span>
                </div>
              </div>
              <button
                @click="deleteAttachment(attachment)"
                class="absolute top-2 right-2 bg-red-600 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Preview Modal -->
      <div v-if="previewAttachment" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
        <div class="max-w-4xl w-full bg-gray-800 rounded-lg overflow-hidden">
          <div class="flex justify-between items-center p-4 border-b border-gray-700">
            <h3 class="text-lg font-medium">{{ getFileName(previewAttachment) }}</h3>
            <button @click="closePreview" class="text-gray-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <div class="p-4">
            <img
              v-if="isImageFile(previewAttachment)"
              :src="`/storage/${previewAttachment}`"
              :alt="getFileName(previewAttachment)"
              class="max-w-full max-h-[80vh] mx-auto"
            >
            <PdfViewer
              v-else-if="isPdfFile(previewAttachment)"
              :url="`/storage/${previewAttachment}`"
              @close="handlePdfClose"
              @upload="handleDocumentUpload"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import { ref, computed, nextTick, watch } from 'vue';
  import { useForm, router } from '@inertiajs/vue3';
  import axios from 'axios';
  import { format, parseISO } from 'date-fns';

  import Messenger from '@/Components/Messenger.vue';
  import PdfViewer from '@/Components/PdfViewer.vue';
  import PdfThumbnail from '@/Components/PdfThumbnail.vue';
  import NetworkStatusIndicator from '@/Components/NetworkStatusIndicator.vue';
  import Timeline from '@/Components/Timeline.vue'; // Import the Timeline component

  export default {
    name: 'WorkOrder',
    components: {
      Messenger,
      PdfViewer, 
      PdfThumbnail,
      NetworkStatusIndicator,
      Timeline,
    },
    emits: ['close', 'work-order-archived'],  // Add this line to declare emits
    props: {
      workOrder: {
        type: Object,
        default: null,
      },
      showModal: {
        type: Boolean,
        required: true,
      },
      users: {
        type: Array,
        default: () => [], // Make users prop optional with empty array default
      },
    },
    setup(props, { emit }) {
      // Early return with empty state if workOrder prop is missing
      if (!props.workOrder) {
        console.warn('WorkOrder prop is missing or invalid');
        return {
          form: useForm({}),
          isEditing: ref(false),
          editingField: ref({}),
          showPdfViewer: ref(false),
          previewAttachment: ref(null),
          closeModal: () => emit('close'),
          // Add minimal required properties to prevent undefined errors
          formatDate: () => 'No date available',
          getStatusClasses: () => '',
          getAllAttachments: () => [],
          formatMultipleDates: () => 'No dates available',
          hasPdfAttachment: computed(() => false),
          mostRecentPdfAttachment: computed(() => null),
        };
      }

      // Add this near the top of setup(), with other refs
      const showPdfViewer = ref(false);
      const previewAttachment = ref(null);
      const isUploading = ref(false);
      const uploadProgress = ref(0);
      const uploadError = ref('');
      const showDuplicateDateModal = ref(false);
      const duplicateDate = ref('');
      const isDuplicating = ref(false);
      
      // Configure Axios - Add this at the top of setup() with better error handling
      let csrf;
      try {
        csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrf) {
          console.warn('CSRF token not found in the document. Some features might not work correctly.');
        }
        
        // Don't override all default headers, just set the ones we need
        axios.defaults.withCredentials = true;
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf || '';
        axios.defaults.headers.common['Accept'] = 'application/json';
      } catch (error) {
        console.error('Error setting up Axios defaults:', error);
      }

      // Define closePreview function here to avoid reference errors
      const closePreview = () => {
        previewAttachment.value = null;
      };
      
      // Helper functions for file types - defined early to avoid reference errors
      const isPdfFile = (filename) => {
        if (!filename) return false;
        return filename.toLowerCase().endsWith('.pdf');
      };

      const isImageFile = (filename) => {
        if (!filename) return false;
        const lowerFilename = filename.toLowerCase();
        return lowerFilename.endsWith('.jpg') || 
               lowerFilename.endsWith('.jpeg') || 
               lowerFilename.endsWith('.png') || 
               lowerFilename.endsWith('.gif') || 
               lowerFilename.endsWith('.webp') || 
               lowerFilename.endsWith('.heic');
      };

      const isDocumentFile = (filename) => {
        if (!filename) return false;
        const lowerFilename = filename.toLowerCase();
        return lowerFilename.endsWith('.docx') ||
               lowerFilename.endsWith('.doc');
      };
      
      const getFileName = (path) => {
        if (!path) return '';
        return path.split('/').pop();
      };
      
      const handlePreviewAttachment = (attachment) => {
        if (isImageFile(attachment) || isPdfFile(attachment) || isDocumentFile(attachment)) {
          previewAttachment.value = attachment;
        }
      };
      
      // ADD THE MISSING handleImageUpload FUNCTION
      const handleImageUpload = async (event) => {
        try {
          if (!event.target.files || event.target.files.length === 0) {
            console.warn('No files selected in handleImageUpload');
            return;
          }
          
          form.images = Array.from(event.target.files);
          console.log(`Selected ${form.images.length} files for upload`);
          
          // Automatically start the upload
          await saveField('images');
          
          // Clear the input value to allow selecting the same file again
          event.target.value = '';
        } catch (error) {
          console.error('Error in handleImageUpload:', error);
          alert('Failed to process selected files. Please try again.');
        }
      };
      
      // Enhanced getStatusClasses function to match the color scheme in the example
      const getStatusClasses = (status) => {
        if (!status) return 'bg-gray-800 text-gray-300 ring-gray-700';
        
        const statusLower = status.toLowerCase();
        
        if (statusLower.includes('complete')) {
          return 'bg-green-800 text-green-100 ring-green-700';
        } else if (statusLower.includes('scheduled')) {
          return 'bg-blue-800 text-blue-100 ring-blue-700';
        } else if (statusLower.includes('progress')) {
          return 'bg-yellow-800 text-yellow-100 ring-yellow-700';
        } else if (statusLower.includes('cancel')) {
          return 'bg-red-800 text-red-100 ring-red-700';
        } else if (statusLower.includes('part') || statusLower.includes('return')) {
          return 'bg-purple-800 text-purple-100 ring-purple-700';
        }
        
        return 'bg-gray-800 text-gray-300 ring-gray-700';
      };

      const isEditing = ref(false);
      const editingField = ref({
        title: false,
        description: false,
        date_time: false,
        status: false,
        price: false,
        customer_id: false,
        images: false,
        address: false,
        hours: false,
      });

      const form = useForm({
        customer_id: props.workOrder?.customer_id || '',
        title: props.workOrder?.title || '',
        description: props.workOrder?.description || '',
        date_time: props.workOrder?.date_time || '',
        end_date: props.workOrder?.end_date || '',
        visit_dates: props.workOrder?.visit_dates || [],
        status: props.workOrder?.status || 'Scheduled',
        price: props.workOrder?.price || 0,
        notes: props.workOrder?.notes || [],
        images: [],
        user_id: props.workOrder?.user_id || '',
        address: props.workOrder?.address || '',
        hours: props.workOrder?.hours || '',
      });

      // New state for advanced date selection
      const dateSelectionType = ref('single');
      const selectedDates = ref([new Date().toISOString().slice(0, 16)]);
      // Initialize date selection type based on work order data
      const initializeDateSelection = () => {
        if (props.workOrder?.visit_dates && props.workOrder.visit_dates.length > 1) {
          dateSelectionType.value = 'multiple';
          selectedDates.value = [...props.workOrder.visit_dates];
        } else if (props.workOrder?.end_date) {
          dateSelectionType.value = 'range';
        } else {
          dateSelectionType.value = 'single';
        }
      };

      // Call initialization when workOrder changes
      watch(() => props.workOrder, (newVal) => {
        if (newVal) {
          form.customer_id = newVal.customer_id || '';
          form.title = newVal.title || '';
          form.description = newVal.description || '';
          form.date_time = newVal.date_time || '';
          form.end_date = newVal.end_date || '';
          form.visit_dates = newVal.visit_dates || [];
          form.status = newVal.status || 'Scheduled';
          form.price = newVal.price || 0;
          form.notes = newVal.notes || [];
          form.user_id = newVal.user_id || '';
          form.address = newVal.address || '';
          form.hours = newVal.hours || '';
          // Initialize date selection after form is updated
          initializeDateSelection();
        }
      }, { immediate: true });

      // Functions for multiple date selection
      const addNewDate = () => {
        selectedDates.value.push(new Date().toISOString().slice(0, 16));
      };

      const removeDate = (index) => {
        selectedDates.value.splice(index, 1);
        // Always keep at least one date
        if (selectedDates.value.length === 0) {
          addNewDate();
        }
      };
      // Add this near the top of the setup function, after the refs
      const VALID_STATUSES = [
        'Scheduled',
        'In Progress',
        'Part Needed',
        'Complete',
        'Cancelled'
      ];

      // Save field with appropriate date structure based on selection type
      const saveField = async (field) => {
        if (field === 'images') {
          return saveImages();
        }

        let data = {};
        // Special handling for status field
        if (field === 'status') {
          if (!VALID_STATUSES.includes(form[field])) {
            console.error('Invalid status:', form[field]);
            alert(`Invalid status value. Must be one of: ${VALID_STATUSES.join(', ')}`);
            return;
          }
          data = { status: form[field].trim() };
        }
        // Special handling for hours field - send only the value directly
        else if (field === 'hours') {
          const hoursValue = parseFloat(form.hours) || 0;
          try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await axios.post(
              route('work-orders.update-hours', props.workOrder.id), 
              { hours: hoursValue },
              {
                headers: {
                  'X-CSRF-TOKEN': csrf,
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
                }
              }
            );
            
            if (response.data.success) {
              props.workOrder.hours = hoursValue;
              editingField.value[field] = false;
            } else {
              throw new Error(response.data.message || 'Failed to update hours');
            }
          } catch (error) {
            console.error('Error updating hours:', error);
            alert(error.response?.data?.message || 'Failed to update hours');
          }
          return;
        }
        // Special handling for address field
        else if (field === 'address') {
          // Send the data in the format the backend expects with a dedicated endpoint
          try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            // Make a separate request specifically for address
            const response = await axios.post(`/work-orders/${props.workOrder.id}/update-address`, {
              address: form.address
            }, {
              headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              }
            });
            
            if (response.data.success) {
              // Update local state with the new address value
              props.workOrder.address = form.address;
              editingField.value[field] = false;
            } else {
              console.error('Failed to update address:', response.data.message);
              alert(response.data.message || 'Failed to update address');
            }
            return;
          } catch (error) {
            console.error('Error updating address:', error);
            if (error.response) {
              console.error('Response data:', error.response.data);
              console.error('Response status:', error.response.status);
              alert(error.response.data?.message || 'Failed to update address');
            } else {
              alert(`Error: ${error.message}`);
            }
            return;
          }
        }
        // Special handling for date fields
        else if (field === 'date_time') {
          if (dateSelectionType.value === 'single') {
            data.date_time = form.date_time;
            data.end_date = null;
            data.visit_dates = [form.date_time];
          } 
          else if (dateSelectionType.value === 'range') {
            data.date_time = form.date_time;
            data.end_date = form.end_date;
            data.visit_dates = generateDateRange(form.date_time, form.end_date);
          } 
          else if (dateSelectionType.value === 'multiple') {
            // Sort dates chronologically
            const sortedDates = [...selectedDates.value].sort();
            data.date_time = sortedDates[0] || '';
            data.end_date = sortedDates[sortedDates.length - 1] || '';
            data.visit_dates = sortedDates;
          }
        } else if (field === 'price') {
          // Ensure price is a number
          data[field] = Number(form[field]);
        } else {
          data[field] = form[field];
        }

        // Add field_name to all requests for better backend validation
        data.field_name = field;

        // Log what we're sending for debugging
        console.log(`Updating ${field} with:`, data);

        try {
          // Get a fresh CSRF token before each request
          const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          
          // Create request config with the latest CSRF token
          const config = {
            withCredentials: true,
            headers: {
              'X-CSRF-TOKEN': csrf,
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          };
          
          // Use web route instead of API route with proper headers
          const response = await axios.post(`/work-orders/${props.workOrder.id}/update-field`, {
            ...data,
            field: field // Send field name separately
          }, config);
          
          if (response.data.success) {
            // Update local state
            if (field === 'hours') {
              props.workOrder.hours = data.hours;
            } else {
              Object.keys(data).forEach(key => {
                props.workOrder[key] = data[key];
              });
            }
            editingField.value[field] = false;
          } else {
            console.error(`Failed to update ${field}:`, response.data.message);
            alert(response.data.message || `Failed to update ${field}`);
          }
        } catch (error) {
          console.error(`Error updating ${field}:`, error);
          if (error.response) {
            console.error('Response data:', error.response.data);
            console.error('Response status:', error.response.status);
            console.error('Response headers:', error.response.headers);
            alert(error.response.data?.message || `Failed to update ${field}`);
          } else if (error.request) {
            console.error('No response received:', error.request);
            alert('No response from server. Please try again later.');
          } else {
            console.error('Error setting up request:', error.message);
            alert(`Error: ${error.message}`);
          }
        }
      };

      // Generate a range of dates between start and end
      const generateDateRange = (start, end) => {
        try {
          if (!start || !end) return [start].filter(Boolean);
          
          const startDate = new Date(start);
          const endDate = new Date(end);
          
          if (isNaN(startDate) || isNaN(endDate)) {
            return [start].filter(Boolean);
          }

          // If they're on the same day, just return both times
          const isSameDay = startDate.toDateString() === endDate.toDateString();
          if (isSameDay) return [start, end];

          // Generate array of dates between start and end
          const dates = [];
          let currentDate = new Date(startDate);
          
          while (currentDate <= endDate) {
            dates.push(currentDate.toISOString().split('T')[0] + 'T00:00:00');
            currentDate.setDate(currentDate.getDate() + 1);
          }
          
          return dates;
        } catch (error) {
          console.error("Error generating date range:", error);
          return [];
        }
      };

      // Define formatDate before it's used in formatMultipleDates
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

      // Format multiple dates for display
      const formatMultipleDates = (dates) => {
        if (!dates || !dates.length) return 'No dates set';
        try {
          // Sort dates chronologically
          const sortedDates = [...dates].sort();
          
          if (sortedDates.length === 1) {
            return formatDate(sortedDates[0]);
          }

          // For many dates, show first and last with count
          const firstDate = formatDateShort(sortedDates[0]);
          const lastDate = formatDateShort(sortedDates[sortedDates.length - 1]);
          
          return `${firstDate} to ${lastDate} (${sortedDates.length} visits)`;
        } catch (error) {
          console.error('Date formatting error:', error);
          return 'Invalid dates';
        }
      };

      // Short date format for multiple dates display
      const formatDateShort = (dateString) => {
        if (!dateString) return '';
        try {
          const date = parseISO(dateString);
          return format(date, 'MMM d, yyyy');
        } catch (error) {
          return '';
        }
      };
      
      // Replace the existing duplicateWorkOrder function with this version    
      const duplicateWorkOrder = (event) => {
        console.log('Duplicate button clicked');
        console.log('Current status:', props.workOrder.status);
        // Prevent default if it's a button click
        if (event) {
          event.preventDefault();
        }

        // Check if status is Part Needed
        if (props.workOrder.status !== 'Part Needed') {
          console.log('Wrong status - cannot duplicate');
          return;
        }

        // Set initial date value to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        duplicateDate.value = tomorrow.toISOString().slice(0, 16);
        console.log('Opening duplicate modal with date:', duplicateDate.value);      
        showDuplicateDateModal.value = true;
      };

      // Add these new functions
      const cancelDuplicate = () => {
        showDuplicateDateModal.value = false;
        duplicateDate.value = '';
      };

      const confirmDuplicate = async () => {
        if (isDuplicating.value) return;
        
        isDuplicating.value = true;
        
        try {
          const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          if (!csrf) {
            throw new Error('CSRF token not found');
          }

          // Ensure we have a valid date
          if (!duplicateDate.value) {
            throw new Error('Please select a valid date');
          }

          // Format the date properly
          const formattedDate = new Date(duplicateDate.value).toISOString();

          // Create focused payload with only necessary data and explicit date handling
          const newWorkOrderData = {
            customer_id: props.workOrder.customer_id,
            title: props.workOrder.title,
            description: props.workOrder.description,
            price: props.workOrder.price,
            user_id: props.workOrder.user_id,
            user_name: props.workOrder.user_name,
            address: props.workOrder.address,
            hours: props.workOrder.hours,
            original_id: props.workOrder.id,
            // Date related fields - make sure these are explicitly set
            date_time: formattedDate,
            visit_dates: [formattedDate],
            end_date: null,
            status: 'Scheduled',
          };

          console.log('Sending duplicate request with date:', formattedDate);
          const response = await axios.post(
            `/work-orders/${props.workOrder.id}/duplicate`,
            newWorkOrderData,
            {
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
              }
            }
          );

          if (response.data.success) {
            console.log('Duplicate response:', response.data);
            showDuplicateDateModal.value = false;
            alert('Work order duplicated successfully with new date!');
            window.location.reload();
          } else {
            throw new Error(response.data.message || 'Failed to duplicate work order');
          }
        } catch (error) {
          console.error('Error duplicating work order:', error);
          console.log('Error details:', error.response?.data);
          alert(error.response?.data?.message || error.message || 'Failed to duplicate work order');
        } finally {
          isDuplicating.value = false;
        }
      };

      const handlePdfClose = () => {
        showPdfViewer.value = false;
        // Any other cleanup needed
      };

      // For image/file uploads with special handling
      const saveImages = async () => {
        if (!form.images || form.images.length === 0) {
          editingField.value.images = false;
          return;
        }

        // Clear previous errors
        uploadError.value = '';
        isUploading.value = true;
        uploadProgress.value = 0;

        try {
          const formData = new FormData();
          // Log the files being uploaded
          console.log('Files to upload:', form.images);
          
          // Append each file to FormData with the correct field name
          form.images.forEach(file => {
            formData.append('attachments[]', file);
            console.log(`Appending file: ${file.name}, size: ${file.size}, type: ${file.type}`);
          });

          // For debugging - log all entries in FormData
          for (let pair of formData.entries()) {
            console.log('FormData entry:', pair[0], pair[1]);
          }

          // Get CSRF token from meta tag
          let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          if (!csrfToken) {
            throw new Error('CSRF token not found. Please refresh the page and try again.');
          }
          
          // Also add the X-XSRF-TOKEN cookie value as a header for additional security
          // Get both types of CSRF tokens - Laravel uses both mechanisms
          const xsrfToken = getCookie('XSRF-TOKEN');
          
          // Refresh the CSRF token first
          try {
            // Get a fresh token from the server
            const refreshResponse = await axios.post('/csrf/refresh', {}, {
              headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
              },
              withCredentials: true
            });
            
            // Use the fresh token if available
            if (refreshResponse.data && refreshResponse.data.csrfToken) {
              csrfToken = refreshResponse.data.csrfToken;
              console.log('Using fresh CSRF token');
            }
          } catch (refreshError) {
            console.warn('Failed to refresh CSRF token, using existing token', refreshError);
          }
          
          const response = await axios.post(
            `/work-orders/${props.workOrder.id}/attachments`,
            formData,
            {
              headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-XSRF-TOKEN': xsrfToken ? xsrfToken : '',
                'Accept': 'application/json',
              },
              // Make sure cookies are included with the request
              withCredentials: true,
              onUploadProgress: (progressEvent) => {
                const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                uploadProgress.value = percentCompleted;
              },
            }
          );

          console.log('Upload response:', response.data);

          if (response.data.success) {
            const attachments = response.data.attachments || [];
            props.workOrder.images = [...(props.workOrder.images || []), ...attachments];
            form.images = [];
            editingField.value.images = false;
          } else {
            uploadError.value = response.data.message || 'Upload failed. Please try again.';
          }
        } catch (error) {
          console.error('Error details:', {
            message: error.message,
            response: error.response?.data,
            status: error.response?.status,
          });

          if (error.response?.status === 422 && error.response?.data?.errors) {
            const validationErrors = error.response.data.errors;
            const errorMessages = Object.entries(validationErrors).map(([field, messages]) => {
              // Check if the error is related to attachments
              const fieldName = field.replace('attachments.', 'File ');
              return `${fieldName}: ${messages.join(', ')}`;
            });
            uploadError.value = errorMessages.join('\n');
          } else {
            uploadError.value = error.response?.data?.message || 'An error occurred during the upload.';
          }
        } finally {
          isUploading.value = false;
        }
      };

      // Function to get all attachments from both images and file_attachments fields
      const getAllAttachments = () => {
        const attachments = [];
        // Add images if they exist
        if (props.workOrder.images) {
          if (typeof props.workOrder.images === 'string') {
            try {
              const parsed = JSON.parse(props.workOrder.images);
              if (Array.isArray(parsed)) {
                attachments.push(...parsed);
              }
            } catch (e) {
              console.error('Error parsing images JSON:', e);
            }
          } else if (Array.isArray(props.workOrder.images)) {
            attachments.push(...props.workOrder.images);
          }
        }
        
        // Add file_attachments if they exist
        if (props.workOrder.file_attachments) {
          if (typeof props.workOrder.file_attachments === 'string') {
            try {
              const parsed = JSON.parse(props.workOrder.file_attachments);
              if (Array.isArray(parsed)) {
                attachments.push(...parsed);
              }
            } catch (e) {
              console.error('Error parsing file_attachments JSON:', e);
            }
          } else if (Array.isArray(props.workOrder.file_attachments)) {
            attachments.push(...props.workOrder.file_attachments);
          }
        }
        
        // Remove duplicates
        return [...new Set(attachments)];
      };

      const archiveWorkOrder = async (event) => {
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        try {
          button.disabled = true;
          button.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Archiving...';
          const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          if (!csrf) {
            throw new Error('CSRF token not found. Please refresh the page and try again.');
          }

          // Create request config with the latest CSRF token
          const config = {
            withCredentials: true,
            headers: {
              'X-CSRF-TOKEN': csrf,
              'X-Requested-With': 'XMLHttpRequest',
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          };

          const response = await axios.post(
            `/work-orders/${props.workOrder.id}/archive`,
            { archive: true },
            config
          );

          if (response.data.success) {
            emit('close');
            emit('work-order-archived', {
              workOrderId: props.workOrder.id,
              message: response.data.message || 'Work order has been successfully archived.'
            });
            window.location.reload();
          } else {
            throw new Error(response.data.message || 'Failed to archive work order');
          }
        } catch (error) {
          console.error('Error archiving work order:', error);
          const errorMessage = error.response?.data?.message || error.message;
          alert(`Failed to archive work order: ${errorMessage}`);
        } finally {
          if (button) {
            button.disabled = false;
            button.innerHTML = originalText;
          }
        }
      };

      const deleteAttachment = async (attachmentPath) => {
        if (!confirm('Are you sure you want to delete this attachment?')) {
          return;
        }

        try {
          await router.post(
            route('work-orders.delete-attachment', props.workOrder.id), 
            {
              attachment_path: attachmentPath
            },
            {
              preserveScroll: true,
              onSuccess: () => {
                // Update local state for both images and file_attachments
                if (props.workOrder.images) {
                  props.workOrder.images = Array.isArray(props.workOrder.images) 
                    ? props.workOrder.images.filter(img => img !== attachmentPath)
                    : [];
                }
                if (props.workOrder.file_attachments) {
                  props.workOrder.file_attachments = Array.isArray(props.workOrder.file_attachments)
                    ? props.workOrder.file_attachments.filter(file => file !== attachmentPath)
                    : [];
                }
              },
              onError: () => {
                alert('Failed to delete attachment. Please try again.');
              }
            }
          );
        } catch (error) {
          console.error('Error deleting attachment:', error);
          alert('Failed to delete attachment. Please try again.');
        }
      };

      // Add this function to handle document uploads from PdfViewer
      const handleDocumentUpload = async (data) => {
        try {
          console.log('Document uploaded:', data);
          if (!data || (!data.path && !data.url)) {
            throw new Error('No document path received from upload');
          }

          // Extract the path, handling both full URLs and storage paths
          let attachmentPath = '';
          if (data.path) {
            attachmentPath = data.path.replace(/^\/storage\//, '');
          } else if (data.url) {
            const pathMatch = data.url.match(/\/storage\/(.*)/);
            attachmentPath = pathMatch ? pathMatch[1] : '';
          }

          if (!attachmentPath) {
            throw new Error('Could not extract valid attachment path');
          }

          // Add the new attachment to both arrays if they exist
          if (!Array.isArray(props.workOrder.file_attachments)) {
            props.workOrder.file_attachments = [];
          }
          props.workOrder.file_attachments.push(attachmentPath);
          if (!Array.isArray(props.workOrder.images)) {
            props.workOrder.images = [];
          }
          props.workOrder.images.push(attachmentPath);

          // Close the preview  
          closePreview();

          // Show success message        
          console.log('Document successfully added:', attachmentPath);
        } catch (error) {
          console.error('Error handling document upload:', error);
          alert(`Failed to process the uploaded document: ${error.message}`);
        }
      };

      // Make sure workOrderData is always defined with safe fallback values
      const workOrderData = computed(() => {
        if (!props.workOrder) {
          return {
            title: 'Untitled Work Order',
            status: 'unknown',
            created_at: null,
            updated_at: null,
            attachments: [],
          };
        }
        return {
          title: props.workOrder.title || 'Untitled Work Order',
          status: props.workOrder.status || 'unknown',
          created_at: props.workOrder.created_at,
          updated_at: props.workOrder.updated_at,
          attachments: props.workOrder.attachments || [],
        };
      });

      return {
        getStatusClasses,
        isEditing,
        editingField,
        form,
        formatDate,
        closeModal: () => emit('close'),
        startEditing,
        saveField: (field) => {
          if (field === 'images') {
            saveImages();
          } else {
            saveField(field);
          }
        },
        duplicateWorkOrder,
        handleImageUpload,
        handlePdfClose,
        previewAttachment,
        handleDocumentUpload,
        isPdfFile,
        isImageFile,
        isDocumentFile,
        getFileName,
        handlePreviewAttachment,
        closePreview,
        getAllAttachments,
        formatMultipleDates,
        formatDateShort,
        dateSelectionType,
        selectedDates,
        addNewDate,
        removeDate,
        archiveWorkOrder,
        deleteAttachment,
        showPdfViewer,
        isUploading,
        uploadProgress,
        uploadError,
        getStatusProgress,
        getProgressBarColor,
        getUserName, // Ensure this is returned so it can be used in the template
        getUserAvatar, // Ensure this is returned so it can be used in the template
        VALID_STATUSES,
        showDuplicateDateModal,
        duplicateDate,
        cancelDuplicate,
        confirmDuplicate,
        isDuplicating,
        deleteWorkOrder,
        updateStatus,
        statusSelect,
        hasPdfAttachment,
        mostRecentPdfAttachment,
        getSignature,
        workOrderData, // Expose the computed workOrderData
      };
    },
    // Add an errorCaptured hook to handle and log errors
    errorCaptured(err, instance, info) {
      console.error('Error captured in WorkOrder component:', err);
      console.log('Error instance:', instance);
      console.log('Error info:', info);
      // Return false to prevent the error from propagating
      return false;
    }
  };
</script>

<style scoped>
.image-icon {
  color: rgb(96 165 250); /* text-blue-400 */
}

.file-icon {
  flex-shrink: 0;
  height: 1.25rem;
  width: 1.25rem;
}

.pdf-icon {
  color: rgb(248 113 113); /* text-red-400 */
}

/* Base styles */
.description {
  word-wrap: break-word;
  overflow-wrap: break-word;
  white-space: normal;
  max-width: 100%;
}

/* Focus outline */
:focus {
  outline-color: rgb(163 230 53); /* lime-400 */
}

/* Animation keyframes */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes modalFadeInSlide {
  from {
    opacity: 0;
    transform: translateY(-50px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Glass morphism styles */
.glossy-card {
  background: linear-gradient(135deg, rgba(17, 24, 39, 0.95), rgba(31, 41, 55, 0.85));
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.glossy-header {
  background: linear-gradient(180deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.85) 100%);
  position: relative;
  overflow: hidden;
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

/* Button styles */
.glossy-btn {
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(115, 115, 115, 0.1);
}

.glossy-btn::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(49, 49, 49, 0.223) 0%, transparent 80%);
  transform: rotate(45deg);
  opacity: 0;
  transition: opacity 0.3s ease;
}

/* Modal and scrollbar styles */
.modal-animation {
  animation: modalFadeInSlide 0.5s ease-out;
}

.modal-body {
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  height: 100%;
  width: 100%;
  max-height: calc(80vh - 125px);
  max-width: calc(50vh - 200px);
}

.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: rgba(75, 85, 99, 0.5) rgba(17, 24, 39, 0.3);
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: rgba(55, 65, 81, 0.7);
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background-color: rgba(17, 24, 39, 0.5);
}

/* Enhanced border styling for the header */
.glossy-header .border-b {
  border-image: linear-gradient(
    to right,
    transparent,
    rgba(255, 255, 255, 0.342),
    transparent
  ) 1;
}

/* Enhanced modal layout */
.glossy-card {
  display: flex;
  flex-direction: column;
  height: 65vh; /* Adjust this value as needed */
  max-height: 85vh;
}

/* Fixed header styling */
.glossy-header {
  flex-shrink: 0;
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
}

/* Scrollable content area */
.overflow-y-auto {
  flex-grow: 1;
}

/* Fixed footer styling */
.glossy-footer {
  flex-shrink: 0;
  border-bottom-right-radius: 0.5rem;
  border-bottom-left-radius: 0.5rem;
  background: linear-gradient(0deg, rgba(31, 41, 55, 0.95) 0%, rgba(17, 24, 39, 0.9) 100%);
}
</style>