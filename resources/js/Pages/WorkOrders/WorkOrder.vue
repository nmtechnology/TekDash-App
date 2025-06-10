<template>
  <div v-if="showModal">
    <!-- Background overlay -->
    <div @click="closeModal" class="fixed inset-0 bg-black bg-opacity-50 z-40"></div>
    
    <!-- Work Order Modal (left side, wider) -->
    <div class="fixed inset-0 flex items-center justify-center z-50 pointer-events-none">
      <div class="relative z-50 w-full max-w-5xl h-[75vh] ml-8 mt-16 rounded-lg overflow-hidden shadow-xl transform transition-all glossy-card pointer-events-auto flex flex-col">
        <!-- Header section -->
        <div class="glossy-header p-4 border-b border-gray-700">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-100">Work Order Details</h2>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-200">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Content section -->
        <div class="flex-1 p-4 overflow-y-auto timeline-container">
          <!-- Work Order Status and Time -->
          <div class="mb-4 flex flex-wrap justify-between items-center">
            <div>
              <span :class="getStatusClasses(workOrder.status)" @click="updateStatus">
                {{ workOrder.status }}
              </span>
            </div>
            <div class="text-gray-300 text-sm">
              {{ formatDate(workOrder.date_time) }}
            </div>
          </div>

          <!-- Work Order Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="space-y-4">
              <!-- Title -->
              <div>
                <label class="block text-sm font-medium text-gray-300">Title</label>
                <div v-if="!editingField.title" @click="startEditing('title')" class="text-gray-100">
                  {{ form.title }}
                </div>
                <div v-else class="mt-1">
                  <input type="text" v-model="form.title" @blur="saveField('title')" class="block w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-300">Description</label>
                <div v-if="!editingField.description" @click="startEditing('description')" class="text-gray-100">
                  {{ form.description }}
                </div>
                <div v-else class="mt-1">
                  <textarea v-model="form.description" @blur="saveField('description')" class="block w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-indigo-500" rows="3"></textarea>
                </div>
              </div>

              <!-- Address -->
              <div>
                <label class="block text-sm font-medium text-gray-300">Address</label>
                <div v-if="!editingField.address" @click="startEditing('address')" class="text-gray-100">
                  {{ form.address }}
                </div>
                <div v-else class="mt-1">
                  <input type="text" v-model="form.address" @blur="saveField('address')" class="block w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <!-- Hours -->
              <div>
                <label class="block text-sm font-medium text-gray-300">Hours</label>
                <div v-if="!editingField.hours" @click="startEditing('hours')" class="text-gray-100">
                  {{ form.hours }}
                </div>
                <div v-else class="mt-1">
                  <input type="number" v-model="form.hours" @blur="saveField('hours')" class="block w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-indigo-500" min="0" step="0.5" />
                </div>
              </div>

              <!-- Hourly Rate -->
              <div>
                <label class="block text-sm font-medium text-gray-300">Hourly Rate</label>
                <div v-if="!editingField.hourly_rate" @click="startEditing('hourly_rate')" class="text-gray-100">
                  {{ formatCurrency(form.hourly_rate) }}
                </div>
                <div v-else class="mt-1">
                  <input type="number" v-model="form.hourly_rate" @blur="saveField('hourly_rate')" class="block w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-indigo-500" min="0" step="0.01" />
                </div>
              </div>

              <!-- Travel Costs -->
              <div>
                <div class="flex items-center mb-2">
                  <input type="checkbox" v-model="form.has_travel" @change="saveField('has_travel')" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800" />
                  <label class="ml-2 text-sm font-medium text-gray-300">Include Travel Costs</label>
                </div>
                <div v-if="form.has_travel">
                  <div v-if="!editingField.travel_cost" @click="startEditing('travel_cost')" class="text-gray-100">
                    {{ formatCurrency(form.travel_cost) }}
                  </div>
                  <div v-else class="mt-1">
                    <input type="number" v-model="form.travel_cost" @blur="saveField('travel_cost')" class="block w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-indigo-500" min="0" step="0.01" />
                  </div>
                </div>
              </div>

              <!-- Total Amount -->
              <div class="p-4 bg-gray-800 rounded-lg">
                <div class="text-sm font-medium text-gray-400">Total Amount</div>
                <div class="text-2xl font-bold text-white">{{ formatCurrency(totalAmount) }}</div>
              </div>
            </div>
          </div>

          <!-- Files and Attachments -->
          <div class="mb-4">
            <h3 class="text-lg font-medium text-gray-200 mb-2">Files & Attachments</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              <template v-for="attachment in getAllAttachments()" :key="attachment.id">
                <!-- Image Preview -->
                <div v-if="isImageFile(attachment)" @click="handlePreviewAttachment(attachment)" class="cursor-pointer relative group">
                  <img :src="attachment" class="w-full h-32 object-cover rounded-lg" />
                  <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <span class="text-white">Preview</span>
                  </div>
                </div>

                <!-- PDF Preview -->
                <div v-else-if="isPdfFile(attachment)" class="cursor-pointer">
                  <PdfThumbnail :file="attachment" @click="handlePreviewAttachment(attachment)" />
                </div>
              </template>
            </div>
          </div>

          <!-- Messaging Section -->
          <div>
            <h3 class="text-lg font-medium text-gray-200 mb-2">Messages</h3>
            <Messenger 
              :workOrderId="workOrder.id"
              :userId="workOrder.user_id"
              :messages="workOrder.messages || []"
              :currentUserId="workOrder.user_id"
              :getUserName="getUserName"
              :getUserAvatar="getUserAvatar"
              :users="users"
            />
          </div>
        </div>

        <!-- Footer section with all action buttons -->
        <div class="glossy-footer p-4 border-t border-gray-700">
          <!-- Footer buttons -->
          <div class="sm:flex sm:flex-row-reverse">
            <!-- Archive Work Order button -->
            <button 
              @click="archiveWorkOrder" 
              :disabled="workOrder.status !== 'Complete'"
              class="glossy-btn btn w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-3 py-1.5 text-green-400 hover:text-gray-900 hover:bg-green-400 font-bold sm:ml-2 sm:w-auto sm:text-xs"
              :class="{
                'opacity-50 cursor-not-allowed hover:bg-transparent hover:text-green-400': workOrder.status !== 'Complete'
              }"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
              </svg>
              Archive
            </button>
            
            <!-- Duplicate button -->
            <button 
              @click="duplicateWorkOrder($event)" 
              :disabled="workOrder.status !== 'Part Needed'"
              :class="[
                'glossy-btn btn w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-3 py-1.5 text-purple-400 font-bold hover:bg-purple-400 hover:text-black sm:ml-2 sm:w-auto sm:text-xs',
                { 'opacity-50 cursor-not-allowed': workOrder.status !== 'Part Needed' }
              ]"
              :title="workOrder.status !== 'Part Needed' ? 'Duplication is only available for work orders with Part Needed status' : 'Create a duplicate work order'"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              Duplicate
            </button>

           <!-- Get Signature button -->
            <button @click="getSignature" 
              class="glossy-btn btn w-full inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-blue-400 font-bold hover:bg-blue-400 hover:text-black sm:ml-2 sm:w-auto sm:text-xs"
              :disabled="!hasPdfAttachment"
              :class="{ 'opacity-50 cursor-not-allowed hover:bg-transparent hover:text-indigo-400': !hasPdfAttachment }"
              :title="!hasPdfAttachment ? 'A PDF document must be attached to get signatures' : `Click to sign ${getFileName(mostRecentPdfAttachment)}`"
            >    
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">          
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 3.487a2.688 2.688 0 113.798 3.798L7.21 19.736a4.5 4.5 0 01-1.889 1.13l-2.7.9.9-2.7a4.5 4.5 0 011.13-1.89l12.75-12.75z" />
              </svg>
              Collect Signature
            </button>
            
            <!-- Add a hidden file input -->
            <input 
              type="file" 
              ref="fileInput"
              multiple 
              class="hidden"
              @change="handleImageUpload" 
              accept=".jpg,.jpeg,.png,.gif,.pdf,.heic,.docx"
            />

            <!-- Upload Files button -->
             <button 
              @click.prevent="$refs.fileInput.click()" 
              v-if="!editingField.images"
              class="glossy-btn btn font-bold w-full inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-lime-400 hover:bg-lime-400 hover:text-black sm:ml-2 sm:w-auto sm:text-xs"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
              </svg>
              Upload Files
            </button>
            
            <!-- Save button when editing -->
            <button 
              v-else
              @click="saveField('images')" 
              class="glossy-btn btn font-bold w-full inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-green-400 hover:bg-green-400 hover:text-black sm:ml-2 sm:w-auto sm:text-xs"
              :disabled="isUploading"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ isUploading ? 'Uploading...' : 'Save' }}
            </button>
            
            <!-- Update Status button -->
            <button
              @click="updateStatus"
              class="glossy-btn btn w-full inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-indigo-400 font-bold hover:bg-indigo-400 hover:text-black sm:ml-2 sm:w-auto sm:text-xs"
            >
              Update Status
            </button>

            <!-- Delete button -->
            <button 
              @click="deleteWorkOrder" 
              class="glossy-btn btn mt-3 w-full inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-red-400 font-bold hover:bg-red-400 hover:text-black sm:mt-0 sm:ml-2 sm:w-auto sm:text-xs"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Delete
            </button>
          </div>
        </div>

        <!-- Preview Modal -->
        <div v-if="previewAttachment" class="fixed inset-0 z-50 flex items-center justify-center">
          <div @click="closePreview" class="absolute inset-0 bg-black bg-opacity-75"></div>
          <div class="relative z-10 max-w-4xl w-full bg-gray-900 rounded-lg overflow-hidden">
            <div class="p-4 border-b border-gray-800 flex justify-between items-center">
              <h3 class="text-lg font-medium text-gray-200">{{ getFileName(previewAttachment) }}</h3>
              <button @click="closePreview" class="text-gray-500 hover:text-gray-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div class="relative">
              <template v-if="isImageFile(previewAttachment)">
                <img :src="previewAttachment" class="max-w-full h-auto" />
              </template>
              <template v-else-if="isPdfFile(previewAttachment)">
                <PdfViewer :file="previewAttachment" />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import Timeline from '@/Components/Timeline.vue';
import PdfThumbnail from '@/Components/PdfThumbnail.vue';
import PdfViewer from '@/Components/PdfViewer.vue';
import Messenger from '@/Components/Messenger.vue';
import axios from 'axios';

const props = defineProps({
  workOrder: {
    type: Object,
    required: true
  },
  showModal: {
    type: Boolean,
    required: true
  },
  users: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'archived']);

// Define the editingField ref to track which fields are being edited
const editingField = ref({
  title: false,
  description: false,
  date_time: false,
  status: false,
  hourly_rate: false,
  customer_id: false,
  images: false,
  address: false,
  hours: false
});

// Add form data ref with initial values from workOrder prop
const form = ref({
  customer_id: props.workOrder?.customer_id || '',
  title: props.workOrder?.title || '',
  description: props.workOrder?.description || '',
  date_time: props.workOrder?.date_time || '',
  end_date: props.workOrder?.end_date || '',
  visit_dates: props.workOrder?.visit_dates || [],
  status: props.workOrder?.status || 'Scheduled',
  hourly_rate: props.workOrder?.hourly_rate || 0,
  notes: props.workOrder?.notes || [],
  images: [],
  user_id: props.workOrder?.user_id || '',
  address: props.workOrder?.address || '',
  hours: props.workOrder?.hours || 0,
  travel_cost: props.workOrder?.travel_cost || 0,
  has_travel: props.workOrder?.has_travel || false
});

// Function to start editing a field
const startEditing = (field) => {
  Object.keys(editingField.value).forEach(key => {
    editingField.value[key] = false;
  });
  editingField.value[field] = true;
};

// Function to save a field
const saveField = async (field) => {
  // Handle special field cases before saving
  if (field === 'date_time') {
    // Handle different date selection types
    if (dateSelectionType.value === 'single') {
      // For single date, just use the date_time value
      // No need to modify form.value
    } else if (dateSelectionType.value === 'range') {
      // For date range, we need both start and end dates
      if (!form.value.end_date) {
        // If no end date, default to start date + 1 hour
        const startDate = new Date(form.value.date_time);
        startDate.setHours(startDate.getHours() + 1);
        form.value.end_date = startDate.toISOString().slice(0, 16);
      }
    } else if (dateSelectionType.value === 'multiple') {
      // For multiple dates, use the selectedDates array
      form.value.visit_dates = [...selectedDates.value]; // Make a copy
    }
  } else if (field === 'images') {
    // For images, we would normally upload them here
    isUploading.value = false;
    uploadProgress.value = 0;
    uploadError.value = null;
  }
  
  try {
    const response = await axios.put(`/work-orders/${props.workOrder.id}`, {
      [field]: form.value[field]
    });
    
    if (response.data.success) {
      // Update was successful
      editingField.value[field] = false;
      
      // If we're updating hours or rates, recalculate grand total
      if (['hours', 'hourly_rate', 'travel_cost', 'has_travel'].includes(field)) {
        await updateGrandTotal();
      }
    }
  } catch (error) {
    console.error(`Error saving ${field}:`, error);
  }
};

// Function to update grand total
const updateGrandTotal = async () => {
  try {
    const response = await axios.post(`/work-orders/${props.workOrder.id}/update-total`);
    if (response.data.success) {
      props.workOrder.grand_total = response.data.grand_total;
    }
  } catch (error) {
    console.error('Error updating grand total:', error);
  }
};

// Function to close modal
const closeModal = () => {
  emit('close');
};

// Define VALID_STATUSES
const VALID_STATUSES = [
  'Scheduled',
  'In Progress',
  'Part Needed',
  'Complete',
  'Cancelled'
];

// File type helpers
const isImageFile = (attachment) => {
  if (!attachment) return false;
  const fileName = getFileName(attachment).toLowerCase();
  return fileName.endsWith('.jpg') || 
         fileName.endsWith('.jpeg') || 
         fileName.endsWith('.png') || 
         fileName.endsWith('.gif') ||
         fileName.endsWith('.heic');
};

const isPdfFile = (attachment) => {
  if (!attachment) return false;
  const fileName = getFileName(attachment).toLowerCase();
  return fileName.endsWith('.pdf');
};

// Add function to get file name
const getFileName = (attachment) => {
  if (!attachment) return '';
  
  if (typeof attachment === 'string') {
    const parts = attachment.split('/');
    return parts[parts.length - 1];
  }
  
  return attachment.file_name || attachment.name || '';
};

// Add missing properties referenced in the template
const hasPdfAttachment = computed(() => {
  return props.workOrder?.attachments?.some(attachment => 
    isPdfFile(attachment)
  ) || false;
});

const mostRecentPdfAttachment = computed(() => {
  if (!props.workOrder?.attachments) return null;
  
  const pdfFiles = props.workOrder.attachments
    .filter(attachment => isPdfFile(attachment))
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  
  return pdfFiles.length > 0 ? pdfFiles[0] : null;
});

// Get all attachments
const getAllAttachments = () => {
  return props.workOrder?.attachments || [];
};

// Preview attachment handling
const previewAttachment = ref(null);
const fileInput = ref(null);
    
const handlePreviewAttachment = (attachment) => {
  previewAttachment.value = attachment;
};
    
const closePreview = () => {
  previewAttachment.value = null;
};

// Add upload-related properties and functions
const isUploading = ref(false);
const uploadProgress = ref(0);
const uploadError = ref(null);
    
const handleImageUpload = (event) => {
  const files = event.target.files;
  if (!files.length) return;
      
  // Set images to uploaded files (simplified)
  form.value.images = Array.from(files);
  editingField.value.images = true;
  isUploading.value = false; // This would normally be set true during upload
};

// Attachment management
const deleteAttachment = (attachment) => {
  if (confirm('Are you sure you want to delete this attachment?')) {
    console.log('Deleting attachment', attachment);
    // This would normally make an API call to delete the attachment
  }
};

const handleDocumentUpload = (documentData) => {
  console.log('Document uploaded', documentData);
  // This would normally handle the uploaded document
};

// Add missing functions referenced in the template
const updateStatus = () => {
  startEditing('status');
};

const deleteWorkOrder = () => {
  if (confirm('Are you sure you want to delete this work order?')) {
    console.log('Deleting work order', props.workOrder.id);
    // This would normally make an API call to delete the work order
  }
};

const duplicateWorkOrder = (event) => {
  if (props.workOrder.status !== 'Part Needed') return;
      
  console.log('Duplicating work order', props.workOrder.id);
  // This would normally create a copy of the work order
};

const getSignature = () => {
  if (!hasPdfAttachment.value) return;
      
  console.log('Getting signature for', getFileName(mostRecentPdfAttachment.value));
  // This would normally open a signature dialog
};

// User-related functions for Messenger component
const getUserName = (userId) => {
  if (!userId) return 'Unknown User';
      
  // Check if users prop is available and find matching user
  const user = props.users?.find(user => user.id === userId || user.name === userId);
  return user ? user.name : userId;
};

const getUserAvatar = (userId) => {
  if (!userId) return null;
      
  // Check if users prop is available and find matching user
  const user = props.users?.find(user => user.id === userId || user.name === userId);
  return user?.profile_photo_url || null;
};

// Computed property for total amount
const totalAmount = computed(() => {
  const laborTotal = (props.workOrder?.hourly_rate || 0) * (props.workOrder?.hours || 0);
  const travelCost = props.workOrder?.has_travel ? (props.workOrder?.travel_cost || 0) : 0;
  return laborTotal + travelCost;
});

// Format numbers to USD currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value);
};

// Logic for handling special field cases has been moved to saveField function

// Add getStatusClasses function with more comprehensive status handling
const getStatusClasses = (status) => {
  if (!status) return 'bg-gray-800 text-gray-300 ring-gray-700 px-2 py-1 rounded-md';
  
  const statusLower = status.toLowerCase();
  
  // Status classes mapping with more specific matches first
  const statusMapping = {
    'complete': 'bg-green-800 text-green-100 ring-green-700',
    'completed': 'bg-green-800 text-green-100 ring-green-700',
    'in progress': 'bg-yellow-800 text-yellow-100 ring-yellow-700',
    'progress': 'bg-yellow-800 text-yellow-100 ring-yellow-700',
    'scheduled': 'bg-blue-800 text-blue-100 ring-blue-700',
    'rescheduled': 'bg-blue-700 text-blue-100 ring-blue-600',
    'pending': 'bg-orange-800 text-orange-100 ring-orange-700',
    'on hold': 'bg-orange-700 text-orange-100 ring-orange-600',
    'cancel': 'bg-red-800 text-red-100 ring-red-700',
    'cancelled': 'bg-red-800 text-red-100 ring-red-700',
    'part': 'bg-purple-800 text-purple-100 ring-purple-700',
    'part needed': 'bg-purple-800 text-purple-100 ring-purple-700',
    'return': 'bg-indigo-800 text-indigo-100 ring-indigo-700',
  };
  
  // Find the first matching status key
  for (const [key, classes] of Object.entries(statusMapping)) {
    if (statusLower.includes(key)) {
      return `${classes} px-2 py-1 rounded-md`;
    }
  }
  
  // Default styling for unknown statuses
  return 'bg-gray-800 text-gray-300 ring-gray-700 px-2 py-1 rounded-md';
};

// Archive work order function 
const archiveWorkOrder = () => {
  // Only allow archiving if status is Complete
  if (props.workOrder.status !== 'Complete') {
    console.log('Cannot archive work order unless it is Complete');
    return;
  }
  
  // In a real implementation, this would make an API call to archive the work order
  console.log('Archiving work order', props.workOrder.id);
  
  // Confirm with user
  if (confirm('Are you sure you want to archive this completed work order?')) {
    // You would typically make an API call here
    axios.put(`/api/work-orders/${props.workOrder.id}/archive`)
      .then(response => {
        console.log('Work order archived successfully');
        closeModal();
        // Optionally emit an event to refresh the parent component
        emit('archived', props.workOrder.id);
      })
      .catch(error => {
        console.error('Error archiving work order:', error);
      });
  }
};

// Define the dateSelectionType ref
const dateSelectionType = ref('single');
const selectedDates = ref([new Date().toISOString().slice(0, 16)]);
    
// Define functions for multiple date selection
const addNewDate = () => {
  // Add a new date with the current time, formatted for datetime-local input
  const now = new Date();
  // Format as YYYY-MM-DDThh:mm
  const formattedDate = now.toISOString().slice(0, 16);
  selectedDates.value.push(formattedDate);
};

const removeDate = (index) => {
  // Only remove if we have more than one date
  if (selectedDates.value.length > 1) {
    selectedDates.value.splice(index, 1);
  } else {
    // If this is the last date, just reset it to current time
    selectedDates.value[0] = new Date().toISOString().slice(0, 16);
  }
};

// Define formatDate and formatMultipleDates functions
const formatDate = (date) => {
  if (!date) return 'No date set';
  
  try {
    const parsedDate = new Date(date);
    if (isNaN(parsedDate)) return 'Invalid date';
    
    // Format date: Monday, June 9, 2025 at 3:30 PM
    const options = { 
      weekday: 'long', 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    };
    
    return parsedDate.toLocaleString('en-US', options);
  } catch (error) {
    console.error('Error formatting date:', error);
    return 'Invalid date';
  }
};

const formatMultipleDates = (dates) => {
  if (!dates || !dates.length) return 'No dates set';
  
  // If only one date, use formatDate
  if (dates.length === 1) {
    return formatDate(dates[0]);
  }
  
  // If we have a small number of dates, list them individually
  if (dates.length <= 3) {
    return dates.map(date => formatDate(date)).join('\n');
  }
  
  // Otherwise, show the number and date range
  try {
    const parsedDates = dates.map(d => new Date(d));
    const validDates = parsedDates.filter(d => !isNaN(d));
    
    if (validDates.length === 0) return 'No valid dates';
    
    // Find the earliest and latest dates
    const earliest = new Date(Math.min(...validDates));
    const latest = new Date(Math.max(...validDates));
    
    // Format as: "5 dates from Jun 9 to Jun 15, 2025"
    const dateOptions = { month: 'short', day: 'numeric' };
    const yearOptions = { year: 'numeric' };
    
    return `${validDates.length} dates from ${earliest.toLocaleDateString('en-US', dateOptions)} to ${latest.toLocaleDateString('en-US', dateOptions)}, ${latest.toLocaleDateString('en-US', yearOptions)}`;
  } catch (error) {
    console.error('Error formatting multiple dates:', error);
    return `${dates.length} dates scheduled`;
  }
};

// All variables and functions declared in the script setup are automatically exposed to the template
// No need for an explicit return statement
</script>

<style scoped>
.timeline-container {
  max-height: 65vh;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: rgba(75, 85, 99, 0.5) rgba(17, 24, 39, 0.3);
}

.timeline-container::-webkit-scrollbar {
  width: 6px;
}

.timeline-container::-webkit-scrollbar-thumb {
  background-color: rgba(55, 65, 81, 0.7);
  border-radius: 3px;
}

.timeline-container::-webkit-scrollbar-track {
  background-color: rgba(17, 24, 39, 0.5);
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
  flex-shrink: 0;
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
  background: linear-gradient(180deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.85) 100%);
}

.glossy-footer {
  flex-shrink: 0;
  border-bottom-right-radius: 0.5rem;
  border-bottom-left-radius: 0.5rem;
  background: linear-gradient(0deg, rgba(31, 41, 55, 0.95) 0%, rgba(17, 24, 39, 0.9) 100%);
}
</style>
