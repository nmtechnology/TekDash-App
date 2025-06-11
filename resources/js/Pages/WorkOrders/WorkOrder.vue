<template>
  <div v-if="props.showModal">
    <!-- Background overlay -->
    <div @click="closeModal" class="fixed inset-0 bg-black bg-opacity-50 z-40"></div>
    
    <!-- Work Order Modal (left side, wider) -->
    <div class="fixed inset-0 flex items-start justify-start z-50 pointer-events-none pl-8">
      <div class="relative z-50 w-full max-w-6xl h-[70vh] mt-60 rounded-lg overflow-hidden shadow-xl transform transition-all glossy-card pointer-events-auto flex flex-col">
        <!-- Header section -->
        <div class="glossy-header p-4 border-b border-gray-700">
          <div class="flex items-center justify-between">
            <div class="flex-grow relative group">
              <div v-if="!editingField.title" @click="startEditing('title')" class="text-xl font-semibold text-gray-100 cursor-pointer hover:text-indigo-400 flex items-center">
                <span>{{ form.title || 'Untitled Work Order' }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
              </div>
              <div v-else class="w-full max-w-xl">
                <input 
                  type="text" 
                  v-model="form.title" 
                  @blur="saveField('title')" 
                  class="block w-full px-3 py-1 text-xl font-semibold bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-indigo-500" 
                  ref="titleInput"
                  @keyup.enter="saveField('title')"
                  placeholder="Enter work order title"
                  autofocus
                />
              </div>
            </div>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-200 z-50 relative">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Content section -->
        <div class="flex-1 p-5 pb-8 overflow-y-auto timeline-container">
          <!-- Work Order Status and Time -->
          <div class="mb-4 flex flex-wrap justify-between items-center">
            <div>
              <span v-if="!editingField.status" :class="getStatusClasses(props.workOrder.status)" @click="updateStatus" class="cursor-pointer">
                {{ props.workOrder.status }}
              </span>
              <div v-else class="inline-block">
                <select v-model="form.status" @blur="saveField('status')" @change="saveField('status')" 
                  class="bg-gray-800 border border-gray-600 rounded-md text-white px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                  <option v-for="status in VALID_STATUSES" :key="status" :value="status">{{ status }}</option>
                </select>
              </div>
            </div>
            <div class="text-gray-300 text-sm">
              {{ formatDate(props.workOrder.date_time) }}
            </div>
          </div>

          <!-- Work Order Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="space-y-4">
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
              :workOrderId="props.workOrder.id"
              :userId="props.workOrder.user_id"
              :initialNotes="props.workOrder.notes || []"
              :currentUserId="props.workOrder.user_id"
              :getUserName="getUserName"
              :getUserAvatar="getUserAvatar"
              :users="users"
            />
          </div>
        </div>

        <!-- Footer section with all action buttons -->
        <div class="glossy-footer p-4 border-t border-gray-700">
          <!-- Upload progress bar - always visible in footer when uploading -->
          <div v-if="isUploading" class="mb-3 w-full">
            <div class="flex justify-between mb-1">
              <span class="text-xs text-gray-300">Uploading Files...</span>
              <span class="text-xs text-gray-300">{{ uploadProgress }}%</span>
            </div>
            <div class="relative w-full h-1.5 bg-gray-700 rounded-full overflow-hidden">
              <div class="absolute left-0 top-0 h-full bg-lime-400" :style="{width: uploadProgress + '%'}"></div>
            </div>
          </div>
          
          <!-- Footer buttons -->
          <div class="sm:flex sm:justify-between">
            <!-- Save All button (left side) - only shown when in edit mode -->
            <button 
              v-if="isAnyFieldBeingEdited"
              @click="saveAllChanges" 
              class="glossy-btn btn w-full mb-2 sm:mb-0 inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-amber-400 font-bold hover:bg-amber-400 hover:text-black sm:w-auto sm:text-xs"
              :class="{ 'animate-pulse': hasChanges }"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
              </svg>
              Save All Changes
            </button>
            <div v-else class="hidden sm:block"></div>
            
            <!-- Right side buttons container -->
            <div class="sm:flex sm:flex-row-reverse">
            <!-- Archive Work Order button -->
            <button 
              @click="archiveWorkOrder" 
              :disabled="props.workOrder.status !== 'Complete'"
              class="glossy-btn btn w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-3 py-1.5 text-green-400 hover:text-gray-900 hover:bg-green-400 font-bold sm:ml-2 sm:w-auto sm:text-xs"
              :class="{
                'opacity-50 cursor-not-allowed hover:bg-transparent hover:text-green-400': props.workOrder.status !== 'Complete'
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
              :disabled="props.workOrder.status !== 'Part Needed'"
              :class="[
                'glossy-btn btn w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-3 py-1.5 text-purple-400 font-bold hover:bg-purple-400 hover:text-black sm:ml-2 sm:w-auto sm:text-xs',
                { 'opacity-50 cursor-not-allowed': props.workOrder.status !== 'Part Needed' }
              ]"
              :title="props.workOrder.status !== 'Part Needed' ? 'Duplication is only available for work orders with Part Needed status' : 'Create a duplicate work order'"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              Duplicate
            </button>

           <!-- Get Signature button -->
            <button @click="getSignature" 
              class="glossy-btn btn w-full inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-blue-400 font-bold hover:bg-blue-400 hover:text-black sm:ml-2 sm:w-auto sm:text-xs"
              :disabled="!hasPdfAttachment || props.workOrder.status === 'Scheduled'"
              :class="{ 'opacity-50 cursor-not-allowed hover:bg-transparent hover:text-indigo-400': !hasPdfAttachment || props.workOrder.status === 'Scheduled' }"
              :title="getSignatureButtonTitle"
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
            
            <!-- Save button and upload progress when editing -->
            <div v-else class="flex flex-col w-full sm:w-auto">
              <button 
                @click="saveField('images')" 
                class="glossy-btn btn font-bold w-full inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-green-400 hover:bg-green-400 hover:text-black sm:ml-2 sm:w-auto sm:text-xs"
                :disabled="isUploading"
              >
                <svg v-if="!isUploading" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg v-else class="animate-spin h-3 w-3 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isUploading ? 'Uploading...' : 'Save' }}
              </button>
              
              <!-- Upload progress indicator -->
              <div v-if="isUploading" class="mt-2 relative w-full h-1 bg-gray-700 rounded-full overflow-hidden sm:ml-2">
                <div class="absolute left-0 top-0 h-full bg-lime-400" :style="{width: uploadProgress + '%'}"></div>
              </div>
              
              <!-- Upload error message -->
              <div v-if="uploadError" class="mt-1 text-xs text-red-400 sm:ml-2">
                {{ uploadError }}
              </div>
              
              <!-- Files selected indicator -->
              <div v-if="form.images && form.images.length && !isUploading" class="mt-1 text-xs text-gray-400 sm:ml-2">
                {{ form.images.length }} file(s) selected
              </div>
            </div>
            
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
    
    <!-- Timeline Modal (positioned closer to work order modal) -->
    <div class="fixed inset-0 flex items-start justify-start z-50 pointer-events-none">
      <div class="flex gap-6 w-full px-8 mt-60 pointer-events-none">
        <!-- Work order modal space -->
        <div class="w-full max-w-6xl pointer-events-none"></div>
        
        <!-- Timeline modal container -->
        <div class="w-80 flex-shrink-0 pointer-events-auto">
          <div class="relative z-60 w-full h-[55vh] rounded-lg overflow-hidden shadow-xl transform transition-all glossy-card flex flex-col pointer-events-auto">
            <!-- Timeline Header -->
            <div class="glossy-header p-4 border-b border-gray-700">
              <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-lime-400">{{ form.title }} - Activity</h2>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-200 z-50 relative">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
            
            <!-- Timeline Content -->
            <div class="flex-1 overflow-y-auto p-3 timeline-container">
              <Timeline :workOrderId="props.workOrder.id" />
            </div>
            
            <!-- Timeline Footer -->
            <div class="glossy-footer p-3 border-t border-gray-700">
              <!-- You can add footer content or buttons here -->
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Toast notification -->
    <div v-if="toast.show" 
      class="fixed bottom-4 right-4 z-70 p-4 rounded-lg shadow-lg flex items-center"
      :class="{
        'bg-green-700 text-white': toast.type === 'success',
        'bg-red-700 text-white': toast.type === 'error',
        'bg-yellow-600 text-white': toast.type === 'warning',
        'bg-blue-700 text-white': toast.type === 'info'
      }"
    >
      <!-- Icon based on toast type -->
      <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
      </svg>
      <svg v-if="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
      </svg>
      <svg v-if="toast.type === 'warning'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
      </svg>
      <svg v-if="toast.type === 'info'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd" />
      </svg>
      
      <!-- Toast message -->
      <div class="mr-8">{{ toast.message }}</div>
      
      <!-- Close button -->
      <button @click="hideToast" class="absolute top-2 right-2 text-white hover:text-gray-100">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>
  </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
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

// Reference for the title input field to allow focusing
const titleInput = ref(null);

// Function to start editing a field
const startEditing = (field) => {
  Object.keys(editingField.value).forEach(key => {
    editingField.value[key] = false;
  });
  editingField.value[field] = true;
  
  // If editing title, wait for the DOM to update then focus the input
  if (field === 'title') {
    nextTick(() => {
      if (titleInput.value) {
        titleInput.value.focus();
      }
    });
  }
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
    // For images, we need to use FormData to handle file uploads
    try {
      isUploading.value = true;
      uploadProgress.value = 0;
      uploadError.value = null;
      
      const formData = new FormData();
      
      // Append each file to the FormData object
      if (form.value.images && form.value.images.length > 0) {
        form.value.images.forEach((file, index) => {
          formData.append(`attachments[]`, file); // Laravel convention for file arrays
        });
      }
      
      // We need to add the work order ID to identify the relationship
      formData.append('work_order_id', props.workOrder.id);
      
      // Make the API call to upload files
      const response = await axios.post(
        `/work-orders/${props.workOrder.id}/attachments`,
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          onUploadProgress: (progressEvent) => {
            if (progressEvent.total) {
              uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
            }
          }
        }
      );
      
      // Handle successful upload
      if (response.data.success || response.status === 200) {
        editingField.value.images = false;
        // Update the work order with new attachments
        if (response.data.attachments) {
          props.workOrder.attachments = response.data.attachments;
        } else if (response.data.workOrder && response.data.workOrder.attachments) {
          props.workOrder.attachments = response.data.workOrder.attachments;
        }
        
        // Clear the file input for subsequent uploads
        if (fileInput.value) {
          fileInput.value.value = '';
        }
        
        isUploading.value = false;
        uploadProgress.value = 100;
        
        // Show success message
        uploadError.value = null;
      }
      
      return; // Skip the regular field update since we've handled the upload
    } catch (error) {
      console.error('Error uploading images:', error);
      uploadError.value = error.response?.data?.message || 'Failed to upload files. Please try again.';
      isUploading.value = false;
      return; // Skip the rest of the function
    }
  }
  
  try {
    // For regular fields (not images), use the updateField endpoint
    const response = await axios.post(`/work-orders/${props.workOrder.id}/update-field`, {
      [field]: form.value[field],
      '_token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    });
    
    if (response.data.success) {
      // Update was successful
      editingField.value[field] = false;
      
      // Update the local workOrder object with the returned data if available
      if (response.data.workOrder) {
        Object.assign(props.workOrder, response.data.workOrder);
      }
      
      // If we're updating hours or rates, recalculate grand total
      if (['hours', 'hourly_rate', 'travel_cost', 'has_travel'].includes(field)) {
        await updateGrandTotal();
      }
      
      // Refresh timeline to show the new activity
      await refreshTimeline();
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
  
  // Clear any previous error
  uploadError.value = null;
      
  // Validate files (optional)
  const validFiles = Array.from(files).filter(file => {
    // Check file type
    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'image/heic', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    return validTypes.includes(file.type);
  });
  
  if (validFiles.length !== files.length) {
    uploadError.value = 'Some files were not recognized as valid file types';
  }
  
  // Set images to validated files
  form.value.images = validFiles;
  editingField.value.images = true;
  
  // We'll set isUploading to true in the saveField function when actually uploading
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
  // Don't proceed if no PDF attachment or status is Scheduled
  if (!hasPdfAttachment.value || props.workOrder.status === 'Scheduled') return;
      
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

// Computed property for signature button title
const getSignatureButtonTitle = computed(() => {
  if (props.workOrder.status === 'Scheduled') {
    return 'Cannot collect signatures for work orders in Scheduled status';
  } else if (!hasPdfAttachment.value) {
    return 'A PDF document must be attached to get signatures';
  } else {
    return `Click to sign ${getFileName(mostRecentPdfAttachment.value)}`;
  }
});

// Computed property for total amount
const totalAmount = computed(() => {
  // Ensure we're working with numbers by using parseFloat
  const hourlyRate = parseFloat(form.value.hourly_rate || 0);
  const hours = parseFloat(form.value.hours || 0);
  const laborTotal = hourlyRate * hours;
  
  // Only include travel cost if has_travel is true
  const travelCost = form.value.has_travel ? parseFloat(form.value.travel_cost || 0) : 0;
  
  // Add the values as numbers and round to 2 decimal places
  return parseFloat((laborTotal + travelCost).toFixed(2));
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

// Toast notification system
const toast = ref({
  show: false,
  message: '',
  type: 'success', // 'success', 'error', 'warning', 'info'
  timeout: null
});

// Show toast notification
const showToast = (message, type = 'success', duration = 3000) => {
  // Clear any existing timeout
  if (toast.value.timeout) {
    clearTimeout(toast.value.timeout);
  }
  
  // Set toast properties
  toast.value.show = true;
  toast.value.message = message;
  toast.value.type = type;
  
  // Auto hide the toast after duration
  toast.value.timeout = setTimeout(() => {
    toast.value.show = false;
  }, duration);
};

// Hide toast notification
const hideToast = () => {
  toast.value.show = false;
  if (toast.value.timeout) {
    clearTimeout(toast.value.timeout);
  }
};

// Track whether there are unsaved changes
const hasChanges = computed(() => {
  // Compare original work order with current form values
  return (
    form.value.title !== props.workOrder.title ||
    form.value.description !== props.workOrder.description ||
    form.value.address !== props.workOrder.address ||
    form.value.hours !== props.workOrder.hours ||
    form.value.hourly_rate !== props.workOrder.hourly_rate ||
    form.value.travel_cost !== props.workOrder.travel_cost ||
    form.value.has_travel !== props.workOrder.has_travel ||
    form.value.status !== props.workOrder.status
  );
});

// Check if any field is currently being edited
const isAnyFieldBeingEdited = computed(() => {
  return Object.values(editingField.value).some(value => value === true);
});

// Save all changes at once
const saveAllChanges = async () => {
  if (!hasChanges.value) {
    showToast('No changes to save', 'info');
    return;
  }
  
  try {
    // Prepare data to send
    const updatedData = {
      title: form.value.title,
      description: form.value.description,
      address: form.value.address,
      hours: form.value.hours,
      hourly_rate: form.value.hourly_rate,
      travel_cost: form.value.travel_cost,
      has_travel: form.value.has_travel,
      status: form.value.status,
      '_token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    };
    
    // Make the API call to update all fields at once
    const response = await axios.post(`/work-orders/${props.workOrder.id}/update-all`, updatedData);
    
    if (response.data.success) {
      // Update the local workOrder object with the returned data
      if (response.data.workOrder) {
        Object.assign(props.workOrder, response.data.workOrder);
      }
      
      // Exit edit mode for all fields
      Object.keys(editingField.value).forEach(key => {
        editingField.value[key] = false;
      });
      
      // Update timeline
      await refreshTimeline();
      
      // Show success notification
      showToast('Work order updated successfully', 'success');
    }
  } catch (error) {
    console.error('Error saving all changes:', error);
    showToast('Failed to save changes: ' + (error.response?.data?.message || 'Unknown error'), 'error');
  }
};

// Function to refresh the timeline
const refreshTimeline = async () => {
  try {
    // Get the current activities from the API
    const response = await axios.get(`/work-orders/${props.workOrder.id}/activities`);
    
    // Emit a custom event that the Timeline component can listen for
    const timelineRefreshEvent = new CustomEvent('timeline-refresh', { 
      detail: { activities: response.data.activities } 
    });
    document.dispatchEvent(timelineRefreshEvent);
    
    return true;
  } catch (error) {
    console.error('Error refreshing timeline:', error);
    return false;
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
  max-height: vh; /* Reduced to match the modal height */
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
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), 0 4px 16px rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
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
