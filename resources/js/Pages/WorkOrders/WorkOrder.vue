<template>
  <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
    <!-- Semi-transparent overlay with blur -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    
    <!-- Modal container using the provided styling -->
    <div class="inline-block align-bottom bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl mx-4 sm:mx-auto border border-gray-700">
      <!-- Modal header -->
      <div class="bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-700">
        <div class="sm:flex sm:items-start">
          <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
            <!-- Header with close button -->
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg leading-6 font-medium text-lime-400" id="modal-title">
                <span v-if="!editingField.title" class="flex items-center">
                   {{ workOrder.title }}
                  <span 
                    class="ml-3 inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                    :class="getStatusClasses(workOrder.status)"
                  >
                    {{ workOrder.status }}
                  
                  </span>
                </span>
                <input 
                  v-else 
                  type="text" 
                  v-model="form.title" 
                  class="w-full rounded-md bg-gray-800 border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 dark:focus:border-lime-400 dark:focus:ring-lime-400 text-white text-lg font-medium"
                />
              </h3>
              <div class="flex space-x-2">
                <button v-if="!editingField.title" @click="startEditing('title')" class="text-gray-400 hover:text-gray-200">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                </button>
                <button v-else @click="saveField('title')" class="text-green-400 hover:text-green-300">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </button>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-200">
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
            
            <!-- Work order details -->
            <div class="text-gray-300 space-y-4">
              <!-- Customer section -->
              <div class="flex justify-between items-start">
                <div class="w-full">
                  <p class="text-sm text-gray-400">Customer:</p>
                  <div class="flex items-center">
                    <span v-if="!editingField.customer_id" class="text-white">{{ workOrder.customer_id }}</span>
                    <select v-else v-model="form.customer_id" class="mt-1 block w-full rounded-md bg-gray-800 border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm">
                      <option value="Advanced Project Solutions">Advanced Project Solutions</option>
                      <option value="Barrister Global Service Network">Barrister Global Service Network</option>
                      <option value="DarAlIslam">DarAlIslam</option>
                      <option value="Field Nation">Field Nation</option>
                      <option value="Navco">Navco</option>
                      <option value="NEW CUSTOMER">NEW CUSTOMER</option>
                      <option value="NuTech National">NuTech National</option>
                      <option value="Telaid">Telaid</option>
                    </select>
                    <span class="ml-2">
                      <button v-if="!editingField.customer_id" @click="startEditing('customer_id')" class="text-lime-400 hover:text-lime-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                      </button>
                      <button v-else @click="saveField('customer_id')" class="text-green-400 hover:text-green-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                      </button>
                    </span>
                  </div>
                </div>
                
                <div>
                  <p class="text-sm text-gray-400">Price:</p>
                  <div class="flex items-center">
                    <span v-if="!editingField.price" class="text-white">${{ workOrder.price }}</span>
                    <input v-else type="number" v-model="form.price" class="mt-1 w-24 rounded-md bg-gray-800 border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm" />
                    <span class="ml-2">
                      <button v-if="!editingField.price" @click="startEditing('price')" class="text-lime-400 hover:text-lime-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                      </button>
                      <button v-else @click="saveField('price')" class="text-green-400 hover:text-green-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                      </button>
                    </span>
                  </div>
                </div>
              </div>
              
              <!-- Status section -->
              <div class="flex justify-between items-start">
                <div class="w-full">
                  <p class="text-sm text-gray-400">Status:</p>
                  <div class="flex items-center">
                    <span v-if="!editingField.status" :class="getStatusClasses(workOrder.status).includes('text')">{{ workOrder.status }}</span>
                    <select v-else v-model="form.status" class="mt-1 block w-full rounded-md bg-gray-800 border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm">
                      <option value="Scheduled">Scheduled</option>
                      <option value="In Progress">In Progress</option>
                      <option value="Part/Return">Part/Return</option>
                      <option value="Complete">Complete</option>
                      <option value="Cancelled">Cancelled</option>
                    </select>
                    <span class="ml-2">
                      <button v-if="!editingField.status" @click="startEditing('status')" class="text-lime-400 hover:text-lime-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                      </button>
                      <button v-else @click="saveField('status')" class="text-green-400 hover:text-green-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                      </button>
                    </span>
                  </div>
                </div>
                
                <div>
                  <p class="text-sm text-gray-400">Date:</p>
                  <div class="flex items-center">
                    <span v-if="!editingField.date_time" class="text-white">{{ formatDate(workOrder.date_time) }}</span>
                    <input v-else type="datetime-local" v-model="form.date_time" class="mt-1 rounded-md bg-gray-800 border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm" />
                    <span class="ml-2">
                      <button v-if="!editingField.date_time" @click="startEditing('date_time')" class="text-lime-400 hover:text-lime-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                      </button>
                      <button v-else @click="saveField('date_time')" class="text-green-400 hover:text-green-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                      </button>
                    </span>
                  </div>
                </div>
              </div>
              
              <!-- Description section -->
              <div>
                <p class="text-sm text-gray-400">Description:</p>
                <div class="flex">
                  <div class="flex-grow">
                    <div v-if="!editingField.description" class="whitespace-pre-line mt-1 text-sm bg-gray-800 p-3 rounded-md text-white">
                      {{ workOrder.description }}
                    </div>
                    <textarea v-else v-model="form.description" rows="4" class="mt-1 block w-full rounded-md bg-gray-800 border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm"></textarea>
                  </div>
                  <span class="ml-2 flex-shrink-0">
                    <button v-if="!editingField.description" @click="startEditing('description')" class="text-lime-400 hover:text-lime-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                      </svg>
                    </button>
                    <button v-else @click="saveField('description')" class="text-green-400 hover:text-green-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                  </span>
                </div>
              </div>
              
              <!-- User section -->
              <div>
                <p class="text-sm text-gray-400">Assigned to:</p>
                <p class="text-white">{{ getUserName(workOrder.user_id) }}</p>
              </div>
              
              <!-- Attachments section - Updated to handle multiple attachment fields -->
              <div>
                <p class="text-sm text-gray-400">Attachments:</p>
                <ul role="list" class="mt-1 divide-y divide-gray-700 rounded-md border border-gray-700">
                  <li v-for="(attachment, index) in getAllAttachments()" :key="index" class="flex items-center justify-between py-2 pl-3 pr-4 text-sm">
                    <div class="flex w-0 flex-1 items-center">
                      <!-- Different icons for different file types -->
                      <svg v-if="isPdfFile(attachment)" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                      </svg>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <span class="ml-2 flex-1 truncate text-white">{{ getFileName(attachment) }}</span>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex space-x-2">
                      <!-- Preview button for images only -->
                      <button 
                        v-if="isImageFile(attachment)" 
                        @click="previewAttachment(attachment)" 
                        class="text-lime-400 hover:text-lime-300"
                      >
                        View
                      </button>
                      <a :href="`/storage/${attachment}`" class="text-lime-400 hover:text-lime-300" download>Download</a>
                    </div>
                  </li>
                  <li v-if="!editingField.images && getAllAttachments().length === 0" class="flex items-center justify-between py-2 pl-3 pr-4 text-sm">
                    <span class="text-gray-400">No attachments</span>
                    <button @click="startEditing('images')" class="text-lime-400 hover:text-lime-300">Add</button>
                  </li>
                  <li v-if="editingField.images" class="flex items-center justify-between py-2 pl-3 pr-4 text-sm">
                    <input 
                      type="file" 
                      id="images" 
                      multiple 
                      @change="handleImageUpload" 
                      class="text-white" 
                      accept=".jpg,.jpeg,.png,.gif,.pdf"
                    />
                    <div class="text-xs text-gray-400 ml-2">
                      Supports: PDF, JPG, PNG, GIF
                    </div>
                    <button @click="saveField('images')" class="text-lime-400 hover:text-lime-300">Save</button>
                  </li>
                </ul>
              </div>

              <!-- Image preview modal -->
              <div v-if="previewImage" class="fixed inset-0 z-[100] flex items-center justify-center">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="closePreview"></div>
                <div class="relative max-w-4xl max-h-[90vh] overflow-auto">
                  <img :src="`/storage/${previewImage}`" class="max-w-full max-h-full object-contain" />
                  <button 
                    @click="closePreview" 
                    class="absolute top-2 right-2 bg-gray-800 rounded-full p-1 text-gray-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-lime-400"
                  >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
              
              <!-- Notes section -->
              <div v-if="workOrder.notes">
                <p class="text-sm text-gray-400">Notes:</p>
                <Messenger 
                  :initialNotes="workOrder.notes" 
                  :workOrderId="workOrder.id"
                  :userId="$page.props.auth.user.id"
                  :getUserName="getUserName"
                  :getUserAvatar="getUserAvatar"
                  :currentUserAvatar="$page.props.auth.user.profile_photo_url"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Modal footer -->
      <div class="bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <button @click="duplicateWorkOrder" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 outline text-green-400 font-medium hover:bg-lime-700 sm:ml-3 sm:w-auto sm:text-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          Duplicate
        </button>
        <button @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-700 shadow-sm px-4 py-2 outline text-red-400 font-medium hover:bg-gray-600 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch, onMounted } from 'vue';
import { format } from 'date-fns';
import { useForm } from '@inertiajs/vue3';
import Messenger from '@/Components/Messenger.vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3'; // Import router for navigation

export default {
  name: 'WorkOrder',
  components: {
    Messenger,
  },
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
    // ...existing code...

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

    // Rest of existing functions
    const isEditing = ref(false);
    const editingField = ref({
      title: false,
      description: false,
      date_time: false,
      status: false,
      price: false,
      customer_id: false,
      images: false
    });

    const form = useForm({
      customer_id: props.workOrder?.customer_id || '',
      title: props.workOrder?.title || '',
      description: props.workOrder?.description || '',
      date_time: props.workOrder?.date_time || '',
      status: props.workOrder?.status || 'Scheduled',
      price: props.workOrder?.price || 0,
      notes: props.workOrder?.notes || [],
      images: [],
      user_id: props.workOrder?.user_id || ''
    });

    // ... more existing code ...

    // Update form when work order changes
    watch(() => props.workOrder, (newVal) => {
      if (newVal) {
        form.customer_id = newVal.customer_id || '';
        form.title = newVal.title || '';
        form.description = newVal.description || '';
        form.date_time = newVal.date_time || '';
        form.status = newVal.status || 'Scheduled';
        form.price = newVal.price || 0;
        form.notes = newVal.notes || [];
        form.user_id = newVal.user_id || '';
      }
    }, { immediate: true });

    // ... rest of existing functions ...

    const saveField = (field) => {
      // Create a proper data object that matches what the controller expects
      let data = {};
      
      // Special handling for different field types
      if (field === 'date_time' && form[field]) {
        // Ensure date is in the correct format
        data[field] = form[field];
      } else if (field === 'price') {
        // Ensure price is a number
        data[field] = Number(form[field]);
      } else {
        // Standard field
        data[field] = form[field];
      }
      
      // Log what we're sending for debugging
      console.log(`Updating ${field} with:`, data);
      
      // Use the correct route with hyphen: "work-orders" instead of "workorders"
      axios.post(`/work-orders/${props.workOrder.id}/update-field`, data)
        .then(response => {
          if (response.data.success) {
            // Update the local work order object to reflect changes
            props.workOrder[field] = form[field];
            console.log('Field updated successfully:', response.data);
          } else {
            console.error('Update returned success: false');
          }
          
          // Reset editing state
          editingField.value[field] = false;
        })
        .catch(error => {
          console.error('Error updating work order:', error);
          if (error.response && error.response.data) {
            console.error('Server validation errors:', error.response.data);
          }
        });
    };
    
    // For image uploads, we need special handling
    // const saveImages = () => {
    //   if (form.images && form.images.length > 0) {
    //     const formData = new FormData();
    //     form.images.forEach((image, index) => {
    //       formData.append(`images[${index}]`, image);
    //     });
        
    //     // Use the correct route with hyphen: "work-orders" instead of "workorders"
    //     axios.post(`/work-orders/${props.workOrder.id}/update-images`, formData, {
    //       headers: {
    //         'Content-Type': 'multipart/form-data'
    //       }
    //     })
    //     .then(response => {
    //       if (response.data.success) {
    //         // Update work order with new images from response
    //         props.workOrder.images = response.data.images;
    //       }
          
          // Reset editing state
    //       editingField.value.images = false;
    //       form.images = [];
    //     })
    //     .catch(error => {
    //       console.error('Error uploading images:', error);
    //       if (error.response && error.response.data) {
    //         console.error('Server validation errors:', error.response.data);
    //       }
    //     });
    //   } else {
    //     editingField.value.images = false;
    //   }
    // };

    const duplicateWorkOrder = () => {
      // Show a loading indicator or disable the button if needed
      const button = document.querySelector('button[disabled]');
      if (button) button.disabled = true;
      
      // Call the server endpoint to duplicate the work order
      axios.post(`/work-orders/${props.workOrder.id}/duplicate`)
        .then(response => {
          if (response.data && response.data.success) {
            // If the server returns a redirect URL, use it
            if (response.data.redirect) {
              window.location.href = response.data.redirect;
            } else {
              // Otherwise, just refresh the dashboard
              router.visit('/dashboard', { 
                replace: true,
                onSuccess: () => {
                  // Show success message if needed
                  console.log('Work order duplicated successfully');
                }
              });
            }
          } else {
            console.error('Failed to duplicate work order', response.data);
            // Re-enable the button
            if (button) button.disabled = false;
          }
        })
        .catch(error => {
          console.error('Error duplicating work order:', error);
          // Re-enable the button
          if (button) button.disabled = false;
          
          // Show error message to user
          alert('Failed to duplicate work order. Please try again.');
        });
    };

    // Add state for image preview
    const previewImage = ref(null);
    
    // Helper functions for file types
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
             lowerFilename.endsWith('.webp');
    };
    
    // Get clean file name for display
    const getFileName = (path) => {
      if (!path) return '';
      // Extract just the filename from the full path
      return path.split('/').pop();
    };
    
    // Preview attachment (for images)
    const previewAttachment = (attachment) => {
      if (isImageFile(attachment)) {
        previewImage.value = attachment;
      }
    };
    
    // Close preview
    const closePreview = () => {
      previewImage.value = null;
    };
    
    // Updated file upload handler that accepts PDFs
    const handleFileUpload = (event) => {
      const files = event.target.files;
      if (files.length) {
        form.images = Array.from(files);
        
        // Add validation if needed
        const validFiles = form.images.filter(file => {
          const isValid = file.type === 'application/pdf' || 
                         file.type.startsWith('image/');
          if (!isValid) {
            console.error(`Invalid file type: ${file.type}`);
          }
          return isValid;
        });
        
        if (validFiles.length !== form.images.length) {
          alert('Some files were not valid and have been removed. Please only upload PDFs and images.');
          form.images = validFiles;
        }
      }
    };
    
    // For image/file uploads with special handling
    const saveImages = () => {
      if (form.images && form.images.length > 0) {
        const formData = new FormData();
        form.images.forEach((file, index) => {
          formData.append(`files[${index}]`, file);
        });
        
        axios.post(`/work-orders/${props.workOrder.id}/update-images`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        .then(response => {
          if (response.data.success) {
            // Update work order with new images/files from response
            props.workOrder.images = response.data.images;
          }
          
          // Reset editing state
          editingField.value.images = false;
          form.images = [];
        })
        .catch(error => {
          console.error('Error uploading files:', error);
          if (error.response && error.response.data) {
            console.error('Server validation errors:', error.response.data);
          }
        });
      } else {
        editingField.value.images = false;
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

    return {
      getStatusClasses,
      // ... rest of existing return values ...
      users: ref([]),
      isLoading: ref(true),
      getUserName: (userId) => {
        // Existing getUserName function
      },
      getUserAvatar: (userId) => {
        // Existing getUserAvatar function
      },
      isEditing,
      editingField,
      form,
      formatDate: (date) => {
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
      },
      closeModal: () => emit('close'),
      startEditing: (field) => {
        Object.keys(editingField.value).forEach(key => {
          editingField.value[key] = false;
        });
        editingField.value[field] = true;
      },
      saveField: (field) => {
        if (field === 'images') {
          saveImages();
        } else {
          saveField(field);
        }
      },
      duplicateWorkOrder,
      handleImageUpload: handleFileUpload,
      isPdfFile,
      isImageFile,
      getFileName,
      previewImage,
      previewAttachment,
      closePreview,
      getAllAttachments
    };
  }
};
</script>

<style scoped>
/* Match the styling from the example */
.description {
  word-wrap: break-word;
  overflow-wrap: break-word;
  white-space: normal;
  max-width: 100%;
}

/* Add lime focus styles */
:focus {
  outline-color: theme('colors.lime.400');
}

/* Add styles for file type icons */
.file-icon {
  @apply flex-shrink-0 h-5 w-5;
}

.pdf-icon {
  @apply text-red-400;
}

.image-icon {
  @apply text-blue-400;
}

/* Preview modal animation */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

[v-if="previewImage"] {
  animation: fadeIn 0.2s ease-out;
}
</style>