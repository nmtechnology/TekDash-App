<template>
  <div v-if="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center modal-animation">
    <!-- Semi-transparent overlay with enhanced blur -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md"></div>
    
    <!-- Modal container with fixed header and scrollable content -->
    <div class="mt-10 glossy-card flex flex-col w-full max-w-4xl mx-4 sm:mx-auto rounded-lg text-left shadow-xl transform transition-all relative z-[10000] max-h-[80vh]">
      <!-- Fixed header -->
      <div class="glossy-header sticky top-0 z-30 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-700/50">
        <div class="flex justify-between items-center">
          <div class="flex items-center space-x-3 flex-grow">
            <h3 class="text-xl2 font-medium text-lime-400" id="modal-title">
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
                class="w-full rounded-md bg-gray-900 border-lime-400 shadow-sm focus:border-lime-600 focus:ring-lime-600 dark:focus:border-lime-400 dark:focus:ring-lime-400 text-white text-lg font-medium"
              />
            </h3>
            
            <!-- Network Status Indicator -->
            <NetworkStatusIndicator :workOrderId="workOrder.id" class="ml-4"/>
          </div>
          
          <div class="flex space-x-2">
            <button v-if="!editingField.title" @click="startEditing('title')" class="glossy-icon-btn btn text-lime-400 hover:text-lime-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
              </svg>
            </button>
            <button v-else @click="saveField('title')" class="text-green-400 btn hover:text-green-300 glossy-icon-btn">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </button>
            <button @click="closeModal" class="text-red-500 btn hover:text-black hover:bg-red-500 glossy-close-btn">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
          </div>
        </div>
      </div>
      
      <!-- Scrollable content -->
      <div class="flex-1 overflow-y-auto p-4">
        <div class="text-gray-300 space-y-4">
          <!-- Work order details -->
          <div class="text-gray-300 space-y-4">
            <!-- Customer section -->
            <div class="flex justify-between items-start glossy-section">
              <div class="w-full">
                <p class="text-sm text-lime-400">Customer:</p>
                <div class="flex items-center">
                  <span v-if="!editingField.customer_id" class="text-white">{{ workOrder.customer_id }}</span>
                  <select v-else v-model="form.customer_id" class="mt-1 block w-full rounded-md bg-gray-900 border-gray-800 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm">
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
                    <button v-if="!editingField.customer_id" @click="startEditing('customer_id')" class="text-lime-400 btn hover:text-lime-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                      </svg>
                    </button>
                    <button v-else @click="saveField('customer_id')" class="text-green-400 btn hover:text-green-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                  </span>
                </div>
              </div>
              
              <div>
                <p class="text-sm text-lime-400">Price:</p>
                <div class="flex items-center">
                  <span v-if="!editingField.price" class="text-white">${{ workOrder.price }}</span>
                  <input v-else type="number" v-model="form.price" class="mt-1 w-24 rounded-md bg-gray-900 border-gray-800 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm" />
                  <span class="ml-2">
                    <button v-if="!editingField.price" @click="startEditing('price')" class="text-lime-400 btn hover:text-lime-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                      </svg>
                    </button>
                    <button v-else @click="saveField('price')" class="text-green-400 btn hover:text-green-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                  </span>
                </div>
              </div>
            </div>
            
            <!-- Status section -->
            <div class="flex justify-between items-start glossy-section">
              <div class="w-full">
                <p class="text-sm text-lime-400">Status:</p>
                <div class="flex items-center">
                  <span v-if="!editingField.status" :class="getStatusClasses(workOrder.status)">
                    {{ workOrder.status }}
                  </span>
                  <select v-else v-model="form.status" class="mt-1 block w-full rounded-md bg-gray-900 border-gray-400 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm">
                    <option v-for="status in VALID_STATUSES" :key="status" :value="status">
                      {{ status }}
                    </option>
                  </select>
                  <span class="ml-2">
                    <button v-if="!editingField.status" @click="startEditing('status')" class="text-lime-400 btn hover:text-lime-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                      </svg>
                    </button>
                    <button v-else @click="saveField('status')" class="text-green-400 btn hover:text-green-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                  </span>
                </div>
              </div>
              
              <div>
                <p class="text-sm text-lime-400">Date(s):</p>
                <div class="flex items-center">
                  <div v-if="!editingField.date_time" class="text-white">
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
                        class="mt-1 w-full rounded-md bg-gray-900 border-gray-800 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm" 
                      />
                    </div>

                    <!-- Date range selection -->
                    <div v-if="dateSelectionType === 'range'" class="space-y-2">
                      <div class="flex items-center">
                        <span class="text-xs text-gray-400 mr-2 w-14">Start:</span>
                        <input 
                          type="datetime-local" 
                          v-model="form.date_time" 
                          class="mt-1 w-full rounded-md bg-gray-900 border-gray-800 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm" 
                        />
                      </div>
                      <div class="flex items-center">
                        <span class="text-xs text-gray-400 mr-2 w-14">End:</span>
                        <input 
                          type="datetime-local" 
                          v-model="form.end_date" 
                          class="mt-1 w-full rounded-md bg-gray-900 border-gray-800 shadow-sm focus:border-lime-600 focus:ring-indigo-600 text-white sm:text-sm" 
                        />
                      </div>
                    </div>

                    <!-- Multiple date selection -->
                    <div v-if="dateSelectionType === 'multiple'" class="space-y-2">
                      <div v-for="(date, index) in selectedDates" :key="index" class="flex items-center">
                        <input 
                          type="datetime-local" 
                          v-model="selectedDates[index]" 
                          class="mt-1 flex-grow rounded-md bg-gray-900 border-gray-800 shadow-sm focus:border-lime-600 focus:ring-indigo-600 text-white sm:text-sm" 
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
                    <button v-if="!editingField.date_time" @click="startEditing('date_time')" class="text-lime-400 btn hover:text-lime-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                      </svg>
                    </button>
                    <button v-else @click="saveField('date_time')" class="text-green-400 btn hover:text-green-300">
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
              <p class="text-sm text-lime-400">Description:</p>
              <div class="flex">
                <div class="flex-grow">
                  <div v-if="!editingField.description" 
                    class="whitespace-pre-line mt-1 text-sm bg-gray-900 p-3 rounded-md text-white overflow-y-auto max-h-[120px] scrollbar-thin scrollbar-thumb-gray-600 scrollbar-track-gray-900 touch-auto">
                    {{ workOrder.description }}
                  </div>
                  <textarea v-else v-model="form.description" rows="4" class="mt-1 block w-full rounded-md bg-gray-900 border-gray-800 shadow-sm focus:ring-lime-600 text-white sm:text-sm"></textarea>
                </div>
                <span class="ml-2 flex-shrink-0">
                  <button v-if="!editingField.description" @click="startEditing('description')" class="text-lime-400 btn hover:text-lime-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                  </button>
                  <button v-else @click="saveField('description')" class="text-green-400 btn hover:text-green-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </button>
                </span>
              </div>
            </div>
            
            <!-- User section -->
            <div>
              <p class="text-sm text-lime-400">Assigned to:</p>
              <div class="flex items-center">
                <span v-if="!editingField.user_name" class="text-white">
                  {{ getUserName(workOrder.user_name) }}
                </span>
                <select 
                  v-else 
                  v-model="form.user_name" 
                  class="mt-1 block w-full rounded-md bg-gray-900 border-gray-800 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm"
                >
                  <option v-for="user in $page.props.users" :key="user.name" :value="user.name">
                    {{ user.name }}
                  </option>
                </select>
                <span class="ml-2">
                  <button v-if="!editingField.user_id" @click="startEditing('user_id')" class="text-lime-400 btn hover:text-lime-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                  </button>
                  <button v-else @click="saveField('user_id')" class="text-green-400 btn hover:text-green-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </button>
                </span>
              </div>
            </div>
            
            <!-- Attachments section - Updated with persistent upload button -->
            <div class="glossy-section">
              <div class="flex justify-between items-center">
                <p class="text-sm text-gray-400">Attachments:</p>
                <button 
                  @click="startEditing('images')" 
                  class="text-lime-400 btn mb-2 p-1 hover:text-gray-900 hover:bg-lime-400 text-sm flex items-center"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                  </svg>
                  Upload
                </button>
              </div>
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
                      v-if="isImageFile(attachment) || isPdfFile(attachment) || isDocumentFile(attachment)" 
                      @click="handlePreviewAttachment(attachment)" 
                      class="text-lime-400 btn outline rounded p-1 hover:text-gray-900 hover:bg-lime-400"
                    >
                      View
                    </button>
                    <a :href="`/storage/${attachment}`" class="text-yellow-500 text-sm hover:bg-yellow-500 outline btn rounded p-1 hover:text-gray-900" download>Download</a>
                    <!-- Add delete button -->
                    <button 
                      v-if="$page.props.auth.user && $page.props.auth.user.role !== 'guest'"
                      @click="deleteAttachment(attachment)" 
                      class="text-red-500 hover:bg-red-400 hover:text-gray-900 btn rounded p-1 outline"
                    >
                      Delete
                    </button>
                  </div>
                </li>
                <li v-if="!editingField.images && getAllAttachments().length === 0" class="flex items-center justify-between py-2 pl-3 pr-4 text-sm">
                  <span class="text-gray-400">No attachments</span>
                </li>
                <li v-if="editingField.images" class="flex items-center justify-between py-2 pl-3 pr-4 text-sm">
                  <input 
                    type="file" 
                    id="images" 
                    multiple 
                    @change="handleImageUpload" 
                    class="text-white" 
                    accept=".jpg,.jpeg,.png,.gif,.pdf,.heic,.docx"
                  />
                  <!-- Update the supported files text -->
                  <div class="text-xs text-gray-400 ml-2">
                    Supports: PDF, JPG, JPEG, PNG, GIF, HEIC, DOCX
                  </div>
                  <button 
                    @click="saveField('images')" 
                    class="text-white btn hover:text-lime-500"
                    :disabled="isUploading"
                  >
                    <span v-if="isUploading">Uploading...</span>
                    <span v-else>Save</span>
                  </button>
                </li>
                <!-- Add upload progress indicator -->
                <li v-if="isUploading && uploadProgress > 0" class="flex items-center justify-between py-2 pl-3 pr-4 text-sm">
                  <div class="w-3/4 mx-auto">
                    <div class="bg-gray-700 rounded-full h-2.5 dark:bg-gray-700 w-full">
                      <div class="bg-lime-500 h-2.5 rounded-full" :style="{ width: uploadProgress + '%' }"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 text-center">{{ uploadProgress }}% complete</p>
                  </div>
                </li>
                <!-- Display upload errors if any -->
                <li v-if="uploadError" class="flex items-center py-2 pl-3 pr-4 text-sm text-red-400 bg-red-900/20 border border-red-800">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>{{ uploadError }}</span>
                </li>
              </ul>
            </div>

            <!-- Image preview modal -->
            <div v-if="previewAttachment" class="fixed inset-0 z-[10001] flex items-center justify-center">
              <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="closePreview"></div>
              <div class="relative max-w-4xl max-h-[90vh] overflow-auto glossy-preview-card bg-gray-900 rounded-lg p-1 z-[10002]">
                <!-- Image preview -->
                <img 
                  v-if="isImageFile(previewAttachment)" 
                  :src="`/storage/${previewAttachment}`" 
                  class="max-w-full max-h-full object-contain" 
                />
                
                <!-- PDF preview -->
                <PdfViewer 
                  v-if="previewAttachment && isPdfFile(previewAttachment)"
                  :pdf-url="previewAttachment"
                  :title="getFileName(previewAttachment)"
                  :redirect-after-upload="true"
                  :work-order-id="workOrder.id"
                  @document-uploaded="handleDocumentUpload"
                  @close="closePreview"
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
                :userId="$page.props.auth.user.name"
                :getUserName="getUserName"
                :getUserAvatar="getUserAvatar"
                :currentUserAvatar="$page.props.auth.user.profile_photo_url"
              />
            </div>
          </div>
        </div>
      </div>
      
      <!-- Fixed footer -->
      <div class="glossy-footer sticky bottom-0 z-30 px-4 py-3 sm:px-6 border-t border-gray-700/50">
        <!-- Progress Bar for Status - moved here -->
        <div class="mb-3">
          <div class="w-full bg-gray-800 rounded-full h-2.5 dark:bg-gray-800">
            <div 
              class="h-2.5 rounded-full transition-all duration-500 ease-in-out" 
              :class="getProgressBarColor(workOrder.status)"
              :style="`width: ${getStatusProgress(workOrder.status)}%`"
            ></div>
          </div>
          <p class="text-xs text-gray-400 mt-1 flex justify-between">
            <span>Progress</span>
            <span>{{ getStatusProgress(workOrder.status) }}%</span>
          </p>
        </div>
        
        <!-- Footer buttons -->
        <div class="sm:flex sm:flex-row-reverse">
          <!-- Replace the Create Invoice button with Archive Work Order button -->
          <button 
            @click="archiveWorkOrder" 
            class="glossy-btn btn w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-purple-400 hover:text-gray-900 hover:bg-purple-400 font-medium sm:ml-3 sm:w-auto sm:text-sm"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
            Archive Work Order
          </button>
          <!-- Updated duplicate button with disabled state when status is Complete -->
          <button 
            @click="duplicateWorkOrder" 
            :disabled="workOrder.status === 'Complete'"
            :class="{'opacity-50 cursor-not-allowed': workOrder.status === 'Complete'}"
            class="glossy-btn btn w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-green-400 font-medium hover:bg-green-400 hover:text-black sm:ml-3 sm:w-auto sm:text-sm"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            Duplicate
          </button>
          <button @click="closeModal" class="glossy-btn btn mt-3 w-full inline-flex justify-center rounded-md border shadow-sm px-4 py-2 text-red-400 font-medium hover:bg-red-400 hover:text-black sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            Close
          </button>
        </div>
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
import NetworkStatusIndicator from '@/Components/NetworkStatusIndicator.vue'; 
import axios from 'axios';
import { router } from '@inertiajs/vue3'; 

export default {
  name: 'WorkOrder',
  components: {
    Messenger,
    PdfViewer, 
    NetworkStatusIndicator, 
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
    users: {
      type: Array,
      required: true,
    },
  },
  setup(props, { emit }) {
    // Add error handling for missing or invalid props
    if (!props.workOrder) {
      console.error('WorkOrder prop is missing or invalid.');
    }
    if (!Array.isArray(props.users)) {
      console.error('Users prop is missing or invalid.');
    }

    // Add this near the top of setup(), with other refs
    const showPdfViewer = ref(false);
    
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

      // Log what we're sending for debugging
      console.log(`Updating ${field} with:`, data);

      try {
        // Get fresh CSRF token
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Use web route instead of API route with explicit headers
        const response = await axios.post(`/work-orders/${props.workOrder.id}/update-field`, data, {
          headers: {
            'X-CSRF-TOKEN': csrf,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });
        
        if (response.data.success) {
          Object.keys(data).forEach(key => {
            props.workOrder[key] = data[key];
          });
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
             lowerFilename.endsWith('.webp') ||
             lowerFilename.endsWith('.heic');
    };

    // Add new function to check for document files
    const isDocumentFile = (filename) => {
      if (!filename) return false;
      const lowerFilename = filename.toLowerCase();
      return lowerFilename.endsWith('.docx') ||
             lowerFilename.endsWith('.doc');
    };
    
    // Get clean file name for display
    const getFileName = (path) => {
      if (!path) return '';
      // Extract just the filename from the full path
      return path.split('/').pop();
    };
    
    // Preview attachment (for images)
    const handlePreviewAttachment = (attachment) => {
      if (isImageFile(attachment) || isPdfFile(attachment) || isDocumentFile(attachment)) {
        previewAttachment.value = attachment;
      }
    };

    // Close preview
    const closePreview = () => {
      previewAttachment.value = null;
    };
    
    // Updated file upload handler that accepts PDFs
    const handleImageUpload = (event) => {
      const files = Array.from(event.target.files);
      const maxFileSize = 8 * 1024 * 1024; // 8MB limit - reduced from 10MB to match server limits
      const validExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.pdf', '.heic', '.docx'];

      // Clear previous files and errors
      form.images = [];
      uploadError.value = '';

      // More thorough validation
      const validFiles = [];
      const invalidFiles = [];
      files.forEach(file => {
        // Get file extension
        const extension = '.' + file.name.split('.').pop().toLowerCase();
        const isValidType = validExtensions.includes(extension);
        const isValidSize = file.size <= maxFileSize;
        console.log(`Validating file: ${file.name} (${file.type}, ${Math.round(file.size/1024)}KB) - Valid type: ${isValidType}, Valid size: ${isValidSize}`);
        if (!isValidType) {
          invalidFiles.push(`${file.name} (unsupported file type)`);
        } else if (!isValidSize) {
          invalidFiles.push(`${file.name} (${Math.round(file.size/1024/1024)}MB exceeds 8MB limit)`);
        } else {
          validFiles.push(file);
        }
      });
      
      if (invalidFiles.length > 0) {
        uploadError.value = `Cannot upload these files:\n${invalidFiles.join('\n')}`;
        alert(`Cannot upload these files:\n${invalidFiles.join('\n')}`);
      }

      if (validFiles.length) {
        form.images = validFiles;
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

      // Create fresh FormData
      const formData = new FormData();
      // Add each file individually with numeric index to match Laravel's expectations
      form.images.forEach((file, index) => {
        // Use attachments[] format for Laravel's array validation
        formData.append(`attachments[${index}]`, file);
        console.log(`Adding file ${index}: ${file.name} (${file.type}, ${Math.round(file.size / 1024)} KB)`);
      });

      // Add work order ID explicitly to ensure it's properly associated
      formData.append('work_order_id', props.workOrder.id);

      try {
        // Get fresh CSRF token for this specific request
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
          throw new Error('CSRF token not found. Please refresh the page and try again.');
        }
        
        // Add CSRF token to formdata as an additional safeguard
        formData.append('_token', csrfToken);
        
        console.log('Uploading files to:', `/work-orders/${props.workOrder.id}/attachments`);
        
        // Make the request with progress tracking
        const response = await axios.post(
          `/work-orders/${props.workOrder.id}/attachments`,
          formData,
          {
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json',
              // Do not set Content-Type for multipart/form-data
            },
            withCredentials: true, // Include cookies for session authentication
            onUploadProgress: (progressEvent) => {
              const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
              uploadProgress.value = percentCompleted;
            },
            timeout: 300000 // 5 minutes
          }
        );
        
        // Handle success response
        console.log('Upload response:', response.data);
        
        if (response.data && (response.data.success || response.data.paths || response.data.attachments)) {
          // Handle attachments from any of the possible response formats
          const attachments = response.data.attachments || response.data.paths || [];
          
          // Ensure we have arrays for storage
          if (!Array.isArray(props.workOrder.images)) {
            props.workOrder.images = [];
          }
          if (!Array.isArray(props.workOrder.file_attachments)) {
            props.workOrder.file_attachments = [];
          }
          
          // Add new attachments to both arrays
          if (Array.isArray(attachments)) {
            attachments.forEach(attachment => {
              props.workOrder.images.push(attachment);
              props.workOrder.file_attachments.push(attachment);
            });
          } else if (typeof attachments === 'string') {
            props.workOrder.images.push(attachments);
            props.workOrder.file_attachments.push(attachments);
          }
          
          // Success - clear form and close upload interface
          form.images = [];
          editingField.value.images = false;
        } else {
          // No clear success indicator
          uploadError.value = response.data?.message || 'Upload may have failed. Please check attachments.';
        }
      } catch (error) {
        console.error('Error uploading files:', error);
        // Enhanced validation error handling
        if (error.response?.status === 422 && error.response?.data?.errors) {
          // Extract and format validation errors
          const validationErrors = error.response.data.errors;
          console.error('Validation errors:', validationErrors);
          
          let errorMessages = [];
          
          // Check for common validation issues and provide helpful messages
          Object.entries(validationErrors).forEach(([field, messages]) => {
            // Extract index from field name like "attachments.0"
            const fileIndex = field.match(/\d+$/);
            const fileNumber = fileIndex ? parseInt(fileIndex[0]) + 1 : '';
            const fileLabel = fileNumber ? `File #${fileNumber}` : field;
            
            messages.forEach(message => {
              if (message.includes('max') || message.includes('size')) {
                // File size error - get the specific max size from the error if possible
                const sizeMatch = message.match(/\d+\s*(kb|mb|gb)/i);
                const sizeLimit = sizeMatch ? sizeMatch[0].toUpperCase() : '8MB';
                errorMessages.push(`${fileLabel}: File exceeds ${sizeLimit} limit`);
              } else if (message.includes('mimes') || message.includes('type')) {
                // Extract allowed mime types from error message if possible
                const mimesMatch = message.match(/must be a file of type: ([^.]+)/i);
                const allowedTypes = mimesMatch ? mimesMatch[1] : 'JPG, JPEG, PNG, GIF, PDF, HEIC, DOCX';
                errorMessages.push(`${fileLabel}: Must be one of these types: ${allowedTypes}`);
              } else {
                // Other errors
                errorMessages.push(`${fileLabel}: ${message}`);
              }
            });
          });
          
          // Set a detailed user-friendly error message
          if (errorMessages.length > 0) {
            uploadError.value = errorMessages.join('\n');
          } else {
            uploadError.value = 'Files failed validation. Try with smaller files (under 8MB) or different file types.';
          }
        } else if (error.response?.status === 419) {
          uploadError.value = 'Your session has expired. Please refresh the page and try again.';
        } else if (error.response?.data?.message) {
          uploadError.value = error.response.data.message;
        } else {
          uploadError.value = `Upload failed: ${error.message}`;
        }
      } finally {
        isUploading.value = false;
        // If no error, close the upload interface after a short delay
        if (!uploadError.value) {
          setTimeout(() => {
            editingField.value.images = false;
          }, 1000);
        }
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
      // Define variables outside try block so they're accessible in finally
      const button = event.target.closest('button');
      const originalText = button.innerHTML;
      
      try {
        // Show loading state
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Archiving...';
        
        // Get CSRF token from meta tag with better error handling
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrf = csrfMeta ? csrfMeta.getAttribute('content') : null;
        
        if (!csrf) {
          throw new Error('CSRF token not found. Please refresh the page and try again.');
        }
        
        // Create payload with CSRF token included in body data
        const payload = {
          archive: true,
          _token: csrf // Include token in request body as well
        };
        
        // Use web route to archive the work order
        const response = await axios.post(`/work-orders/${props.workOrder.id}/archive`, payload, {
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
          emit('work-order-archived', {
            workOrderId: props.workOrder.id,
            message: 'Work order has been successfully archived.'
          });
          
          // Refresh the main work order list to reflect the change
          router.reload({ only: ['workOrders'] });
        } else {
          throw new Error(response.data.message || 'Failed to archive work order');
        }
      } catch (error) {
        console.error('Error archiving work order:', error);
        // Detailed error logging
        if (error.response) {
          console.error('Response data:', error.response.data);
          console.error('Response status:', error.response.status);
          console.error('Response headers:', error.response.headers);
          // Special handling for CSRF token issues
          if (error.response.status === 419 || 
              (error.response.data && error.response.data.message && 
              error.response.data.message.toLowerCase().includes('csrf'))) {
            alert('Your session has expired. Please refresh the page and try again.');
            return;
          }
        } else if (error.request) {
          console.error('No response received:', error.request);
        } else {
          console.error('Error setting up request:', error.message);
        }
        
        // Handle auth errors
        if (error.response?.status === 401) {
          window.location.href = '/login';
        } else {
          alert(`Failed to archive work order: ${error.response?.data?.message || error.message}`);
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
        // Extract just the path portion from the full URL
        const pathMatch = data.path || data.url.match(/\/storage\/(.*)/);
        const attachmentPath = typeof pathMatch === 'string' ? pathMatch : pathMatch?.[1];
        
        if (attachmentPath) {
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
          alert(`Document uploaded successfully`);
        }
      } catch (error) {
        console.error('Error handling document upload:', error);
        alert('Failed to process the uploaded document.');
      }
    };

    // Add these new refs near the top of setup()
    const isUploading = ref(false);
    const uploadProgress = ref(0);
    const uploadError = ref('');

    const getStatusProgress = (status) => {
      if (!status) return 0;
      const statusLower = status.toLowerCase();
      
      if (statusLower.includes('complete')) {
        return 100;
      } else if (statusLower.includes('part') || statusLower.includes('return')) {
        return 75;
      } else if (statusLower.includes('progress')) {
        return 50;
      } else if (statusLower.includes('scheduled')) {
        return 10;
      } else if (statusLower.includes('cancel')) {
        return 0;
      }
      
      return 0;
    };

    const getProgressBarColor = (status) => {
      if (!status) return 'bg-gray-600';
      
      const statusLower = status.toLowerCase();
      
      if (statusLower.includes('complete')) {
        return 'bg-green-500';
      } else if (statusLower.includes('part') || statusLower.includes('return')) {
        return 'bg-purple-500';
      } else if (statusLower.includes('progress')) {
        return 'bg-yellow-500';
      } else if (statusLower.includes('scheduled')) {
        return 'bg-blue-500';
      } else if (statusLower.includes('cancel')) {
        return 'bg-red-500';
      }
      
      return 'bg-gray-600';
    };

    // Define the getUserName function
    const getUserName = (userName) => {
      return userName || 'Unknown User';
    };

    const getUserAvatar = (userName) => {
      // Replace this logic with the actual implementation for fetching user avatars
      return `/images/avatars/${userName || 'default'}.png`;
    };

    return {
      getStatusClasses,
      isEditing,
      editingField,
      form,
      formatDate, 
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
    };
  },
};
</script>

<style scoped>
/* Base styles */
.description {
  word-wrap: break-word;
  overflow-wrap: break-word;
  white-space: normal;
  max-width: 100%;
}

/* Basic styles for focus and icons */
:focus {
  outline-color: theme('colors.lime.400');
}

.file-icon {
  @apply flex-shrink-0 h-5 w-5;
}

.pdf-icon {
  @apply text-red-400;
}

.image-icon {
  @apply text-blue-400;
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
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.glossy-btn::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 80%);
  transform: rotate(45deg);
  opacity: 0;
  transition: opacity 0.3s ease;
}

/* Modal and scrollbar styles */
.modal-animation {
  animation: modalFadeInSlide 0.5s ease-out;
  align-items: center;
  padding: 0;
}

.modal-body {
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  height: 100%;
  max-height: calc(80vh - 125px);
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
  background: linear-gradient(180deg, rgba(31, 41, 55, 0.95) 0%, rgba(17, 24, 39, 0.9) 100%);
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
}

/* Scrollable content area */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: rgba(75, 85, 99, 0.5) rgba(17, 24, 39, 0.3);
  overflow-y: auto;
  flex-grow: 1;
}

/* Fixed footer styling */
.glossy-footer {
  flex-shrink: 0;
  background: linear-gradient(0deg, rgba(31, 41, 55, 0.95) 0%, rgba(17, 24, 39, 0.9) 100%);
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
}

/* Ensure content doesn't overflow modal */
.modal-animation {
  padding: 1rem;
  align-items: center;
  justify-content: center;
}
</style>