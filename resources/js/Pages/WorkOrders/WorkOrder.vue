<template>
  
  <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80">
     <!-- Semi-transparent overlay with blur -->
     <div class="absolute inset-0 bg-black/40 backdrop-blur-md"></div>
     <!-- Modal content with transparency -->
     <div class="relative bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-3xl mx-4 sm:mx-auto border border-gray-200 dark:border-gray-700">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
        <div class="flex items-center gap-3">
          <!-- Title with status indicator -->
          <div class="flex items-center">
            <h3 class="text-xl/7 font-semibold text-gray-900 dark:text-lime-400">
              <span v-if="!editingField.title">{{ workOrder.title }}</span>
              <input 
                v-else 
                type="text" 
                v-model="form.title" 
                class="w-full rounded-md bg-white dark:bg-slate-800 border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 dark:focus:border-lime-400 dark:focus:ring-lime-400 dark:text-lime-400 text-xl font-semibold"
              />
            </h3>
            <span class="ml-2" v-if="!editingField.title">
              <button @click="startEditing('title')" type="button" class="rounded-md bg-white dark:bg-transparent p-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-indigo-600 dark:text-lime-400">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
              </button>
            </span>
            <span class="ml-2" v-else>
              <button @click="saveField('title')" type="button" class="rounded-md bg-white dark:bg-transparent p-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-green-600 dark:text-lime-400">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
              </button>
            </span>
            
            <!-- Status badge -->
            <span 
              class="ml-3 inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
              :class="getStatusClasses(workOrder.status)"
            >
              {{ workOrder.status }}
            </span>
          </div>
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
                <!-- Customer -->
                <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500 dark:text-lime-400" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                    </svg>
                    Customer
                  </dt>
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
                <!-- Description -->
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500 dark:text-lime-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                    </svg>
                    Description
                  </dt>
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
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500 dark:text-lime-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                    </svg>
                    Scheduled For
                  </dt>
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
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500 dark:text-lime-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                    Status
                  </dt>
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
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500 dark:text-lime-400" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                    </svg>
                    Price
                  </dt>
                  <dd class="mt-1 flex text-sm/6 text-gray-700 dark:text-lime-400 sm:col-span-2 sm:mt-0">
                    <span v-if="!editingField.price" class="grow">${{ workOrder.price }}</span>
                    <input v-else type="number" v-model="form.price" class="grow mt-1 block w-full rounded-md bg-white dark:bg-slate-800 border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 dark:focus:border-lime-400 dark:focus:ring-lime-400 dark:text-lime-400 sm:text-sm">
                    <span class="ml-4 shrink-0">
                      <button v-if="!editingField.price" @click="startEditing('price')" type="button" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500">Edit</button>
                      <button v-else @click="saveField('price')" type="button" class="rounded-md bg-white dark:bg-transparent font-medium text-indigo-600 dark:text-lime-400 hover:text-indigo-500">Save</button>
                    </span>
                  </dd>
                </div>
                
                <!-- User (non-editable) -->
                <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500 dark:text-lime-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    User
                  </dt>
                  <dd class="mt-1 text-sm/6 text-gray-700 dark:text-lime-400 sm:col-span-2 sm:mt-0">
                    {{ getUserName(workOrder.user_id) }}
                  </dd>
                </div>
                
                <!-- Attachments -->
                <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500 dark:text-lime-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 102 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                    </svg>
                    Attachments
                  </dt>
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
                  <dt class="text-sm/6 font-medium text-gray-900 dark:text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500 dark:text-lime-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd" />
                    </svg>
                    Notes
                  </dt>
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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Duplicate
              </button>
              <button @click="closeModal" type="button" class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-900 px-3 py-2 text-sm font-semibold text-red-600 dark:text-red-300 shadow-sm hover:bg-red-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
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
      title: false,     // Add title to editable fields
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
  // Special handling for images
  if (field === 'images') {
    if (form.images && form.images.length > 0) {
      // Create FormData for file uploads
      const formData = new FormData();
      
      // Add each file to the FormData
      form.images.forEach((file, index) => {
        formData.append(`images[${index}]`, file);
      });
      
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
    }
    return;
  }
      
  // For regular fields, use axios with only the changed field
  const fieldData = {};
  fieldData[field] = form[field];

  // Get CSRF token safely
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

  // Use POST instead of PUT since your backend may not be configured to accept PUT requests
  axios.post(`/work-orders/${props.workOrder.id}/update-field`, fieldData, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    }
  })
    .then(response => {
      if (response.data && response.data.success) {
        // Update the local workOrder object
        props.workOrder[field] = form[field];
        // Exit editing mode for this field
        editingField.value[field] = false;
      } else {
        console.error('Error updating field:', response.data?.error || 'Unknown error');
      }
    })
    .catch(error => {
      console.error('Error updating field:', error);
      
      // More detailed error logging to help troubleshoot
      if (error.response) {
        console.error('Response status:', error.response.status);
        console.error('Response data:', error.response.data);
      }
    });
};  // Make sure this semicolon is here

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

     // Add this new function to get status styling classes
     const getStatusClasses = (status) => {
      if (!status) return {
        'bg-gray-100': true,
        'text-gray-600': true,
        'ring-gray-500/10': true
      };
      
      switch (status.toLowerCase()) {
        case 'scheduled':
          return {
            'bg-blue-50': true, 
            'text-blue-700': true, 
            'ring-blue-600/20': true,
            'dark:bg-blue-900/30': true,
            'dark:text-blue-300': true,
            'dark:ring-blue-500/20': true
          };
        case 'in progress':
          return {
            'bg-yellow-50': true, 
            'text-yellow-800': true, 
            'ring-yellow-600/20': true,
            'dark:bg-yellow-900/30': true,
            'dark:text-yellow-300': true,
            'dark:ring-yellow-500/20': true
          };
        case 'part/return':
          return {
            'bg-purple-50': true, 
            'text-purple-700': true, 
            'ring-purple-700/10': true,
            'dark:bg-purple-900/30': true,
            'dark:text-purple-300': true,
            'dark:ring-purple-500/20': true
          };
        case 'complete':
          return {
            'bg-green-50': true, 
            'text-green-700': true, 
            'ring-green-600/20': true,
            'dark:bg-green-900/30': true,
            'dark:text-green-300': true,
            'dark:ring-green-500/20': true
          };
        case 'cancelled':
          return {
            'bg-red-50': true, 
            'text-red-700': true, 
            'ring-red-600/10': true,
            'dark:bg-red-900/30': true,
            'dark:text-red-300': true,
            'dark:ring-red-500/20': true
          };
        default:
          return {
            'bg-gray-50': true, 
            'text-gray-600': true, 
            'ring-gray-500/10': true,
            'dark:bg-gray-800/50': true,
            'dark:text-gray-300': true,
            'dark:ring-gray-500/20': true
          };
      }
    };

    return {
      getStatusClasses,
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
      handleImageUpload
    };
  }
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