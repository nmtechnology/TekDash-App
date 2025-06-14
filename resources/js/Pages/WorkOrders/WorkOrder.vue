<template>
  <div v-if="props.showModal">
    <!-- Toast Container for notifications -->
    <ToastContainer />

    <!-- Background overlay -->
    <div @click="emit('close')" class="fixed inset-0 bg-black bg-opacity-50 z-40"></div>

    <!-- Work Order Modal (center position, slightly narrower) -->
    <div class="fixed inset-0 flex items-start justify-center z-50 pointer-events-none">
      <div
        class="relative z-50 w-full max-w-3xl h-[70vh] mt-60 rounded-lg overflow-hidden shadow-xl transform transition-all glossy-card pointer-events-auto flex flex-col">
        <!-- Header section -->
        <div class="glossy-header p-4 border-b border-gray-700">
          <div class="flex items-center justify-between">
            <div class="flex-grow relative group">
              <div v-if="!editingField.title" @click="startEditing('title')"
                class="text-xl font-semibold text-gray-100 cursor-pointer hover:text-indigo-400 flex items-center">
                <span>{{ form.title || 'Untitled Work Order' }}</span>
                <svg xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4 ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-gray-400" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
              </div>
              <div v-else class="w-full max-w-xl">
                <input type="text" v-model="form.title" @blur="saveField('title')"
                  class="block w-full px-3 py-1 text-xl font-semibold bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  ref="titleInput" @keyup.enter="saveField('title')" placeholder="Enter work order title" autofocus />
              </div>
            </div>
            <div class="flex space-x-2">
              <button @click="toggleTimelineModal"
                class="glossy-btn btn inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-lime-400 font-bold hover:bg-lime-400 hover:text-black z-50 relative"
                title="Toggle Timeline">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 17l-6-6m0 0l6-6m-6 6h14" />
                </svg>
              </button>
              <button @click="toggleMessengerModal"
                class="glossy-btn btn inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-lime-400 font-bold hover:bg-lime-400 hover:text-black z-50 relative"
                title="Toggle Messages">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
              </button>
              <button @click="emit('close')"
                class="glossy-btn btn inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-red-400 font-bold hover:bg-red-600 hover:text-black z-50 relative">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Content section -->
        <div class="flex-1 p-5 pb-8 overflow-y-auto timeline-container">
          <!-- Work Order Status and Time -->
          <div class="mb-4 flex flex-wrap justify-between items-center">
            <div>
              <template v-if="!editingField.status">
                <Badge :variant="statusBadgeVariant.variant" :class="statusBadgeVariant.class + ' cursor-pointer ring-1 ring-inset mr-2'" @click="updateStatus">
                  {{ props.workOrder.status }}
                </Badge>
              </template>
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
                  <textarea v-model="form.description" @blur="saveField('description')"
                    class="block w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    rows="3"></textarea>
                </div>
              </div>

              <!-- Address -->
              <div>
                <label class="block text-sm font-medium text-gray-300">Address</label>
                <div v-if="!editingField.address" @click="startEditing('address')" class="text-gray-100">
                  {{ form.address }}
                </div>
                <div v-else class="mt-1">
                  <input type="text" v-model="form.address" @blur="saveField('address')"
                    class="block w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-indigo-500" />
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
                  <input type="number" v-model="form.hours" @blur="saveField('hours')"
                    class="block w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    min="0" step="0.5" />
                </div>
              </div>

              <!-- Hourly Rate -->
              <div>
                <label class="block text-sm font-medium text-gray-300">Hourly Rate</label>
                <div v-if="!editingField.hourly_rate" @click="startEditing('hourly_rate')" class="text-gray-100">
                  {{ formatCurrency(form.hourly_rate) }}
                </div>
                <div v-else class="mt-1">
                  <input type="number" v-model="form.hourly_rate" @blur="saveField('hourly_rate')"
                    class="block w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    min="0" step="0.01" />
                </div>
              </div>

              <!-- Travel Costs -->
              <div>
                <div class="flex items-center mb-2">
                  <input type="checkbox" v-model="form.has_travel" @change="saveField('has_travel')"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800" />
                  <label class="ml-2 text-sm font-medium text-gray-300">Include Travel Costs</label>
                </div>
                <div v-if="form.has_travel">
                  <div v-if="!editingField.travel_cost" @click="startEditing('travel_cost')" class="text-gray-100">
                    {{ formatCurrency(form.travel_cost) }}
                  </div>
                  <div v-else class="mt-1">
                    <input type="number" v-model="form.travel_cost" @blur="saveField('travel_cost')"
                      class="block w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                      min="0" step="0.01" />
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
                <div v-if="isImageFile(attachment)" @click="handlePreviewAttachment(attachment)"
                  class="cursor-pointer relative group">
                  <img :src="attachment.url || attachment" class="w-full h-32 object-cover rounded-lg" />
                  <div
                    class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <span class="text-white">Preview</span>
                  </div>
                </div>

                <!-- PDF Preview -->
                <div v-else-if="isPdfFile(attachment)" class="cursor-pointer">
                  <div
                    class="pdf-preview-container h-32 bg-gray-800 rounded-lg overflow-hidden flex flex-col hover:bg-gray-700 transition-colors"
                    @click="handlePreviewAttachment(attachment)">
                    <!-- Use PdfThumbnail component for PDFs -->
                    <PdfThumbnail :pdfUrl="attachment.url || attachment" :filename="getFileName(attachment)"
                      class="flex-1" />

                    <!-- Action indicators below thumbnail -->
                    <div class="flex items-center justify-center py-2 space-x-2 bg-gray-900 bg-opacity-80">
                      <span class="text-xs bg-blue-800 text-white px-2 py-1 rounded-sm">View</span>
                      <span class="text-xs bg-green-800 text-white px-2 py-1 rounded-sm"
                        :class="{ 'opacity-50': props.workOrder.status === 'Scheduled' }">
                        Sign
                      </span>
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>

          <!-- Messaging Section Button -->
          <div>
            <button @click="toggleMessengerModal"
              class="btn bg-gray-800 hover:bg-gray-700 text-lime-400 border border-gray-700 rounded-lg px-4 py-2 flex items-center gap-2 shadow-lg transition-all duration-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
              Open Messages
            </button>
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
            <button v-if="isAnyFieldBeingEdited" @click="saveAllChanges"
              class="glossy-btn btn w-full mb-2 sm:mb-0 inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-amber-400 font-bold hover:bg-amber-400 hover:text-black sm:w-auto sm:text-xs"
              :class="{ 'animate-pulse': hasChanges }">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
              </svg>
              Save All Changes
            </button>
            <div v-else class="hidden sm:block"></div>

            <!-- Right side buttons container -->
            <div class="sm:flex sm:flex-row-reverse">
              <!-- Archive Work Order button -->
              <button @click="archiveWorkOrder" :disabled="props.workOrder.status !== 'Complete'"
                class="glossy-btn btn w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-3 py-1.5 text-green-400 hover:text-gray-900 hover:bg-green-400 font-bold sm:ml-2 sm:w-auto sm:text-xs"
                :class="{
                'opacity-50 cursor-not-allowed hover:bg-transparent hover:text-green-400': props.workOrder.status !== 'Complete'
              }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
                Archive
              </button>

              <!-- Duplicate button -->
              <button @click="duplicateWorkOrder($event)" :disabled="props.workOrder.status !== 'Part Needed'" :class="[
                'glossy-btn btn w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-3 py-1.5 text-purple-400 font-bold hover:bg-purple-400 hover:text-black sm:ml-2 sm:w-auto sm:text-xs',
                { 'opacity-50 cursor-not-allowed': props.workOrder.status !== 'Part Needed' }
              ]"
                :title="props.workOrder.status !== 'Part Needed' ? 'Duplication is only available for work orders with Part Needed status' : 'Create a duplicate work order'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Duplicate
              </button>

              <!-- Get Signature button -->
              <button @click="getSignature"
                class="glossy-btn btn w-full inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-blue-400 font-bold hover:bg-blue-400 hover:text-black sm:ml-2 sm:w-auto sm:text-xs"
                :disabled="!hasPdfAttachment || props.workOrder.status === 'Scheduled'"
                :class="{ 'opacity-50 cursor-not-allowed hover:bg-transparent hover:text-indigo-400': !hasPdfAttachment || props.workOrder.status === 'Scheduled' }"
                :title="getSignatureButtonTitle">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16.862 3.487a2.688 2.688 0 113.798 3.798L7.21 19.736a4.5 4.5 0 01-1.889 1.13l-2.7.9.9-2.7a4.5 4.5 0 011.13-1.89l12.75-12.75z" />
                </svg>
                Collect Signature
              </button>

              <!-- Add a hidden file input -->
              <input type="file" ref="fileInput" multiple class="hidden" @change="handleImageUpload"
                accept=".jpg,.jpeg,.png,.gif,.pdf,.heic,.docx" />

              <!-- Upload Files button -->
              <button @click.prevent="$refs.fileInput.click()" v-if="!editingField.images"
                class="glossy-btn btn font-bold w-full inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-lime-400 hover:bg-lime-400 hover:text-black sm:ml-2 sm:w-auto sm:text-xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Upload Files
              </button>

              <!-- Save button and upload progress when editing -->
              <div v-else class="flex flex-col w-full sm:w-auto">
                <button @click="saveField('images')"
                  class="glossy-btn btn font-bold w-full inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-green-400 hover:bg-green-400 hover:text-black sm:ml-2 sm:w-auto sm:text-xs"
                  :disabled="isUploading">
                  <svg v-if="!isUploading" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <svg v-else class="animate-spin h-3 w-3 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  {{ isUploading ? 'Uploading...' : 'Save' }}
                </button>

                <!-- Upload progress indicator -->
                <div v-if="isUploading"
                  class="mt-2 relative w-full h-1 bg-gray-700 rounded-full overflow-hidden sm:ml-2">
                  <div class="absolute left-0 top-0 h-full bg-lime-400" :style="{width: uploadProgress + '%'}"></div>
                </div>

                <!-- Upload error message -->
                <div v-if="uploadError" class="mt-1 text-xs text-red-400 sm:ml-2">
                  {{ uploadError }}
                </div>

                <!-- Files selected indicator -->
                <div v-if="form.images && form.images.length && !isUploading"
                  class="mt-1 text-xs text-gray-400 sm:ml-2">
                  {{ form.images.length }} file(s) selected
                </div>
              </div>

              <!-- Update Status button -->
              <button @click="updateStatus"
                class="glossy-btn btn w-full inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-indigo-400 font-bold hover:bg-indigo-400 hover:text-black sm:ml-2 sm:w-auto sm:text-xs">
                Update Status
              </button>

              <!-- Delete button -->
              <button @click="deleteWorkOrder"
                class="glossy-btn btn mt-3 w-full inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-red-400 font-bold hover:bg-red-400 hover:text-black sm:mt-0 sm:ml-2 sm:w-auto sm:text-xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete
              </button>
            </div>
          </div>

          <!-- Preview Modal -->
          <div v-if="previewAttachment" class="fixed inset-0 z-50 flex items-center justify-center">
            <div @click="closePreview" class="absolute inset-0 bg-black bg-opacity-75"></div>
            <div class="relative z-10 max-w-4xl w-full bg-gray-900 rounded-lg overflow-hidden">
              <!-- Only show header for images, since PdfViewer has its own header -->
              <div v-if="previewMode !== 'pdf'" class="p-4 border-b border-gray-800 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-200">{{ getFileName(previewAttachment) }}</h3>
                <button @click="closePreview" class="text-gray-500 hover:text-gray-400">
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <div class="relative">
                <!-- Image preview -->
                <template v-if="previewMode === 'image'">
                  <img :src="typeof previewAttachment === 'string' ? previewAttachment : previewAttachment.url"
                    class="max-w-full h-auto" alt="Image preview" />
                </template>

                <!-- PDF preview with viewer -->
                <template v-else-if="previewMode === 'pdf'">
                  <PdfViewer :pdfUrl="typeof previewAttachment === 'string' ? previewAttachment : previewAttachment.url"
                    :title="getFileName(previewAttachment)" :workOrderId="props.workOrder.id"
                    :workOrderTitle="props.workOrder.title" :editable="props.workOrder.status !== 'Scheduled'"
                    :redirectAfterUpload="false" @close="closePreview" @document-uploaded="handleDocumentUpload"
                    class="w-full h-[80vh]" />
                </template>

                <!-- Generic file preview -->
                <template v-else>
                  <div class="p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-4 text-gray-300">{{ getFileName(previewAttachment) }}</p>
                    <a :href="typeof previewAttachment === 'string' ? previewAttachment : previewAttachment.url"
                      download class="mt-4 inline-block px-4 py-2 bg-gray-800 text-blue-400 rounded hover:bg-gray-700">
                      Download File
                    </a>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Timeline Modal (positioned on the left side) -->
      <div v-if="showTimelineModal" class="fixed inset-0 flex items-start justify-start z-50 pointer-events-none">
        <div class="flex w-full mt-60 pointer-events-none">
          <!-- Timeline modal container -->
          <div class="w-[535px] flex-shrink-0 pointer-events-auto ml-8">
            <div
              class="relative z-60 w-full h-[70vh] rounded-lg overflow-hidden shadow-xl transform transition-all glossy-card flex flex-col pointer-events-auto">
              <!-- Timeline Header -->
              <div class="glossy-header p-4 border-b border-gray-700">
                <div class="flex items-center justify-between">
                  <h2 class="text-lg font-semibold text-lime-400">{{ form.title }} - Activity</h2>
                  <button @click="toggleTimelineModal"
                    class="glossy-btn btn text-red-400 font-bold hover:bg-red-600 hover:text-blackz-50 relative">
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

      <!-- Messenger Modal (positioned on the right side) -->
      <div v-if="showMessengerModal" class="fixed top-0 right-0 z-50 pointer-events-none">
        <div class="mt-60 mr-8 pointer-events-none">
          <!-- Messenger modal container -->
          <div class="w-[535px] pointer-events-auto">
            <div
              class="relative z-60 w-full h-[70vh] rounded-lg overflow-hidden shadow-xl transform transition-all glossy-card flex flex-col pointer-events-auto">
              <!-- Messenger Header -->
              <div class="glossy-header p-4 border-b border-gray-700">
                <div class="flex items-center justify-between">
                  <h2 class="text-lg font-semibold text-lime-400">{{ form.title }} - Messages</h2>
                  <button @click="toggleMessengerModal"
                    class="glossy-btn btn text-red-400 font-bold hover:bg-red-600 hover:text-black z-50 relative">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Messenger Content -->
              <div class="flex-1 overflow-y-auto p-3 max-w-full">
                <Messenger class="max-w-full" :workOrderId="props.workOrder.id" :userId="props.workOrder.user_id"
                  :initialNotes="props.workOrder.notes || []" :currentUserId="props.workOrder.user_id"
                  :getUserName="getUserName" :getUserAvatar="getUserAvatar" :users="props.users" />
              </div>

              <!-- Messenger Footer -->
              <div class="glossy-footer p-3 border-t border-gray-700">
                <!-- You can add footer content or buttons here -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue';
import Messenger from '@/Components/Messenger.vue';
import PdfViewer from '@/Components/PdfViewer.vue';
import PdfThumbnail from '@/Components/PdfThumbnail.vue';
import ToastContainer from '@/Components/ToastContainer.vue';
import Timeline from '@/Components/Timeline.vue';
import { Badge } from '@/Components/ui/badge';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';
import format from 'date-fns/format';

// Format currency utility (since @/Utils/formatCurrency does not exist)
function formatCurrency(value) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
}

// Format date utility for displaying work order date/time
function formatDate(date) {
  if (!date) return '';
  try {
    return format(new Date(date), 'PPpp'); // e.g., Jun 12, 2025, 10:00 AM
  } catch (e) {
    return date;
  }
}

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
        
        // Show success toast notification
        showSuccess(`${form.value.images.length} file(s) uploaded successfully`);
        uploadError.value = null;
        
        // Recognize and highlight PDFs for signature opportunities
        const pdfFiles = form.value.images.filter(file => 
          file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')
        );
        
        if (pdfFiles.length > 0 && props.workOrder.status !== 'Scheduled') {
          showInfo(`${pdfFiles.length} PDF document(s) ready for signatures`);
        }
      }
      
      return; // Skip the regular field update since we've handled the upload
    } catch (error) {
      console.error('Error uploading images:', error);
      const errorMessage = error.response?.data?.message || 'Failed to upload files. Please try again.';
      uploadError.value = errorMessage;
      isUploading.value = false;
      
      // Show error toast notification
      showError(errorMessage);
      
      // If the error is related to file size, provide a more helpful message
      if (error.response?.status === 413 || error.message?.includes('payload')) {
        showWarning('The file(s) may be too large. Try uploading smaller files or one at a time.');
      }
      
      return; // Skip the rest of the function
    }
  }
  
  try {
    // Show info toast notification for critical fields
    if (['status', 'hourly_rate', 'hours', 'title', 'date_time', 'customer_id'].includes(field)) {
      showInfo(`Updating ${field.replace('_', ' ')}...`);
    }
    
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
        
        // Show success toast for financial updates
        showSuccess(`Updated financial details - New total: ${formatCurrency(grandTotal.value)}`);
      }
      // Show success toast for important field updates
      else if (['status', 'title', 'date_time', 'customer_id'].includes(field)) {
        const fieldName = field.replace('_', ' ');
        showSuccess(`Updated ${fieldName} successfully`);
      }
      
      // Refresh timeline to show the new activity
      await refreshTimeline();
    }
  } catch (error) {
    console.error(`Error saving ${field}:`, error);
    showError(`Failed to update ${field.replace('_', ' ')}: ${error.response?.data?.message || 'Please try again'}`);
    
    // Provide more specific guidance for certain fields
    if (field === 'customer_id' && error.response?.status === 404) {
      showWarning('The selected customer could not be found. Please choose a valid customer.');
    }
  }
};

// Function to update grand total
const updateGrandTotal = async () => {
  try {
    // Calculate financial metrics for logging
    const laborCost = (parseFloat(form.value.hours || 0) * parseFloat(form.value.hourly_rate || 0));
    const travelCost = form.value.has_travel ? parseFloat(form.value.travel_cost || 0) : 0;
    const calculatedTotal = laborCost + travelCost;
    
    // Make API request to update total
    const response = await axios.post(`/work-orders/${props.workOrder.id}/update-total`);
    
    if (response.data.success) {
      props.workOrder.grand_total = response.data.grand_total;
      
      // Log the breakdown for debugging
      console.log('Grand total updated:', {
        laborCost: laborCost.toFixed(2),
        hours: form.value.hours,
        hourlyRate: form.value.hourly_rate,
        travelCost: travelCost.toFixed(2),
        grandTotal: response.data.grand_total || calculatedTotal.toFixed(2)
      });
    }
  } catch (error) {
    console.error('Error updating grand total:', error);
    showError('Failed to update total cost. Please try again.');
    
    // If error is related to validation, provide more specific guidance
    if (error.response?.data?.errors) {
      const validationErrors = error.response.data.errors;
      if (validationErrors.hourly_rate) {
        showWarning('Please enter a valid hourly rate');
      }
      if (validationErrors.hours) {
        showWarning('Please enter valid hours');
      }
      if (validationErrors.travel_cost) {
        showWarning('Please enter a valid travel cost');
      }
    }
  }
};

// Modal toggle functionality
const showMessengerModal = ref(true); // Messenger modal is visible by default
const showTimelineModal = ref(true); // Timeline modal is visible by default

// Function to close the modal
const closeModal = () => {
  emit('close');
};

// Toggle functions for both modals
const toggleMessengerModal = () => {
  showMessengerModal.value = !showMessengerModal.value;
};

const toggleTimelineModal = () => {
  showTimelineModal.value = !showTimelineModal.value;
};

// Define VALID_STATUSES
const VALID_STATUSES = [
  'Scheduled',
  'In Progress',
  'Part Needed',
  'Complete',
  'Cancelled'
];

// --- Status Badge Variant Mapping ---
const statusBadgeVariant = computed(() => {
  const status = (props.workOrder.status || '').toLowerCase();
  if (status.includes('complete')) return { variant: 'default', class: 'bg-green-500 text-white border-green-600' };
  if (status.includes('scheduled')) return { variant: 'secondary', class: 'bg-blue-500 text-white border-blue-600' };
  if (status.includes('progress')) return { variant: 'outline', class: 'bg-yellow-400 text-black border-yellow-500' };
  if (status.includes('cancel')) return { variant: 'destructive', class: 'bg-red-500 text-white border-red-600' };
  if (status.includes('part') || status.includes('return')) return { variant: 'outline', class: 'bg-purple-500 text-white border-purple-600' };
  return { variant: 'outline', class: 'bg-gray-400 text-black border-gray-500' };
});

// Computed: is any field being edited?
const isAnyFieldBeingEdited = computed(() => {
  return Object.values(editingField.value).some(Boolean);
});

// Dummy hasChanges computed property to prevent Vue warning
const hasChanges = computed(() => false);

// Dummy refreshTimeline function to prevent ReferenceError
async function refreshTimeline() {
  // No-op for now
}

// --- Toast notification system (renamed to avoid shadowing) ---
const { toasts, success: toastSuccess, error: toastError, warning: toastWarning, info: toastInfo } = useToast();

const showSuccess = (message) => {
  toastSuccess(message);
};
const showError = (message) => {
  toastError(message);
};
const showWarning = (message) => {
  toastWarning(message);
};
const showInfo = (message) => {
  toastInfo(message);
};

// Function to update status
function updateStatus() {
  // Only allow editing if not already editing
  if (!editingField.value.status) {
    editingField.value.status = true;
    nextTick(() => {
      // Optionally focus the select dropdown
      const select = document.querySelector('select[v-model="form.status"]');
      if (select) select.focus();
    });
  }
}

// --- Upload state refs ---
const isUploading = ref(false);
const uploadProgress = ref(0);
const uploadError = ref(null);

// --- Preview modal state ---
const previewAttachment = ref(null);
const previewMode = ref('image'); // 'image', 'pdf', or 'file'

// --- Signature button title ---
const getSignatureButtonTitle = computed(() => {
  if (!hasPdfAttachment.value) return 'No PDF attachments available for signature.';
  if (props.workOrder.status === 'Scheduled') return 'Signature collection is not available for Scheduled work orders.';
  return 'Collect signature on PDF attachments';
});

// --- User name/avatar helpers for Messenger ---
function getUserName(userId) {
  const user = props.users?.find(u => u.id === userId);
  return user ? user.name : 'Unknown User';
}
function getUserAvatar(userId) {
  const user = props.users?.find(u => u.id === userId);
  return user ? user.avatar_url || user.profile_photo_url || '' : '';
}

// Helper: isPdfFile
function isPdfFile(attachment) {
  if (!attachment) return false;
  const name = attachment.file_name || attachment.name || attachment.url || '';
  return (
    (attachment.file_type && attachment.file_type.includes('pdf')) ||
    name.toLowerCase().endsWith('.pdf')
  );
}

// Helper: Get filename from attachment
function getFileName(attachment) {
  if (!attachment) return 'Unknown file';
  if (typeof attachment === 'string') {
    // If attachment is a URL string, extract the filename
    return attachment.split('/').pop() || 'Unknown file';
  }
  // Return filename from various possible attachment object structures
  return attachment.file_name || attachment.name || attachment.url?.split('/').pop() || 'Unknown file';
}

// Helper: isImageFile for attachment type checking
function isImageFile(attachment) {
  if (!attachment) return false;
  
  // Check if attachment has file_type property
  if (attachment.file_type) {
    return attachment.file_type.startsWith('image/');
  }
  
  // Check file name extension as fallback
  const name = attachment.file_name || attachment.name || attachment.url || '';
  const ext = name.toLowerCase().split('.').pop();
  return ['jpg', 'jpeg', 'png', 'gif', 'heic'].includes(ext);
}

// Add missing hasPdfAttachment computed property
const hasPdfAttachment = computed(() => {
  const attachments = props.workOrder?.attachments || [];
  return attachments.some(att => isPdfFile(att));
});

// Computed: totalAmount for display
const totalAmount = computed(() => {
  const hours = parseFloat(form.value.hours || 0);
  const rate = parseFloat(form.value.hourly_rate || 0);
  const travel = form.value.has_travel ? parseFloat(form.value.travel_cost || 0) : 0;
  return (hours * rate) + travel;
});

// Method: getAllAttachments for template usage
function getAllAttachments() {
  return props.workOrder?.attachments || [];
}
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

.pdf-preview-container {
  transition: all 0.2s ease-in-out;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.pdf-preview-container:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
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

/* Signed PDF indicator styles */
.pdf-signed-indicator {
  position: absolute;
  top: 8px;
  right: 8px;
  background-color: rgba(34, 197, 94, 0.9);
  color: white;
  border-radius: 9999px;
  padding: 2px 6px;
  font-size: 0.7rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 2px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  z-index: 10;
}

.pdf-signature-timestamp {
  font-size: 0.65rem;
  color: rgba(255, 255, 255, 0.8);
}
</style>
