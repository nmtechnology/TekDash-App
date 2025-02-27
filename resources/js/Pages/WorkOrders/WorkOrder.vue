<template>
  <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80">
     <!-- Semi-transparent overlay with blur -->
     <div class="absolute inset-0 bg-black/40 backdrop-blur-md"></div>
     <!-- Modal content with transparency -->
     <div class="relative bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-3xl mx-4 sm:mx-auto border border-gray-200 dark:border-gray-700">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
        <div>
          <h3 class="text-xl/7 font-semibold text-gray-900 dark:text-lime-400">{{ workOrder.title }}</h3>
          <p class="mt-1 max-w-2xl text-sm/6 text-gray-500 dark:text-gray-400">Work Order Details</p>
        </div>
        <button @click="closeModal" type="button" class="rounded-md bg-white dark:bg-gray-700 p-2">
          <svg class="h-5 w-5 text-gray-400 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <div class="px-6 py-4">
        <div v-if="workOrder">
          <!-- View mode -->
          <div>
            <div class="mt-2 border-t border-gray-100 dark:border-gray-700">
              <dl class="divide-y divide-gray-100 dark:divide-gray-700">
                <!-- Description -->
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white">Description</dt>
                  <dd class="mt-1 flex text-sm/6 text-gray-700 dark:text-lime-400 sm:col-span-2 sm:mt-0">
                    <span v-if="!editingField.description" class="grow overflow-y-auto max-h-40 description">{{ workOrder.description }}</span>
                    <textarea v-else v-model="form.description" rows="4" class="grow mt-1 block w-full rounded-md bg-white dark:bg-slate-800 border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 dark:focus:border-lime-400 dark:focus:ring-lime-400 dark:text-lime-400 sm:text-sm"></textarea>
                    <span class="ml-4 shrink-0">
                      <button v-if="!editingField.description" @click="startEditing('description')" type="button" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500">Edit</button>
                      <button v-else @click="saveField('description')" type="button" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500">Save</button>
                    </span>
                  </dd>
                </div>
                
                <!-- Scheduled For -->
                <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white">Scheduled For</dt>
                  <dd class="mt-1 flex text-sm/6 text-gray-700 dark:text-lime-400 sm:col-span-2 sm:mt-0">
                    <span v-if="!editingField.date_time" class="grow">{{ formatDate(workOrder.date_time) }}</span>
                    <input v-else type="datetime-local" v-model="form.date_time" class="grow mt-1 block w-full rounded-md bg-white dark:bg-slate-800 border-gray-300 dark:border-gray-700 shadow-sm focus:border-lime-600 focus:ring-lime-600 dark:focus:border-lime-400 dark:focus:ring-lime-400 dark:text-lime-400 sm:text-sm">
                    <span class="ml-4 shrink-0">
                      <button v-if="!editingField.date_time" @click="startEditing('date_time')" type="button" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500">Edit</button>
                      <button v-else @click="saveField('date_time')" type="button" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500">Save</button>
                    </span>
                  </dd>
                </div>
                
                <!-- Status -->
                <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white">Status</dt>
                  <dd class="mt-1 flex text-sm/6 text-gray-700 dark:text-lime-400 sm:col-span-2 sm:mt-0">
                    <span v-if="!editingField.status" class="grow">{{ workOrder.status }}</span>
                    <select v-else v-model="form.status" class="grow mt-1 block w-full rounded-md bg-white dark:bg-slate-800 border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 dark:focus:border-lime-400 dark:focus:ring-lime-400 dark:text-lime-400 sm:text-sm">
                      <option value="Scheduled">Scheduled</option>
                      <option value="In Progress">In Progress</option>
                      <option value="Part/Return">Part/Return</option>
                      <option value="Complete">Complete</option>
                      <option value="Cancelled">Cancelled</option>
                    </select>
                    <span class="ml-4 shrink-0">
                      <button v-if="!editingField.status" @click="startEditing('status')" type="button" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500">Edit</button>
                      <button v-else @click="saveField('status')" type="button" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500">Save</button>
                    </span>
                  </dd>
                </div>
                
                <!-- Price -->
                <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white">Price</dt>
                  <dd class="mt-1 flex text-sm/6 text-gray-700 dark:text-lime-400 sm:col-span-2 sm:mt-0">
                    <span v-if="!editingField.price" class="grow">${{ workOrder.price }}</span>
                    <input v-else type="number" v-model="form.price" class="grow mt-1 block w-full rounded-md bg-white dark:bg-slate-800 border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 dark:focus:border-lime-400 dark:focus:ring-lime-400 dark:text-lime-400 sm:text-sm">
                    <span class="ml-4 shrink-0">
                      <button v-if="!editingField.price" @click="startEditing('price')" type="button" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500">Edit</button>
                      <button v-else @click="saveField('price')" type="button" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500">Save</button>
                    </span>
                  </dd>
                </div>
                
                <!-- Customer -->
                <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white">Customer</dt>
                  <dd class="mt-1 flex text-sm/6 text-gray-700 dark:text-lime-400 sm:col-span-2 sm:mt-0">
                    <span v-if="!editingField.customer_id" class="grow">{{ workOrder.customer_id }}</span>
                    <select v-else v-model="form.customer_id" class="grow mt-1 block w-full rounded-md bg-white dark:bg-slate-800 border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 dark:focus:border-lime-400 dark:focus:ring-lime-400 dark:text-lime-400 sm:text-sm">
                      <option value="Advanced Project Solutions">Advanced Project Solutions</option>
                      <option value="Barrister Global Service Network">Barrister Global Service Network</option>
                      <option value="DarAlIslam">DarAlIslam</option>
                      <option value="Field Nation">Field Nation</option>
                      <option value="Navco">Navco</option>
                      <option value="NEW CUSTOMER">NEW CUSTOMER</option>
                      <option value="NuTech National">NuTech National</option>
                      <option value="Telaid">Telaid</option>
                    </select>
                    <span class="ml-4 shrink-0">
                      <button v-if="!editingField.customer_id" @click="startEditing('customer_id')" type="button" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500">Edit</button>
                      <button v-else @click="saveField('customer_id')" type="button" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500">Save</button>
                    </span>
                  </dd>
                </div>
                
                <!-- User (non-editable) -->
                <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white">User</dt>
                  <dd class="mt-1 text-sm/6 text-gray-700 dark:text-lime-400 sm:col-span-2 sm:mt-0">
                    {{ getUserName(workOrder.user_id) }}
                  </dd>
                </div>
                
                <!-- Attachments -->
                <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white">Attachments</dt>
                  <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">
                    <ul role="list" class="divide-y divide-gray-100 dark:divide-gray-700 rounded-md border border-gray-200 dark:border-gray-700">
                      <li v-for="(image, index) in workOrder.images" :key="index" class="flex items-center justify-between py-4 pl-4 pr-5 text-sm/6">
                        <div class="flex w-0 flex-1 items-center">
                          <PaperClipIcon class="size-5 shrink-0 text-gray-400" aria-hidden="true" />
                          <div class="ml-4 flex min-w-0 flex-1 gap-2">
                            <span class="truncate font-medium dark:text-lime-400">{{ image }}</span>
                            <span class="shrink-0 text-gray-400">Image</span>
                          </div>
                        </div>
                        <div class="ml-4 flex shrink-0 space-x-4">
                          <a :href="`/storage/${image}`" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500" download>Download</a>
                        </div>
                      </li>
                      <li v-if="!editingField.images && (!workOrder.images || workOrder.images.length === 0)" class="flex items-center justify-between py-4 pl-4 pr-5 text-sm/6">
                        <div class="text-gray-500 dark:text-gray-400">No attachments</div>
                        <span class="ml-4 shrink-0">
                          <button @click="startEditing('images')" type="button" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500">Add</button>
                        </span>
                      </li>
                      <li v-if="editingField.images" class="flex items-center justify-between py-4 pl-4 pr-5 text-sm/6">
                        <input type="file" id="images" multiple @change="handleImageUpload" class="grow mt-1 block w-full rounded-md bg-white dark:bg-slate-800 border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 dark:focus:border-lime-400 dark:focus:ring-lime-400 dark:text-lime-400 sm:text-sm">
                        <span class="ml-4 shrink-0">
                          <button @click="saveField('images')" type="button" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500">Save</button>
                        </span>
                      </li>
                    </ul>
                  </dd>
                </div>

                <!-- Notes -->
                <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white">Notes</dt>
                  <dd class="mt-1 sm:col-span-2 sm:mt-0 dark:text-lime-400">
                    <Messenger 
                      :initialNotes="workOrder.notes || []"
                      :workOrderId="workOrder.id"
                      :userId="$page.props.auth.user.id"
                      :getUserName="getUserName"
                      :getUserAvatar="getUserAvatar"
                      :currentUserAvatar="$page.props.auth.user.profile_photo_url"
                    />
                  </dd>
                </div>
              </dl>
            </div>
            
            <!-- Actions -->
            <div class="px-4 py-6 sm:px-0 flex flex-wrap justify-end gap-4">
              <button @click="duplicateWorkOrder" type="button" class="inline-flex items-center rounded-md bg-indigo-50 dark:bg-blue-900 px-3 py-2 text-sm font-semibold text-indigo-600 dark:text-blue-300 shadow-sm hover:bg-indigo-100">
                Duplicate
              </button>
              <button @click="closeModal" type="button" class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-900 px-3 py-2 text-sm font-semibold text-red-600 dark:text-red-300 shadow-sm hover:bg-red-100">
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch, onMounted } from 'vue';
import { format } from 'date-fns';
import { useForm } from '@inertiajs/vue3';
import { PaperClipIcon } from '@heroicons/vue/20/solid';
import Messenger from '@/Components/Messenger.vue';
import axios from 'axios';

export default {
  name: 'WorkOrder',
  components: {
    PaperClipIcon,
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
    const isEditing = ref(false);
    const editingField = ref({
      description: false,
      date_time: false,
      status: false,
      price: false,
      customer_id: false,
      images: false
      // Removed user_id from editable fields
    });

       // Define form first before using it in the watch function
       const form = useForm({
      customer_id: props.workOrder?.customer_id || '',
      title: props.workOrder?.title || '',
      description: props.workOrder?.description || '',
      date_time: props.workOrder?.date_time || '',
      status: props.workOrder?.status || 'Scheduled',
      price: props.workOrder?.price || 0,
      notes: props.workOrder?.notes || [],
      images: [],
      // Keep user_id in the form for data consistency but don't make it editable
      user_id: props.workOrder?.user_id || ''
    });

    // Watch for changes to the workOrder prop
    watch(() => props.workOrder, (newVal) => {
  if (newVal) {
    // Initialize form with the new work order data
    form.customer_id = newVal.customer_id || '';
    form.title = newVal.title || '';
    form.description = newVal.description || '';
    form.date_time = newVal.date_time || '';
    form.status = newVal.status || 'Scheduled';
    form.price = newVal.price || 0;
    form.notes = newVal.notes || [];
    form.user_id = newVal.user_id || '';
    
    // Log the data for debugging
    console.log('Work order data in modal:', newVal);
  }
}, { immediate: true });

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

    const startEditing = (field) => {
      // Reset all fields to not editing
      Object.keys(editingField.value).forEach(key => {
        editingField.value[key] = false;
      });
      // Set the current field to editing
      editingField.value[field] = true;
    };

    const saveField = (field) => {
      const data = {};
      data[field] = form[field];
      
      // For images, we need to use FormData
      if (field === 'images') {
        const formData = new FormData();
        if (form.images.length) {
          form.images.forEach((file, index) => {
            formData.append(`images[${index}]`, file);
          });
        }
        
        // Get CSRF token safely
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        
        // Save only the images
        fetch(`/work-orders/${props.workOrder.id}/update-images`, {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-TOKEN': csrfToken
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            editingField.value[field] = false;
            // Update the workOrder object with new images
            if (data.images) {
              props.workOrder.images = data.images;
            }
          } else {
            console.error('Error updating images:', data.error);
          }
        })
        .catch(error => {
          console.error('Error:', error);
        });
        
        return;
      }
          
      // For regular fields, use axios with only the changed field
      const fieldData = {};
      fieldData[field] = form[field];

      const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
      };
          
      // Use axios to update just this field
      axios.put(`/work-orders/${props.workOrder.id}/update-field`, fieldData, { headers })
        .then(response => {
          if (response.data.success) {
            // Update the local workOrder object
            props.workOrder[field] = form[field];
            // Exit editing mode for this field
            editingField.value[field] = false;
          }
        })
        .catch(error => {
          console.error('Error updating field:', error);
        });
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

    const users = ref([]);
    const isLoading = ref(true);
    
    // Enhanced getUserName function
    const getUserName = (userId) => {
  if (!userId) return 'Guest';
  
  // Parse userId to ensure it's treated as a number for comparison
  const numericUserId = parseInt(userId, 10);
  
  // Check if users are loaded
  if (users.value && users.value.length) {
    // Find the user in our users array
    const user = users.value.find(user => user.id === numericUserId);
    if (user && user.name) {
      return user.name;
    }
  }
  
  // If specific work order user, get from props
  if (props.workOrder && props.workOrder.user && props.workOrder.user.name) {
    return props.workOrder.user.name;
  }
  
  // Last resort fallback
  return `User ${userId}`;
};

    const getUserAvatar = (userId) => {
      if (!userId) return getDefaultAvatar('Guest');
      
      const user = users.value.find(user => user.id === parseInt(userId, 10));
      const userName = user ? user.name : `User ${userId}`;
      
      return getDefaultAvatar(userName);
    };
    
    const getDefaultAvatar = (name) => {
      return `https://ui-avatars.com/api/?name=${encodeURIComponent(name.replace(/\s+/g, '+'))}&color=7F9CF5&background=EBF4FF`;
    };

    const fetchUsers = async () => {
  try {
    isLoading.value = true;
    const response = await axios.get('/users-list');
    
    if (response.data && Array.isArray(response.data)) {
      users.value = response.data;
    }
  } catch (error) {
    console.error('Error fetching users:', error);
    // Fallback implementation as above
  } finally {
    isLoading.value = false;
  }
};

    onMounted(() => {
      fetchUsers();
    });

    const handleImageUpload = (event) => {
      const files = event.target.files;
      if (files.length) {
        form.images = Array.from(files);
      }
    };

    watch(() => props.showModal, (newVal) => {
      if (!newVal) {
        closeModal();
      }
    });

    return {
      users,
      isLoading,
      getUserName,
      getUserAvatar,
      isEditing,
      editingField,
      form,
      formatDate,
      closeModal,
      startEditing,
      saveField,
      updateWorkOrder,
      duplicateWorkOrder,
      handleImageUpload,
    };
  },
};
</script>

<style scoped>
.description {
  word-wrap: break-word;
  overflow-wrap: break-word;
  white-space: normal;
  max-width: 100%;
}
</style>