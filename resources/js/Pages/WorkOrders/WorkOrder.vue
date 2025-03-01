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
              
              <!-- Attachments section -->
              <div>
                <p class="text-sm text-gray-400">Attachments:</p>
                <ul role="list" class="mt-1 divide-y divide-gray-700 rounded-md border border-gray-700">
                  <li v-for="(image, index) in workOrder.images" :key="index" class="flex items-center justify-between py-2 pl-3 pr-4 text-sm">
                    <div class="flex w-0 flex-1 items-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                      </svg>
                      <span class="ml-2 flex-1 truncate text-white">{{ image }}</span>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                      <a :href="`/storage/${image}`" class="text-lime-400 hover:text-lime-300" download>Download</a>
                    </div>
                  </li>
                  <li v-if="!editingField.images && (!workOrder.images || workOrder.images.length === 0)" class="flex items-center justify-between py-2 pl-3 pr-4 text-sm">
                    <span class="text-gray-400">No attachments</span>
                    <button @click="startEditing('images')" class="text-lime-400 hover:text-lime-300">Add</button>
                  </li>
                  <li v-if="editingField.images" class="flex items-center justify-between py-2 pl-3 pr-4 text-sm">
                    <input type="file" id="images" multiple @change="handleImageUpload" class="text-white" />
                    <button @click="saveField('images')" class="text-lime-400 hover:text-lime-300">Save</button>
                  </li>
                </ul>
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
        <button @click="duplicateWorkOrder" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-lime-600 text-base font-medium text-white hover:bg-lime-700 sm:ml-3 sm:w-auto sm:text-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          Duplicate
        </button>
        <button @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-700 shadow-sm px-4 py-2 bg-gray-700 text-base font-medium text-gray-300 hover:bg-gray-600 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
    const saveImages = () => {
      if (form.images && form.images.length > 0) {
        const formData = new FormData();
        form.images.forEach((image, index) => {
          formData.append(`images[${index}]`, image);
        });
        
        // Use the correct route with hyphen: "work-orders" instead of "workorders"
        axios.post(`/work-orders/${props.workOrder.id}/update-images`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        .then(response => {
          if (response.data.success) {
            // Update work order with new images from response
            props.workOrder.images = response.data.images;
          }
          
          // Reset editing state
          editingField.value.images = false;
          form.images = [];
        })
        .catch(error => {
          console.error('Error uploading images:', error);
          if (error.response && error.response.data) {
            console.error('Server validation errors:', error.response.data);
          }
        });
      } else {
        editingField.value.images = false;
      }
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
      duplicateWorkOrder: () => {
        // Existing duplicateWorkOrder function
      },
      handleImageUpload: (event) => {
        const files = event.target.files;
        if (files.length) {
          form.images = Array.from(files);
        }
      }
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
</style>