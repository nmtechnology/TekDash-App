<template>
  <div v-if="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center">
    <!-- Semi-transparent overlay with blur -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    
    <!-- Modal container using the provided styling -->
    <div class="inline-block align-bottom bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl mx-4 sm:mx-auto border border-gray-700 relative z-[10000] mt-16 max-h-[80vh]">
      <!-- Modal header -->
      <div class="bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-700 overflow-auto max-h-[70vh]">
        <div class="sm:flex sm:items-start">
          <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
            <!-- Header with close button -->
            <div class="flex justify-between items-center mb-4">
              <div class="flex items-center space-x-3 flex-grow">
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
                    class="w-full rounded-md bg-lime-400 border-lime-400 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 dark:focus:border-lime-400 dark:focus:ring-lime-400 text-white text-lg font-medium"
                  />
                </h3>
                
                <!-- Network Status Indicator -->
                <NetworkStatusIndicator :workOrderId="workOrder.id" />
              </div>
              
              <div class="flex space-x-2">
                <button v-if="!editingField.title" @click="startEditing('title')" class="text-lime-400 hover:text-lime-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                </button>
                <button v-else @click="saveField('title')" class="text-green-400 hover:text-green-300">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </button>
                <button @click="closeModal" class="text-red-500 outline-dotted hover:text-gray-900 hover:bg-red-500">
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
                  <p class="text-sm text-gray-400">Date(s):</p>
                  <div class="flex items-center">
                    <div v-if="!editingField.date_time" class="text-green-400">
                      <span v-if="workOrder.visit_dates && workOrder.visit_dates.length">
                        {{ formatMultipleDates(workOrder.visit_dates) }}
                      </span>
                      <span v-else>
                        {{ formatDate(workOrder.date_time) }}
                      </span>
                    </div>
                    <div v-else class="flex flex-col space-y-3 w-full">
                      <!-- Date selection type toggle -->
                      <div class="flex space-x-4 text-sm">
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
                      <div v-if="dateSelectionType === 'single'" class="flex items-center">
                        <input 
                          type="datetime-local" 
                          v-model="form.date_time" 
                          class="mt-1 w-full rounded-md bg-gray-800 border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm" 
                        />
                      </div>

                      <!-- Date range selection -->
                      <div v-if="dateSelectionType === 'range'" class="space-y-2">
                        <div class="flex items-center">
                          <span class="text-xs text-gray-400 mr-2 w-14">Start:</span>
                          <input 
                            type="datetime-local" 
                            v-model="form.date_time" 
                            class="mt-1 w-full rounded-md bg-gray-800 border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm" 
                          />
                        </div>
                        <div class="flex items-center">
                          <span class="text-xs text-gray-400 mr-2 w-14">End:</span>
                          <input 
                            type="datetime-local" 
                            v-model="form.end_date" 
                            class="mt-1 w-full rounded-md bg-gray-800 border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm" 
                          />
                        </div>
                      </div>

                      <!-- Multiple date selection -->
                      <div v-if="dateSelectionType === 'multiple'" class="space-y-2">
                        <div v-for="(date, index) in selectedDates" :key="index" class="flex items-center">
                          <input 
                            type="datetime-local" 
                            v-model="selectedDates[index]" 
                            class="mt-1 flex-grow rounded-md bg-gray-800 border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm" 
                          />
                          <button 
                            @click="removeDate(index)" 
                            class="ml-2 text-red-400 hover:text-red-300"
                            type="button"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                          </button>
                        </div>
                        <button 
                          @click="addNewDate" 
                          class="text-sm text-lime-400 hover:text-lime-300 flex items-center"
                          type="button"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                          </svg>
                          Add Another Date
                        </button>
                      </div>
                    </div>
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
              
              <!-- Description section - Updated to be smaller and scrollable -->
              <div>
                <p class="text-sm text-gray-400">Description:</p>
                <div class="flex">
                  <div class="flex-grow">
                    <div v-if="!editingField.description" 
                      class="whitespace-pre-line mt-1 text-sm bg-gray-800 p-3 rounded-md text-lime-400 overflow-y-auto max-h-[120px] scrollbar-thin scrollbar-thumb-gray-600 scrollbar-track-gray-800 touch-auto">
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
                <div class="flex items-center">
                  <span v-if="!editingField.user_id" class="text-white">
                  {{ getUserName(workOrder.user_id) || 'Unassigned' }}
                  </span>
                  <select 
                  v-else 
                  v-model="form.user_id" 
                  class="mt-1 block w-full rounded-md bg-gray-800 border-gray-700 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm"
                  >
                  <option value="">Unassigned</option>
                  <option v-for="user in $page.props.users" :key="user.id" :value="user.id">
                    {{ user.name }}
                  </option>
                  </select>
                  <span class="ml-2">
                  <button v-if="!editingField.user_id" @click="startEditing('user_id')" class="text-lime-400 hover:text-lime-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                  </button>
                  <button v-else @click="saveField('user_id')" class="text-green-400 hover:text-green-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </button>
                  </span>
                </div>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2z" />
                      </svg>
                      <span class="ml-2 flex-1 truncate text-white">{{ getFileName(attachment) }}</span>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex space-x-2">
                      <!-- Preview button for images and PDFs -->
                      <button 
                        v-if="isImageFile(attachment) || isPdfFile(attachment)" 
                        @click="handlePreviewAttachment(attachment)" 
                        class="text-lime-400 btn outline rounded p-1 hover:text-gray-900 hover:bg-lime-400"
                      >
                        View
                      </button>
                      <a :href="`/storage/${attachment}`" class="text-yellow-500 text-sm hover:bg-yellow-500 outline btn rounded p-1 hover:text-gray-900" download>Download</a>
                      <!-- Add delete button -->
                      <button 
                        @click="deleteAttachment(attachment)" 
                        class="text-red-500 hover:bg-red-400 hover:text-gray-900 btn rounded p-1 outline"
                      >
                        Delete
                      </button>
                    </div>
                  </li>
                  <li v-if="!editingField.images && getAllAttachments().length === 0" class="flex items-center justify-between py-2 pl-3 pr-4 text-sm">
                    <span class="text-gray-400">No attachments</span>
                    <button @click="startEditing('images')" class="text-lime-400 btn outline rounded hover:text-lime-300">Add</button>
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
                    <button @click="saveField('images')" class="text-lime-400 hover:text-lime-500">Save</button>
                  </li>
                </ul>
              </div>

              <!-- Image preview modal -->
              <div v-if="previewAttachment" class="fixed inset-0 z-[10001] flex items-center justify-center">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="closePreview"></div>
                <div class="relative max-w-4xl max-h-[90vh] overflow-auto bg-gray-900 rounded-lg p-1 z-[10002]">
                  <!-- Image preview -->
                  <img 
                    v-if="isImageFile(previewAttachment)" 
                    :src="`/storage/${previewAttachment}`" 
                    class="max-w-full max-h-full object-contain" 
                  />
                  
                  <!-- PDF preview -->
                  <PdfViewer 
                    v-else-if="isPdfFile(previewAttachment)"
                    :pdfUrl="previewAttachment"
                    :title="getFileName(previewAttachment)"
                  />
                  
                  <!-- Close button -->
                  <button 
                    @click="closePreview" 
                    class="absolute top-2 right-2 bg-gray-800 rounded-full p-1 text-gray-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-lime-400 z-10"
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
        <!-- Add this new invoice button -->
        <button 
          @click="createInvoice" 
          :disabled="workOrder.status !== 'Complete'"
          :class="{'opacity-50 cursor-not-allowed': workOrder.status !== 'Complete'}"
          class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 outline text-blue-400 hover:text-gray-900 hover:bg-blue-400 font-medium sm:ml-3 sm:w-auto sm:text-sm"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          Create Invoice
        </button>
        <!-- Updated duplicate button with disabled state when status is Complete -->
        <button 
          @click="duplicateWorkOrder" 
          :disabled="workOrder.status === 'Complete'"
          :class="{'opacity-50 cursor-not-allowed': workOrder.status === 'Complete'}"
          class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 outline text-green-400 font-medium hover:bg-green-400 hover:text-gray-900 sm:ml-3 sm:w-auto sm:text-sm"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          Duplicate
        </button>
        <button @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-700 shadow-sm px-4 py-2 outline text-red-400 font-medium hover:bg-red-400 hover:text-gray-900 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch, onMounted, computed } from 'vue';
import { format, parseISO } from 'date-fns';
import { useForm } from '@inertiajs/vue3';
import Messenger from '@/Components/Messenger.vue';
import PdfViewer from '@/Components/PdfViewer.vue';
import NetworkStatusIndicator from '@/Components/NetworkStatusIndicator.vue'; // Import the NetworkStatusIndicator
import axios from 'axios';
import { router } from '@inertiajs/vue3'; // Import router for navigation

export default {
  name: 'WorkOrder',
  components: {
    Messenger,
    PdfViewer, // Add the PDF viewer component
    NetworkStatusIndicator, // Register the component
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
  methods: {
    updateWorkOrder() {
        // Check authentication before making request
        if (!this.$page.props.auth.user) {
            // Redirect to login if not authenticated
            window.location.href = route('login');
            return;
        }
        
        axios.put(`/api/work-orders/${this.workOrder.id}`, this.workOrder)
            .then(response => {
                // Success handling
            })
            .catch(error => {
                console.error('Error updating work order:', error);
                if (error.response && error.response.status === 401) {
                    // Handle auth error - maybe redirect to login
                    window.location.href = route('login');
                } else {
                    console.error('Server validation errors:', error.response?.data);
                }
            });
    }
},
  setup(props, { emit }) {
    // Configure Axios - Add this at the top of setup()
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    axios.defaults.withCredentials = true;
    axios.defaults.headers.common = {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrf,
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };

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
      end_date: props.workOrder?.end_date || '',
      visit_dates: props.workOrder?.visit_dates || [],
      status: props.workOrder?.status || 'Scheduled',
      price: props.workOrder?.price || 0,
      notes: props.workOrder?.notes || [],
      images: [],
      user_id: props.workOrder?.user_id || ''
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

    // Save field with appropriate date structure based on selection type
    const saveField = async (field) => {
      if (field === 'images') {
        return saveImages();
      }

      let data = {};
      // Special handling for date fields
      if (field === 'date_time') {
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
      } 
      else if (field === 'price') {
        // Ensure price is a number
        data[field] = Number(form[field]);
      } 
      else {
        // Standard field
        data[field] = form[field];
      }
      
      // Log what we're sending for debugging
      console.log(`Updating ${field} with:`, data);
      
      try {
        // Use web route instead of API route
        const response = await axios.post(`/work-orders/${props.workOrder.id}/update-field`, data);
        
        if (response.data.success) {
          Object.keys(data).forEach(key => {
            props.workOrder[key] = data[key];
          });
          editingField.value[field] = false;
        } else {
          throw new Error(response.data.message || 'Update failed');
        }
      } catch (error) {
        console.error('Error updating work order:', error);
        if (error.response?.status === 401) {
          window.location.href = '/login';
        } else {
          alert(error.response?.data?.message || 'Failed to update field');
        }
      }
    };

    // Set axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;  // Important for maintaining session cookies

// Get CSRF token from the meta tag
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

    // Generate array of dates between start and end for range selection
    const generateDateRange = (start, end) => {
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
    
    // ... rest of existing functions ...

    const duplicateWorkOrder = async (event) => {
      const button = event.target.closest('button');
      const originalText = button.innerHTML;
      
      try {
        button.disabled = true;
        button.innerHTML = 'Duplicating...';
        
        // Add the leading slash to ensure it's from the root path
        const response = await axios.post(`/work-orders/${props.workOrder.id}/duplicate`, {}, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf
          }
        });
        
        if (response.data.success) {
          window.location.reload();
        } else {
          throw new Error(response.data.message || 'Failed to duplicate work order');
        }
      } catch (error) {
        console.error('Error duplicating work order:', error);
        alert(error.response?.data?.message || 'Failed to duplicate work order');
      } finally {
        button.disabled = false;
        button.innerHTML = originalText;
      }
    };

    // Add state for image preview
    const previewAttachment = ref(null);

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
    const handlePreviewAttachment = (attachment) => {
      if (isImageFile(attachment) || isPdfFile(attachment)) {
        previewAttachment.value = attachment;
      }
    };
    
    // Close preview
    const closePreview = () => {
      previewAttachment.value = null;
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
          editingField.value.images = false;
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

    const createInvoice = async (event) => {
      // Define variables outside try block so they're accessible in finally
      const button = event.target.closest('button');
      const originalText = button.innerHTML;
      
      try {
        // Show loading state
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Creating Invoice...';
        
        // Get CSRF token from meta tag
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Use web route instead of API route for better Laravel session handling
        // Add explicit status parameter to ensure it's properly quoted in SQL
        const response = await axios.post(`/work-orders/${props.workOrder.id}/invoice`, {
          archive: true, // Explicitly request archiving
          status: 'Archived' // Explicitly set status as a string
        }, {
          headers: {
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          withCredentials: true
        });
    
        if (response.data.success) {
          // Close current modal first
          emit('close');
          
          // Show success message
          const invoiceId = response.data.invoice_id || '';
          
          // Emit event to show the archived work order modal with this work order
          emit('invoice-created', {
            workOrderId: props.workOrder.id,
            invoiceId: invoiceId,
            message: 'Invoice created successfully in QuickBooks! The work order has been archived.'
          });
          
          // Optionally refresh the main work order list to reflect the change
          router.reload({ only: ['workOrders'] });
        } else {
          throw new Error(response.data.message || 'Failed to create invoice');
        }
      } catch (error) {
        console.error('Error creating invoice:', error);
        
        // Detailed error logging
        if (error.response) {
          console.error('Response data:', error.response.data);
          console.error('Response status:', error.response.status);
          console.error('Response headers:', error.response.headers); // Log response headers
        } else if (error.request) {
          console.error('No response received:', error.request);
        } else {
          console.error('Error setting up request:', error.message);
        }
        
        // Handle auth errors
        if (error.response?.status === 401) {
          window.location.href = '/login';
        } else {
          alert(`Failed to create invoice: ${error.response?.data?.message || error.message}`);
        }
      } finally {
        // Reset button state - originalText now in scope
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
        const response = await axios.delete(`/work-orders/${props.workOrder.id}/delete-attachment`, {
          data: { attachment_path: attachmentPath }
        });
        
        if (response.data.success) {
          // Update local state to remove the attachment
          if (props.workOrder.images && Array.isArray(props.workOrder.images)) {
            props.workOrder.images = props.workOrder.images.filter(img => img !== attachmentPath);
          }
          
          if (props.workOrder.file_attachments && Array.isArray(props.workOrder.file_attachments)) {
            props.workOrder.file_attachments = props.workOrder.file_attachments.filter(file => file !== attachmentPath);
          }
        } else {
          throw new Error(response.data.message || 'Failed to delete attachment');
        }
      } catch (error) {
        console.error('Error deleting attachment:', error);
        alert(error.response?.data?.message || 'Failed to delete attachment');
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
      formatDate, // Make sure formatDate is included in the returned object
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
      previewAttachment,
      handlePreviewAttachment,
      closePreview,
      getAllAttachments,
      formatMultipleDates,
      formatDateShort, // Also export this helper function
      dateSelectionType,
      selectedDates,
      addNewDate,
      removeDate,
      createInvoice,
      deleteAttachment
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

[v-if="previewAttachment"] {
  animation: fadeIn 0.2s ease-out;
}

/* Preview container sizing */
.max-w-4xl {
  max-width: 84vw; /* Adjust based on your needs */
}

/* Custom scrollbar for webkit browsers */
.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
}

.scrollbar-thumb-gray-600::-webkit-scrollbar-thumb {
  background-color: #4B5563;
  border-radius: 3px;
}

.scrollbar-track-gray-800::-webkit-scrollbar-track {
  background-color: #1F2937;
}

/* For Firefox */
.scrollbar-thin {
  scrollbar-width: thin;
  scrollbar-color: #4B5563 #1F2937;
}

/* Ensure scrolling works well on touch devices */
.touch-auto {
  -webkit-overflow-scrolling: touch;
}
</style>