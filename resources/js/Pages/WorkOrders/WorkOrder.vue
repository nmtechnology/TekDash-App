<template>
  <!-- Removed ToastContainer from here; it should only be rendered at the app root (AppLayout.vue) -->
  <div v-if="props.showModal">
    <!-- Background overlay -->
    <div @click="emit('close')" class="fixed inset-0 bg-black bg-opacity-50 z-[60]"></div>

    <!-- Work Order Modal (center position, slightly narrower) -->
    <div class="fixed inset-0 flex items-start justify-center z-[70] pointer-events-none">
      <div
        class="relative z-[80] w-full max-w-3xl h-[70vh] mt-60 rounded-lg overflow-hidden shadow-xl transform transition-all glossy-card pointer-events-auto flex flex-col">
        <!-- Header section -->
        <div class="glossy-header p-4 border-b border-gray-700">
          <div class="flex items-center justify-between">
            <div class="flex-grow relative group">
              <div class="flex items-center">
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
                
                <!-- Status Badge - moved from content section -->
                <div class="ml-3">
                  <template v-if="!editingField.status">
                    <Badge :variant="statusBadgeVariant.variant"
                      :class="statusBadgeVariant.class + ' cursor-pointer ring-1 ring-inset'" @click="updateStatus">
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
              </div>
            </div>
            <div class="flex space-x-2 items-center">
              <button v-if="isAnyFieldBeingEdited" @click="saveAllChanges"
                class="glossy-btn btn inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-amber-400 font-bold hover:bg-amber-400 hover:text-black z-50 relative mr-2"
                :class="{ 'animate-pulse': hasChanges }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                Save All Changes
              </button>
              <!-- Print Work Order button -->
              <button type="button" @click.prevent="showPrintOptionsModal = true; console.log('Opening print modal')"
                class="glossy-btn btn inline-flex justify-center rounded-md border shadow-sm px-3 py-1.5 text-purple-400 font-bold hover:bg-purple-400 hover:text-black z-50 relative cursor-pointer"
                title="Print or download the work order">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v-4a2 2 0 002-2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
              </button>
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
          <!-- Work Order Creation Time -->
          <div class="mb-4 flex flex-wrap justify-end items-center">
            <div class="text-gray-300 text-sm">
              Created: {{ formatDate(props.workOrder.created_at) }}
              <span v-if="props.workOrder.visit_number" class="ml-2 text-indigo-400">Visit #{{ props.workOrder.visit_number }}</span>
            </div>
          </div>

          <!-- Work Order Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="space-y-4">
              <!-- Scheduled Date (Calendar Picker) -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Scheduled Date</label>
                <div v-if="!editingField.date_time">
                  <Button variant="outline" class="w-full justify-start text-left font-normal"
                    @click="startEditing('date_time')">
                    <CalendarIcon class="mr-2 h-5 w-5 text-indigo-400" />
                    <span v-if="calendarValue">
                      {{ calendarDateFormatter.format(calendarValue.toDate(getLocalTimeZone())) }}
                    </span>
                    <span v-else class="text-gray-400">Pick a date</span>
                  </Button>
                </div>
                <div v-else>
                  <Popover open>
                    <PopoverTrigger as-child>
                      <Button variant="outline" class="w-full justify-start text-left font-normal">
                        <CalendarIcon class="mr-2 h-5 w-5 text-indigo-400" />
                        <span v-if="calendarValue">
                          {{ calendarDateFormatter.format(calendarValue.toDate(getLocalTimeZone())) }}
                        </span>
                        <span v-else class="text-gray-400">Pick a date</span>
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-auto p-0">
                      <RangeCalendar v-model="calendarValue" :number-of-months="2" :initial-focus="true"
                        @update:start-value="handleCalendarSelect" />
                      <div class="flex justify-end mt-2">
                        <Button size="sm" variant="ghost" @click="editingField.date_time = false">Cancel</Button>
                      </div>
                    </PopoverContent>
                  </Popover>
                </div>
              </div>

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
                  {{ form.address || props.workOrder.address || 'No address provided' }}
                </div>
                <div v-else class="mt-1">
                  <input type="text" v-model="form.address" @blur="saveField('address')"
                    class="block w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                </div>
                <!-- Mapbox Static Map Preview -->
                <div v-if="mapboxImageUrl" class="mt-2">
                  <a :href="mapboxMapsLink" target="_blank" rel="noopener" title="Open in Map App">
                    <img :src="mapboxImageUrl" alt="Map snapshot"
                      class="rounded-lg shadow-md border border-gray-700 hover:opacity-90 transition-opacity cursor-pointer"
                      style="width: 100%; max-width: 600px; min-height: 120px; background: #222;" />
                  </a>
                  <div v-if="mapboxLoading" class="text-xs text-gray-400 mt-1">Loading map...</div>
                  <div v-if="mapboxError" class="text-xs text-red-400 mt-1">{{ mapboxError }}</div>
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
              <template v-for="attachment in getAllAttachments()" :key="attachment.id || attachment.file_name || attachment.path || attachment.url || attachment">
                <!-- Image Preview -->
                <div v-if="isImageFile(attachment)" @click="handlePreviewAttachment(attachment)"
                  class="cursor-pointer relative group">
                  <img :src="getAttachmentUrl(attachment)" class="w-full h-32 object-cover rounded-lg" />
                  <div
                    class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <span class="text-white">Preview</span>
                  </div>
                </div>

                <!-- PDF Preview -->
                <div v-else-if="isPdfFile(attachment)" class="cursor-pointer">
                  <template v-if="attachment && getAttachmentUrl(attachment) && !pdfPreviewErrorMap[attachment.id || attachment.file_name || attachment.path || attachment.url || 'unknown']">
                    <PdfThumbnail
                      :pdfUrl="getAttachmentUrl(attachment)"
                      @error="setPdfPreviewError(attachment.id || attachment.file_name || attachment.path || attachment.url || 'unknown')"
                    />
                  </template>
                  <template v-else>
                    <div class="flex flex-col items-center justify-center h-32 w-full bg-gray-800 rounded-lg">
                      <svg class="h-8 w-8 text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      <span class="text-xs text-gray-400 mt-2">PDF Preview Unavailable</span>
                    </div>
                  </template>
                </div>
              </template>
            </div>
          </div>

          <!-- Messaging Section Button -->
          <div>
            <!-- <button @click="toggleMessengerModal"
              class="btn bg-gray-800 hover:bg-gray-700 text-lime-400 border border-gray-700 rounded-lg px-4 py-2 flex items-center gap-2 shadow-lg transition-all duration-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
              Open Messages
            </button> -->
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
              <div class="absolute left-0 top-0 h-full bg-lime-400" :style="{ width: uploadProgress + '%' }"></div>
            </div>
          </div>

          <!-- Footer buttons -->
          <div class="sm:flex sm:justify-between">
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
              <button @click="duplicateWorkOrder($event)"
                :disabled="!canDuplicate"
                :class="[
                  'glossy-btn btn w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-3 py-1.5 text-purple-400 font-bold hover:bg-purple-400 hover:text-black sm:ml-2 sm:w-auto sm:text-xs',
                  { 'opacity-50 cursor-not-allowed': !canDuplicate }
                ]"
                :title="canDuplicate ? 'Create a duplicate work order' : 'Cannot duplicate archived work orders'">
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
                :disabled="!canCollectSignature"
                :class="{ 'opacity-50 cursor-not-allowed hover:bg-transparent hover:text-indigo-400': !canCollectSignature }"
                :title="signatureButtonTitle">
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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="http://www.w3.org/2000/svg" stroke="currentColor">
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
                    viewBox="http://www.w3.org/2000/svg">
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
                  <div class="absolute left-0 top-0 h-full bg-lime-400" :style="{ width: uploadProgress + '%' }"></div>
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
          <div v-if="previewAttachment" class="fixed inset-0 z-[90] flex items-center justify-center">
            <div @click="closePreview" class="absolute inset-0 bg-black bg-opacity-75 z-[91]"></div>
            <div class="relative z-[92] max-w-4xl w-full bg-gray-900 rounded-lg overflow-hidden">
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
                <template v-if="previewMode === 'pdf'">
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
      <div v-if="showTimelineModal" class="fixed inset-0 flex items-start justify-start z-[70] pointer-events-none">
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
      <div v-if="showMessengerModal" class="fixed top-0 right-0 z-[70] pointer-events-none">
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
                <Messenger class="max-w-full"
                  :workOrderId="props.workOrder.id"
                  :userId="currentUserId"
                  :initialNotes="props.workOrder.notes || []"
                  :currentUserId="currentUserId"
                  :getUserName="getUserName"
                  :getUserAvatar="getUserAvatar"
                  :currentUserAvatar="currentUserAvatar"
                  :users="props.users"
                />
              </div>

              <!-- Messenger Footer -->
              <div class="glossy-footer p-3 border-t border-gray-700">
                <!-- You can add footer content or buttons here -->
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Print Options Modal -->
      <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100"
        leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-show="showPrintOptionsModal"
          class="fixed inset-0 z-[3000] overflow-y-auto flex items-center justify-center">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-black bg-opacity-70 transition-opacity pointer-events-auto" 
            @click.prevent="showPrintOptionsModal = false; console.log('Backdrop click - closing modal')">
          </div>

          <!-- Modal container -->
          <div class="relative z-[3001] bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-auto p-6 pointer-events-auto">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xl font-bold text-lime-400">Work Order Output</h3>
              <button type="button" @click.prevent="showPrintOptionsModal = false; console.log('Close button clicked')" 
                class="text-red-400 hover:text-red-500 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <p class="text-gray-300 mb-6">Choose a format and action for your work order:</p>

            <div class="space-y-4">
              <!-- Cost Breakdown Option -->
              <div class="w-full mb-4 bg-gray-700 rounded-lg overflow-hidden">
                <div class="p-4 flex items-center">
                  <div class="bg-purple-600 p-2 rounded-lg mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.736 6.979C9.208 6.193 9.696 6 10 6c.304 0 .792.193 1.264.979a1 1 0 001.715-1.029C12.279 4.784 11.232 4 10 4s-2.279.784-2.979 1.95c-.285.475-.507 1-.67 1.55H6a1 1 0 000 2h.013a9.358 9.358 0 000 1H6a1 1 0 100 2h.351c.163.55.385 1.075.67 1.55C7.721 15.216 8.768 16 10 16s2.279-.784 2.979-1.95a1 1 0 10-1.715-1.029c-.472.786-.96.979-1.264.979-.304 0-.792-.193-1.264-.979a4.265 4.265 0 01-.264-.521H10a1 1 0 100-2H8.017a7.36 7.36 0 010-1H10a1 1 0 100-2H8.472c.08-.185.167-.36.264-.521z"
                        clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="text-left">
                    <h4 class="text-white font-bold">Cost Breakdown Version</h4>
                    <p class="text-sm text-gray-400">Includes all cost details and pricing</p>
                  </div>
                </div>

                <div class="flex border-t border-gray-800">
                  <button type="button" @click.prevent="printWorkOrder('cost'); showPrintOptionsModal = false; console.log('Print cost clicked')"
                    class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 transition-colors duration-200 text-center flex items-center justify-center cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                      viewBox="http://www.w3.org/2000/svg" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v-4a2 2 0 002-2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Print</span>
                  </button>
                  <div class="border-r border-gray-800"></div>
                  <button type="button" @click.prevent="generatePDF('cost'); showPrintOptionsModal = false; console.log('Download PDF cost clicked')"
                    class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 transition-colors duration-200 text-center flex items-center justify-center cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download PDF</span>
                  </button>
                </div>
              </div>

              <!-- Signature Option -->
              <div class="w-full mb-4 bg-gray-700 rounded-lg overflow-hidden">
                <div class="p-4 flex items-center">
                  <div class="bg-blue-600 p-2 rounded-lg mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="http://www.w3.org/2000/svg">
                      <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                      <path fill-rule="evenodd"
                        d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                        clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="text-left">
                    <h4 class="text-white font-bold">Signature Version</h4>
                    <p class="text-sm text-gray-400">Includes notes area and signature line</p>
                  </div>
                </div>

                <div class="flex border-t border-gray-800">
                  <button type="button" @click.prevent="printWorkOrder('signature'); showPrintOptionsModal = false; console.log('Print signature clicked')"
                    class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 transition-colors duration-200 text-center flex items-center justify-center cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v-4a2 2 0 002-2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Print</span>
                  </button>
                  <div class="border-r border-gray-800"></div>
                  <button type="button" @click.prevent="generatePDF('signature'); showPrintOptionsModal = false; console.log('Download PDF signature clicked')"
                    class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 transition-colors duration-200 text-center flex items-center justify-center cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download PDF</span>
                  </button>
                </div>
              </div>

              <!-- Parts Label Option -->
              <div class="w-full mb-4 bg-gray-700 rounded-lg overflow-hidden">
                <div class="p-4 flex items-center">
                  <div class="bg-green-600 p-2 rounded-lg mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd"
                        d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z"
                        clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="text-left">
                    <h4 class="text-white font-bold">Parts Label</h4>
                    <p class="text-sm text-gray-400">Compact QR code label for tagging materials</p>
                  </div>
                </div>

                <div class="flex border-t border-gray-800">
                  <button type="button" @click.prevent="printWorkOrder('label'); showPrintOptionsModal = false; console.log('Print label clicked')"
                    class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 transition-colors duration-200 text-center flex items-center justify-center cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v-4a2 2 0 002-2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Print</span>
                  </button>
                  <div class="border-r border-gray-800"></div>
                  <button type="button" @click.prevent="generatePDF('label'); showPrintOptionsModal = false; console.log('Download PDF label clicked')"
                    class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 transition-colors duration-200 text-center flex items-center justify-center cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download PDF</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue';
import Messenger from '@/Components/Messenger.vue';
import PdfViewer from '@/Components/PdfViewer.vue';
import PdfThumbnail from '@/Components/PdfThumbnail.vue';
import Timeline from '@/Components/Timeline.vue';
import { Badge } from '@/Components/ui/badge';
import { useToast } from '@/Composables/useToast';
import { getAttachmentUrl, isPdfFile, isImageFile, getFileName, processAttachments } from '@/helpers/attachmentHelpers';
import axios from 'axios';
import format from 'date-fns/format';
import { CalendarDate, DateFormatter, getLocalTimeZone } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Button } from '@/Components/ui/button';
import { RangeCalendar } from '@/Components/ui/range-calendar';
import { usePage } from '@inertiajs/vue3';
import QRCode from 'qrcode'; // For JavaScript library
import html2pdf from 'html2pdf.js/dist/html2pdf.bundle.min.js';

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

// Create a local computed property that ensures attachments is always an array
const safeWorkOrder = computed(() => {
  if (!props.workOrder) return { attachments: [] };
  
  return {
    ...props.workOrder,
    attachments: Array.isArray(props.workOrder.attachments) ? props.workOrder.attachments : []
  };
});

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

// Add a ref for grandTotal to display in success messages
const grandTotal = ref(props.workOrder?.grand_total || 0);

// Print functionality variables
const showPrintOptionsModal = ref(false);
const isLoading = ref(false);

// QR Code value for this work order
const qrCodeValue = computed(() => {
  // Create a direct URL to the work order page when scanned
  return window.location.origin + '/work-orders/' + props.workOrder.id;
});

// Utility to replace modern CSS color functions with a fallback (for PDF/print compatibility)
function replaceModernCssColors(str) {
  if (!str) return str;
  // Replace oklch(), lab(), lch(), color() with #222 (dark gray) as a fallback
  return str
    .replace(/oklch\([^)]*\)/gi, '#222')
    .replace(/lab\([^)]*\)/gi, '#222')
    .replace(/lch\([^)]*\)/gi, '#222')
    .replace(/color\([^)]*\)/gi, '#222');
}

// Helper function to escape HTML content for safety
const escapeHtml = (unsafe) => {
  if (unsafe === undefined || unsafe === null) return '';
  return String(unsafe)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
};

// Function to generate work order content (used by both print and PDF methods)
const generateWorkOrderContent = async (mode = 'cost', forPdf = false) => {
  // First, generate QR code as a data URL
  const qrValue = qrCodeValue.value;
  let qrImageUrl = '';

  try {
    // Generate QR code directly to data URL using the QRCode library
    qrImageUrl = await QRCode.toDataURL(qrValue, {
      margin: 1,
      errorCorrectionLevel: 'M',
      type: 'image/png',
      quality: 0.92,
      width: forPdf ? 600 : 300 // Higher resolution for PDF
    });
    console.log('QR code generated successfully');
  } catch (e) {
    console.error('Error creating QR code data URL:', e);
    qrImageUrl = ''; // Empty if failed
  }

  // Get customer and technician data
  const customerName = props.workOrder?.customer?.name || props.workOrder?.customer?.business_name || 'Not specified';
  const technicianName = props.workOrder?.technician?.name || 
                       (props.workOrder?.technician?.first_name && props.workOrder?.technician?.last_name ? 
                        `${props.workOrder.technician.first_name} ${props.workOrder.technician.last_name}` : 
                        'Not specified');

  // Calculate costs for display
  const hourlyRate = props.workOrder.hourly_rate || 0;
  const hours = props.workOrder.hours || 0;
  const laborCost = hourlyRate * hours;
  const travelCost = props.workOrder.travel_cost || 0;
  const totalPrice = laborCost + travelCost;

  // Generate PDF filename based on work order title and current date
  const filename = `${escapeHtml(props.workOrder.title || 'WorkOrder').replace(/\s+/g, '_')}_${mode === 'cost' ? 'Cost' :
      mode === 'signature' ? 'Signature' :
        'Label'
    }_${new Date().toISOString().split('T')[0]}.pdf`;

  // Create the print content with styling
  const htmlContent = `
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>${escapeHtml(props.workOrder.title) || 'Work Order'} - ${mode === 'cost' ? 'Cost Breakdown' :
      mode === 'signature' ? 'Signature Version' :
        'Parts Label'
    }</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          margin: 0;
          padding: 20px;
          color: #333;
        }
        .header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 20px;
          padding-bottom: 10px;
          border-bottom: 2px solid #4ade80;
        }
        .title {
          font-size: 24px;
          font-weight: bold;
          color: #1f2937;
        }
        .status {
          display: inline-block;
          background-color: #4ade80;
          padding: 5px 10px;
          border-radius: 4px;
          font-weight: bold;
          color: white;
        }
        .section {
          margin-bottom: 20px;
          padding: 15px;
          background-color: #f9fafb;
          border-radius: 8px;
          border-left: 4px solid #4ade80;
          page-break-inside: avoid;
        }
        .section-title {
          font-size: 18px;
          font-weight: bold;
          margin-bottom: 10px;
          color: #1f2937;
        }
        .grid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 15px;
        }
        .label {
          font-size: 14px;
          font-weight: bold;
          color: #4b5563;
          margin-bottom: 4px;
        }
        .value {
          font-size: 16px;
          color: #1f2937;
        }
        .box {
          border: 1px solid #d1d5db;
          border-radius: 4px;
          padding: 10px;
          background-color: #f9fafb;
        }
        .cost-item {
          display: flex;
          justify-content: space-between;
          margin-bottom: 8px;
          padding-bottom: 8px;
          border-bottom: 1px solid #e5e7eb;
        }
        .cost-item:last-child {
          border-bottom: none;
        }
        .cost-label {
          font-size: 14px;
          color: #4b5563;
        }
        .cost-value {
          font-size: 14px;
          font-weight: bold;
          color: #1f2937;
        }
        .total-row {
          font-size: 16px;
          font-weight: bold;
          color: #1f2937;
          border-top: 2px solid #d1d5db;
          padding-top: 8px;
        }
        .qr-container {
          text-align: right;
          margin-bottom: 20px;
        }
        .company-logo-container {
          display: flex;
          flex-direction: column;
        }
        .company-name {
          font-size: 18px;
          font-weight: bold;
          color: #4ade80;
        }
        .company-details {
          font-size: 12px;
          color: #6b7280;
          margin-top: 4px;
        }
        @media print {
          body {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
          }
          .section {
            break-inside: avoid;
          }
          .page-break {
            page-break-before: always;
          }
        }
        
        /* Label-specific styles */
        .label-container {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          width: 100%;
          height: 100vh;
          padding: 20px;
          box-sizing: border-box;
        }
        .label-qrcode {
          width: 130px;
          height: 130px;
          margin: 0 auto 20px;
          display: block;
        }
        .label-title {
          font-size: 18px;
          font-weight: bold;
          text-align: center;
          margin-bottom: 10px;
        }
        .label-info {
          font-size: 14px;
          text-align: center;
          margin-bottom: 5px;
        }
        
        /* Signature area styling */
        .signature-area {
          margin-top: 30px;
          padding: 20px;
          border: 1px solid #e5e7eb;
          border-radius: 4px;
          margin-bottom: 15px;
          padding: 10px;
        }
        .signature-line {
          display: flex;
          justify-content: space-between;
        }
        .signature-field {
          flex: 1;
          max-width: 45%;
          text-align: center;
        }
        .signature-label {
          font-size: 12px;
          color: #6b7280;
          margin-top: 5px;
        }
      </style>
    </head>
    <body>
      ${mode === 'label' ? `
      <div class="label-container">
        ${qrImageUrl ?
        `<img src="${qrImageUrl}" class="label-qrcode" alt="QR Code">` :
        `<div style="width: 200px; height: 200px; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; text-align: center; font-size: 12px;">QR Code<br>Not Available</div>`
      }
        <div class="label-title">${escapeHtml(props.workOrder.title) || 'Work Order'}</div>
        <div class="label-info">NM Technology</div>
        <div class="label-info">${escapeHtml(props.workOrder.status) || 'Not set'}</div>
        <div class="label-info">${props.workOrder.date_time ? 'Date: ' + formatDate(props.workOrder.date_time) : ''}</div>
      </div>
      ` : `
      <!-- Standard header for full work order versions -->
      <div class="header">
        <div class="company-logo-container">
          <!-- Company Logo -->
          <div style="display: flex; align-items: center;">
            <img src="https://www.nmtechnology.us/build/assets/nm-logo-rmbg-f8bd446d.webp" alt="NM Technology Logo" style="width: 65px; height: 35px; margin-right: 10px;">
            <div class="company-name">Technology</div>
          </div>
          <div style="margin-top: 10px;">
            <div class="company-details">Network Management Technology, Inc.</div>
            <div class="company-details">9227 Haven Avenue, Suite 360, Rancho Cucamonga, CA 91730</div>
            <div class="company-details">Phone: (909) 257-7278 | Email: service@nmtechnology.us</div>
          </div>
        </div>
        
        <div class="qr-container">
          ${qrImageUrl ?
      `<img src="${qrImageUrl}" width="150" height="150" alt="QR Code">` :
      `<div style="width: 180px; height: 180px; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; margin-left: auto; text-align: center; font-size: 12px;">QR Code<br>Not Available</div>`
    }
            <div style="margin-top: 5px; font-size: 12px; text-align: center;">
              Scan for Work Order details
            </div>
          </div>
      </div>
      `}

      ${mode !== 'label' ? `
      <div class="header">
        <div>
          <div class="title">Work Order: ${escapeHtml(props.workOrder.title) || 'No Title'}</div>
          <div style="font-size: 14px; color: #6b7280;">${escapeHtml(formatDate(props.workOrder.date_time)) || 'Not scheduled'}</div>
        </div>
        <div>
          <div class="status">${escapeHtml(props.workOrder.status) || 'Not set'}</div>
        </div>
      </div>

      <!-- Customer & Technician Section -->
      <div class="section">
        <div class="section-title">Customer & Technician</div>
        <div class="grid">
          <div>
            <div class="label">Customer:</div>
            <div class="value">${escapeHtml(customerName)}</div>
          </div>
          <div>
            <div class="label">Technician:</div>
            <div class="value">${escapeHtml(technicianName)}</div>
          </div>
        </div>
      </div>

      <!-- Work Order Details Section -->
      <div class="section">
        <div class="section-title">Work Order Details</div>
        <div>
          <div class="label">Description:</div>
          <div class="value">${escapeHtml(props.workOrder.description) || 'No description provided'}</div>
        </div>
        ${props.workOrder.address ? `
        <div style="margin-top: 15px;">
          <div class="label">Location:</div>
          <div class="value">${escapeHtml(props.workOrder.address)}</div>
        </div>
        ` : ''}
      </div>

      ${mode === 'cost' ? `
      <!-- Cost Section -->
      <div class="section">
        <div class="section-title">Cost Breakdown</div>
        <div class="cost-item">
          <div class="cost-label">Labor (${hours} hours @ $${hourlyRate}/hour)</div>
          <div class="cost-value">$${laborCost || 0}</div>
        </div>
        ${travelCost > 0 ? `
        <div class="cost-item">
          <div class="cost-label">Travel Cost</div>
          <div class="cost-value">$${travelCost || 0}</div>
        </div>
        ` : ''}
        <div class="cost-item total-row">
          <div class="cost-label">Total</div>
          <div class="cost-value">$${totalPrice || 0}</div>
        </div>
      </div>
      ` : ''}

      ${mode === 'signature' ? `
      <!-- Signature Section -->
      <div class="signature-area">
        <div style="font-weight: bold; margin-bottom: 15px;">Notes:</div>
        <div style="min-height: 100px; border: 1px solid #d1d5db; border-radius: 4px; padding: 10px; margin-bottom: 20px;"></div>
        
        <div class="signature-line">
          <div class="signature-field">
            <div style="border-top: 1px solid #d1d5db; padding-top: 5px;">
              <div class="signature-label">Technician Signature</div>
            </div>
          </div>
          <div class="signature-field">
            <div style="border-top: 1px solid #d1d5db; padding-top: 5px;">
              <div class="signature-label">Date: ${new Date().toLocaleDateString()}</div>
            </div>
          </div>
        </div>
        
        <div class="signature-line" style="margin-top: 30px;">
          <div class="signature-field">
            <div style="border-top: 1px solid #d1d5db; padding-top: 5px;">
              <div class="signature-label">Customer Signature</div>
            </div>
          </div>
          <div class="signature-field">
            <div style="border-top: 1px solid #d1d5db; padding-top: 5px;">
              <div class="signature-label">Date</div>
            </div>
          </div>
        </div>
      </div>
      ` : ''}
      ` : ''}
    </body>
    </html>
  `;

  return { htmlContent, filename };
};

// Function to print work order
const printWorkOrder = async (mode = 'cost') => {
  try {
    console.log('printWorkOrder function called with mode:', mode);
    
    // Create and return the HTML content and filename
    const { htmlContent: printContent } = await generateWorkOrderContent(mode);
    console.log('Generated HTML content successfully');

    // Open print window with blank target
    const printWindow = window.open('', '_blank');
    if (!printWindow) {
      console.error('Could not open print window - pop-ups may be blocked');
      alert('Please allow pop-up windows to print the work order');
      return;
    }
    console.log('Print window opened successfully');

    // Process the HTML content to replace modern CSS color functions before printing
    const safePrintContent = replaceModernCssColors(printContent);

    // Write the HTML content to the new window
    printWindow.document.open();
    printWindow.document.write(safePrintContent);
    printWindow.document.close();

    // Wait a moment for resources to load then print
    setTimeout(() => {
      printWindow.focus(); // Focus the window
      printWindow.print(); // Trigger the print dialog
    }, 500); // Small delay to ensure content loads
  } catch (error) {
    console.error('Error in printWorkOrder:', error);
    alert('There was an error printing the work order. Please try again.');
  }
};

// Generate PDF function
const generatePDF = async (mode = 'cost') => {
  try {
    console.log('generatePDF function called with mode:', mode);
    isLoading.value = true;
    
    // Generate formatted content first
    const { htmlContent, filename } = await generateWorkOrderContent(mode, true);
    console.log('Generated HTML content for PDF successfully');

    // Create a temporary container for html2pdf to work with
    const element = document.createElement('div');
    // Use safe content with all modern CSS color functions replaced
    element.innerHTML = replaceModernCssColors(htmlContent);
    console.log('Created DOM element for PDF generation');

    // Apply some safe inline styles to avoid any modern CSS that might be dynamically added
    const styleElements = element.querySelectorAll('style');
    styleElements.forEach(styleEl => {
      // Process any style tags to replace modern CSS functions
      styleEl.textContent = replaceModernCssColors(styleEl.textContent);
    });

    // Append to document but keep hidden
    document.body.appendChild(element);
    element.style.position = 'absolute';
    element.style.left = '-9999px';

    // Configure html2pdf options with optimized settings
    const options = {
      margin: 10,
      filename: filename,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: {
        scale: 2,
        useCORS: true,
        letterRendering: true,
        allowTaint: true,
        logging: false, // Disable logging
        removeContainer: true, // Clean up container after render
        backgroundColor: '#ffffff', // Ensure white background
        imageTimeout: 15000, // Increase timeout for image loading
      },
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    // Generate PDF
    await html2pdf()
      .from(element)
      .set(options)
      .save();

    // Clean up the temporary element
    document.body.removeChild(element);
    
    // Hide loading indicator
    isLoading.value = false;
  } catch (error) {
    console.error('Error in generatePDF:', error);
    alert('There was an error generating the PDF. Please try again or use the print option.');
    isLoading.value = false;
  }
};

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

// Reference for the file input field to allow clearing after upload
const fileInput = ref(null);

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
    // Only save the date_time field, no dateSelectionType logic needed
    // (If you add range/multiple in the future, add logic here)
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
      );      // Handle successful upload
      if (response.data.success || response.status === 200) {
        editingField.value.images = false;
        
        // Process attachments from the response
        let newAttachments = [];
        
        if (response.data.attachments) {
          newAttachments = Array.isArray(response.data.attachments) ? 
            response.data.attachments : [];
        } else if (response.data.workOrder && response.data.workOrder.attachments) {
          newAttachments = Array.isArray(response.data.workOrder.attachments) ? 
            response.data.workOrder.attachments : [];
        }
        
        // Process attachments for consistent format
        const processedAttachments = processAttachments(newAttachments);
        console.log('WorkOrder: Processed attachments after upload:', processedAttachments);
        
        // Create a new array to ensure Vue detects the change
        props.workOrder.attachments = [...processedAttachments];
        
        // Clear the file input for subsequent uploads
        if (fileInput.value) {
          fileInput.value.value = '';
        }

        isUploading.value = false;
        uploadProgress.value = 100;

        // Show success toast notification
        showSuccess(`${form.value.images.length} file(s) uploaded successfully`);
        uploadError.value = null;

        // Check for PDFs in the uploaded files
        const pdfFiles = form.value.images.filter(file =>
          file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')
        );

        if (pdfFiles.length > 0) {
          console.log(`WorkOrder: Detected ${pdfFiles.length} PDF files in upload`);
          
          if (props.workOrder.status !== 'Scheduled') {
            showInfo(`${pdfFiles.length} PDF document(s) ready for signatures`);
          }
          
          // Force immediate check for PDF attachments
          nextTick(() => {
            console.log('WorkOrder: Checking for PDFs after nextTick:', hasPdfAttachment.value);
          });
        }
        
        // Force a refresh of the work order data to ensure we have the latest attachments
        console.log('WorkOrder: Scheduling refresh after upload');
        setTimeout(() => {
          refreshWorkOrder().then(() => {
            // After refresh, force a check of signature button state
            nextTick(() => {
              console.log('WorkOrder: After refresh - PDF attachments:', hasPdfAttachment.value);
              console.log('WorkOrder: After refresh - Can collect signature:', canCollectSignature.value);
            });
          });
        }, 500);
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
        try {
          await updateGrandTotal();
          // Show success toast for financial updates
          showSuccess(`Updated financial details - New total: ${formatCurrency(grandTotal.value)}`);
        } catch (error) {
          console.error('Error updating grand total:', error);
          // Calculate fallback total if API call fails
          const laborCost = (parseFloat(form.value.hours || 0) * parseFloat(form.value.hourly_rate || 0));
          const travelCost = form.value.has_travel ? parseFloat(form.value.travel_cost || 0) : 0;
          const calculatedTotal = laborCost + travelCost;
          
          // Update the local grand total reference
          grandTotal.value = calculatedTotal;
          
          // Still show a success message but with calculated total
          showSuccess(`Updated financial details - New total: ${formatCurrency(calculatedTotal)}`);
        }
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
    
    // More specific error handling for financial fields
    if (['hours', 'hourly_rate', 'travel_cost', 'has_travel'].includes(field)) {
      showError(`Failed to update financial details: ${error.response?.data?.message || 'Please try again'}`);
      
      // Ensure we still have a value for grandTotal even if the update failed
      if (typeof grandTotal.value !== 'number' || isNaN(grandTotal.value)) {
        const laborCost = (parseFloat(form.value.hours || 0) * parseFloat(form.value.hourly_rate || 0));
        const travelCost = form.value.has_travel ? parseFloat(form.value.travel_cost || 0) : 0;
        grandTotal.value = laborCost + travelCost;
      }
    } else {
      showError(`Failed to update ${field.replace('_', ' ')}: ${error.response?.data?.message || 'Please try again'}`);
    }

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
      // Update both the props object and our reactive ref
      props.workOrder.grand_total = response.data.grand_total;
      grandTotal.value = response.data.grand_total;

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

// Handle document upload from PdfViewer component
const handleDocumentUpload = (data) => {
  if (data && data.success) {
    // Show success message
    showSuccess(`Document "${data.fileName}" was successfully uploaded`);
    
    // Close the preview modal after a short delay
    setTimeout(() => {
      closePreview();
      
      // If the document was signed, show an additional confirmation
      if (data.signed) {
        showSuccess(`Document was signed by ${data.signature.firstName} ${data.signature.lastName}`);
      }
      
      console.log('WorkOrder: Document upload successful, refreshing work order data...');
      
      // Make sure we properly refresh the work order attachments
      // This is a critical step to ensure the signature button works after upload
      refreshWorkOrder().then(() => {
        // Force a re-evaluation of the hasPdfAttachment computed property
        console.log('WorkOrder: Refresh complete, PDF attachments available:', hasPdfAttachment.value);
        
        if (hasPdfAttachment.value) {
          console.log('WorkOrder: PDF attachments detected after refresh');
        } else {
          console.log('WorkOrder: No PDF attachments detected after refresh, forcing additional check');
          // If still no PDFs found, try one more check with a small delay
          setTimeout(() => {
            console.log('WorkOrder: Delayed check for PDF attachments:', hasPdfAttachment.value);
          }, 500);
        }
      });
    }, 1000);
  } else {
    // Show error message if upload failed
    showError('Failed to upload document. Please try again.');
  }
};

// Function to refresh the work order data
const refreshWorkOrder = async () => {
  try {
    // Show a loading message for user feedback
    showInfo('Refreshing work order data...');
    console.log('WorkOrder: Refreshing work order data for ID:', props.workOrder.id);
    
    // Use the appropriate endpoint for fetching work order data as JSON
    const response = await axios.get(`/api/work-orders/${props.workOrder.id}`, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    // Check if response is valid JSON
    if (typeof response.data === 'string' && response.data.includes('<!DOCTYPE html>')) {
      console.error('WorkOrder: Received HTML response instead of JSON');
      // Don't clear existing attachments if we got HTML
      return;
    }
    
    console.log('WorkOrder: Refresh API response:', response.data);
    
    if (response.data) {
      // Extract and process the work order data
      const workOrderData = response.data.workOrder || response.data;
      
      // Get attachments from the response
      let newAttachments = [];
      
      if (workOrderData.attachments) {
        console.log('WorkOrder: Found attachments in response data:', workOrderData.attachments);
        newAttachments = Array.isArray(workOrderData.attachments) ? 
          workOrderData.attachments : [];
      } else if (response.data.attachments) {
        console.log('WorkOrder: Found attachments at top level of response:', response.data.attachments);
        newAttachments = Array.isArray(response.data.attachments) ? 
          response.data.attachments : [];
      } else {
        // Instead of clearing attachments when none are found, keep the existing ones
        console.log('WorkOrder: No attachments found in response, keeping existing attachments');
        newAttachments = safeWorkOrder.value.attachments || [];
      }
      
      // Only process attachments if we actually have some
      if (newAttachments && newAttachments.length > 0) {
        // Process the attachments to ensure consistent format
        const processedAttachments = processAttachments(newAttachments);
        console.log('WorkOrder: Processed attachments:', processedAttachments);
        
        if (processedAttachments && processedAttachments.length > 0) {
          // Ensure we preserve PDF detection flags from existing attachments
          const existingAttachments = props.workOrder.attachments || [];
          const existingPdfIds = new Map();
          
          // Build a map of existing PDFs by URL or name
          existingAttachments.forEach(att => {
            if (att._isPdf) {
              const key = att.url || att.file_name || att.name || att.path;
              if (key) existingPdfIds.set(key, true);
            }
          });
          
          // Mark any attachments that were previously identified as PDFs
          const enhancedAttachments = processedAttachments.map(att => {
            const key = att.url || att.file_name || att.name || att.path;
            if (key && existingPdfIds.has(key)) {
              console.log(`WorkOrder: Preserving PDF flag for ${key}`);
              att._isPdf = true;
            }
            return att;
          });
          
          // Force Vue to detect the change by creating a new array and replacing the entire property
          props.workOrder.attachments = [...enhancedAttachments];
          console.log('WorkOrder: Updated attachments array with processed attachments');
          
          // Force re-evaluation of hasPdfAttachment computed property
          nextTick(() => {
            console.log('WorkOrder: PDF detection after update:', hasPdfAttachment.value);
          });
        } else {
          // Don't overwrite existing attachments with an empty array if processing failed
          console.log('WorkOrder: Processed attachments array is empty, keeping existing attachments');
        }
      } else {
        console.log('WorkOrder: No attachments to process, keeping existing attachments');
      }
      
      // Update other properties from the response
      if (workOrderData) {
        Object.keys(workOrderData).forEach(key => {
          if (key !== 'attachments') {
            props.workOrder[key] = workOrderData[key];
          }
        });
      }
      
      // Log success
      console.log('WorkOrder: Work order data refreshed successfully');
      showSuccess('Work order data refreshed');
    }
  } catch (error) {
    console.error('WorkOrder: Error refreshing work order:', error);
    showError('Failed to refresh work order data. Please try again.');
  }
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

// Function to handle previewing attachments
function handlePreviewAttachment(attachment) {
  previewAttachment.value = attachment;
  // Determine preview mode
  if (isPdfFile(attachment)) {
    previewMode.value = 'pdf';
  } else if (isImageFile(attachment)) {
    previewMode.value = 'image';
  } else {
    previewMode.value = 'file';
  }
}

// --- Signature button enabled/disabled logic ---
const canCollectSignature = computed(() => {
  // Force access to hasPdfAttachment to ensure it's calculated
  const hasPdf = hasPdfAttachment.value;
  const status = (props.workOrder?.status || '').toLowerCase();
  
  console.log('WorkOrder: canCollectSignature evaluation:');
  console.log('WorkOrder: - Status =', status);
  console.log('WorkOrder: - hasPdfAttachment =', hasPdf);
  
  // If we don't have any PDF attachments, we can't collect signatures
  if (!hasPdf) {
    console.log('WorkOrder: No PDF attachments available for signature');
    return false;
  }
  
  // Check if the work order status allows signature collection
  // We need to be more flexible with partial string matches
  const validStatus = (
    status === 'in progress' ||
    status === 'part needed' ||
    status === 'complete' ||
    status.includes('progress') ||
    status.includes('part') ||
    status.includes('return')
  );
  
  console.log('WorkOrder: - validStatus =', validStatus);
  
  // For debugging, if we have PDFs but status is invalid, log a clear message
  if (hasPdf && !validStatus) {
    console.log(`WorkOrder: Has PDFs but status "${status}" doesn't allow signature collection`);
  }
  
  const result = hasPdf && validStatus;
  console.log('WorkOrder: - canCollectSignature final result =', result);
  
  return result;
});

const signatureButtonTitle = computed(() => {
  const status = (props.workOrder.status || '').toLowerCase();
  if (!hasPdfAttachment.value) return 'No PDF attachments available for signature.';
  if (!(
    status.includes('in progress') ||
    status.includes('part needed') ||
    status.includes('complete') ||
    status.includes('part') ||
    status.includes('return')
  )) return 'Signature collection is only available for In Progress, Part Needed, or Complete work orders.';
  return 'Collect signature on PDF attachments';
});

// --- Duplicate button enabled/disabled logic ---
const canDuplicate = computed(() => {
  // Active for all except In Progress, Complete, and Archived
  return !['In Progress', 'Complete', 'Archived'].includes(props.workOrder.status);
});

// --- Archive button enabled/disabled logic ---
const canArchive = computed(() => {
  // Only active for Complete status
  return props.workOrder.status === 'Complete';
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

// --- Watch for changes in attachments ---
watch(() => props.workOrder.attachments, (newAttachments, oldAttachments) => {
  console.log('WorkOrder: Attachments changed - re-evaluating PDF detection');
  
  if (!newAttachments || !Array.isArray(newAttachments)) {
    console.log('WorkOrder: New attachments array is invalid');
    return;
  }
  
  if (!oldAttachments || !Array.isArray(oldAttachments)) {
    console.log('WorkOrder: Old attachments array was invalid, processing new attachments');
  } else {
    console.log(`WorkOrder: Attachments changed from ${oldAttachments.length} to ${newAttachments.length}`);
  }
  
  // Force immediate check for PDFs (this will trigger hasPdfAttachment computed property)
  nextTick(() => {
    console.log('WorkOrder: PDF detection after attachments changed:', hasPdfAttachment.value);
    console.log('WorkOrder: Signature button state after attachments changed:', canCollectSignature.value);
  });
});

// --- Per-attachment PDF preview error state ---
const pdfPreviewErrorMap = ref({});
function setPdfPreviewError(key) {
  pdfPreviewErrorMap.value[key] = true;
}

// Helper functions are now imported from attachmentHelpers.js

// Method: getAllAttachments for template usage with improved debugging
function getAllAttachments() {
  try {
    // Use our safe computed property that ensures attachments is always an array
    const attachments = safeWorkOrder.value.attachments || [];
    
    // Enhanced debugging
    console.log('WorkOrder: getAllAttachments called');
    
    // Check if we have any attachments
    if (attachments.length === 0) {
      console.log('WorkOrder: No attachments found in workOrder data');
      return [];
    }
    
    // Log detailed info about attachments
    console.log(`WorkOrder: Found ${attachments.length} attachments`);
    
    // Cache a map of attachment types for debugging
    const attachmentTypes = attachments.map((att, i) => {
      const isPdf = isPdfFile(att);
      const isImg = isImageFile(att);
      const type = isPdf ? 'PDF' : isImg ? 'Image' : 'Other';
      const name = att.file_name || att.name || `attachment-${i}`;
      
      console.log(`WorkOrder: Attachment ${i} (${name}) is type ${type}`);
      if (isPdf) {
        console.log(`WorkOrder: PDF detected: ${name}`);
      }
      
      return { index: i, name, type };
    });
    
    console.log('WorkOrder: Attachment types summary:', attachmentTypes);
    
    // Process attachments to ensure consistent format
    const processed = processAttachments(attachments);
    
    // Final verification of processed attachments
    if (processed.length > 0) {
      console.log(`WorkOrder: Successfully processed ${processed.length} attachments`);
      
      // Check if we have PDFs after processing
      const pdfs = processed.filter(att => att._isPdf);
      if (pdfs.length > 0) {
        console.log(`WorkOrder: Found ${pdfs.length} PDF attachments after processing`);
      } else {
        console.log('WorkOrder: No PDF attachments found after processing');
      }
    } else {
      console.log('WorkOrder: No attachments after processing');
    }
    
    return processed;
  } catch (error) {
    console.error('WorkOrder: Error in getAllAttachments:', error);
    return []; // Return empty array on error
  }
}

// --- Mapbox Static Image and Link ---
const mapboxAccessToken = 'pk.eyJ1Ijoibm10ZWNoIiwiYSI6ImNtYndzNG0yZTB2MTQycm9yMmxrZTJiOXYifQ.teJIWClLiWUJvvacQC3EFQ'; // TODO: Replace with your real Mapbox public token
const mapboxCoords = ref({ lat: null, lon: null });
const mapboxLoading = ref(false);
const mapboxError = ref(null);

// Geocode the address to get lat/lon from Mapbox
async function geocodeAddress(address) {
  if (!address) {
    mapboxCoords.value = { lat: null, lon: null };
    return;
  }
  mapboxLoading.value = true;
  mapboxError.value = null;
  try {
    const resp = await fetch(
      `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(address)}.json?access_token=${mapboxAccessToken}`
    );
    const data = await resp.json();
    if (data.features && data.features.length > 0) {
      const [lon, lat] = data.features[0].center;
      mapboxCoords.value = { lat, lon };
    } else {
      mapboxCoords.value = { lat: null, lon: null };
      mapboxError.value = 'No location found.';
    }
  } catch (e) {
    mapboxCoords.value = { lat: null, lon: null };
    mapboxError.value = 'Error fetching map location.';
  } finally {
    mapboxLoading.value = false;
  }
}

// --- Headquarters address for travel calculation ---
const HEADQUARTERS_ADDRESS = "625 Horseshoe Trl SE, Albuquerque, New Mexico, 87123"; // <-- Set your real HQ address here

// Geocode an address to lat/lon
async function geocodeAddressToCoords(address) {
  const resp = await fetch(
    `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(address)}.json?access_token=${mapboxAccessToken}`
  );
  const data = await resp.json();
  if (data.features && data.features.length > 0) {
    const [lon, lat] = data.features[0].center;
    return { lat, lon };
  }
  throw new Error('Address not found');
}

// Get driving distance in miles using Mapbox Directions API
async function getTravelDistanceMiles(originAddress, destinationAddress) {
  try {
    const origin = await geocodeAddressToCoords(originAddress);
    const destination = await geocodeAddressToCoords(destinationAddress);
    const directionsResp = await fetch(
      `https://api.mapbox.com/directions/v5/mapbox/driving/${origin.lon},${origin.lat};${destination.lon},${destination.lat}?access_token=${mapboxAccessToken}`
    );
    const directionsData = await directionsResp.json();
    if (directionsData.routes && directionsData.routes.length > 0) {
      const route = directionsData.routes[0];
      return route.distance / 1609.34; // meters to miles
    }
  } catch (e) {
    // Optionally handle error
  }
  return 0;
}

// Watch for address changes and auto-calculate travel if needed
watch(() => form.value.address, async (newAddress) => {
  if (!newAddress) return;
  // Only run if address is not empty
  const miles = await getTravelDistanceMiles(HEADQUARTERS_ADDRESS, newAddress);
  if (miles > 25) {
    form.value.has_travel = true;
    form.value.travel_cost = parseFloat(miles.toFixed(2)); // or set travelMiles if you use that field
  } else {
    form.value.has_travel = false;
    form.value.travel_cost = 0;
  }
}, { immediate: false });

watch(() => form.value.address, (newAddress) => {
  geocodeAddress(newAddress);
}, { immediate: true });

const mapboxImageUrl = computed(() => {
  const { lat, lon } = mapboxCoords.value;
  if (!lat || !lon) return '';
  // Mapbox static image with a red pin
  return `https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/pin-s+ff0000(${lon},${lat})/${lon},${lat},16/600x200?access_token=${mapboxAccessToken}`;
});
const mapboxMapsLink = computed(() => {
  const { lat, lon } = mapboxCoords.value;
  if (!lat || !lon) return '#';
  // Mapbox web link
  return `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lon}#map=16/${lat}/${lon}`;
});

// Calendar date formatter for displaying scheduled date/time
const calendarDateFormatter = new DateFormatter('en-US', { dateStyle: 'medium', timeStyle: 'short' });
const calendarValue = ref(form.value.date_time ? new CalendarDate(
  new Date(form.value.date_time).getFullYear(),
  new Date(form.value.date_time).getMonth() + 1,
  new Date(form.value.date_time).getDate()
) : null);

watch(() => form.value.date_time, (newVal) => {
  if (newVal) {
    const d = new Date(newVal);
    calendarValue.value = new CalendarDate(d.getFullYear(), d.getMonth() + 1, d.getDate());
  }
});

function handleCalendarSelect(date) {
  if (!date) return;
  // Set to start of day, but keep time if present
  const prev = form.value.date_time ? new Date(form.value.date_time) : new Date();
  const newDate = new Date(date.year, date.month - 1, date.day, prev.getHours(), prev.getMinutes());
  form.value.date_time = newDate.toISOString();
  saveField('date_time');
  editingField.value.date_time = false;
}

// Methods
const deleteWorkOrder = async () => {
  try {
    // Show confirmation dialog
    if (!window.confirm('Are you sure you want to delete this work order? This action cannot be undone.')) {
      return;
    }

    // Send delete request to correct API endpoint
    const response = await axios.delete(`/api/work-orders/${props.workOrder.id}`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    console.log('Delete response:', response);

    if (response.status === 200 || response.data?.success) {
      showSuccess('Work order deleted successfully');
      // Wait 1 second before closing and reloading so toast is visible
      setTimeout(() => {
        emit('close');
        window.location.reload();
      }, 1000);
    } else {
      showError(response.data?.message || 'Failed to delete work order. Please try again.');
    }
  } catch (error) {
    console.error('Error deleting work order:', error);
    if (error.response?.status === 403) {
      showError('Permission denied. You may not have the required permissions to delete work orders.');
    } else if (error.response?.data?.message) {
      showError(error.response.data.message);
    } else {
      showError('Failed to delete work order. Please try again.');
    }
  }
};

const duplicateWorkOrder = async (event) => {
  try {
    event.preventDefault();
    // Prompt user for new date
    const newDate = window.prompt('Please enter a new date for the duplicated work order (MM/DD/YYYY):', 
      new Date().toLocaleDateString('en-US'));
    if (!newDate) {
      return;
    }
    // Validate the date format
    const dateRegex = /^(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])\/\d{4}$/;
    if (!dateRegex.test(newDate)) {
      showError('Please enter a valid date in MM/DD/YYYY format');
      return;
    }
    // Convert the date to ISO format for the API
    const [month, day, year] = newDate.split('/');
    const isoDate = new Date(year, month - 1, day).toISOString();

    // Determine the next visit number
    const currentVisit = props.workOrder.visit_number || 1;
    const nextVisit = currentVisit + 1;

    // Send the duplicate request with the new date and visit number
    const response = await axios.post(`/work-orders/${props.workOrder.id}/duplicate`, {
      date_time: isoDate,
      visit_number: nextVisit
    }, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    if (response.data.message) {
      showSuccess('Work order duplicated successfully with scheduled date: ' + newDate + ' (Visit #' + nextVisit + ')');
      emit('workOrderDuplicated', response.data);
      emit('close');
    }
  } catch (error) {
    console.error('Error duplicating work order:', error);
    if (error.response?.status === 403) {
      showError('Permission denied. You may not have the required permissions to duplicate work orders.');
    } else {
      showError(error.response?.data?.message || 'Failed to duplicate work order. Please try again.');
    }
  }
};

// --- Page props ---
const page = usePage();
const currentUserId = page.props.auth?.user?.id;
const currentUserAvatar = page.props.auth?.user?.profile_photo_url || '';

// Function to handle image uploads
function handleImageUpload(event) {
  const files = event.target.files;
  if (!files || files.length === 0) return;
  // Convert FileList to Array and assign to form.value.images
  form.value.images = Array.from(files);
  editingField.value.images = true;
  uploadError.value = null;
}

// Function to open the first PDF for signature collection
function getSignature() {
  // Find the first PDF attachment
  const pdf = safeWorkOrder.value.attachments.find(att => isPdfFile(att));
  if (pdf) {
    previewAttachment.value = pdf;
    previewMode.value = 'pdf';
  } else {
    showError('No PDF available for signature.');
  }
}

// --- Computed: totalAmount for display in template ---
const totalAmount = computed(() => {
  if (props.workOrder && typeof props.workOrder.grand_total !== 'undefined') {
    return props.workOrder.grand_total;
  }
  const hours = parseFloat(form.value.hours || 0);
  const rate = parseFloat(form.value.hourly_rate || 0);
  const travel = form.value.has_travel ? parseFloat(form.value.travel_cost || 0) : 0;
  return (hours * rate) + travel;
});

// --- Computed: hasPdfAttachment for signature logic ---
const hasPdfAttachment = computed(() => {
  console.log('WorkOrder: hasPdfAttachment computed property called');
  
  // Make sure we have attachments to check
  if (!safeWorkOrder.value.attachments || !Array.isArray(safeWorkOrder.value.attachments)) {
    console.log('WorkOrder: No valid attachments array found');
    return false;
  }
  
  // Log the number of attachments to check
  console.log(`WorkOrder: Checking ${safeWorkOrder.value.attachments.length} attachments for PDFs`);
  
  if (safeWorkOrder.value.attachments.length === 0) {
    console.log('WorkOrder: Attachments array is empty');
    return false;
  }
  
  // First check if any attachment has the _isPdf flag already set
  const hasPreProcessedPdf = safeWorkOrder.value.attachments.some(att => att && att._isPdf === true);
  if (hasPreProcessedPdf) {
    console.log('WorkOrder: Found attachment with _isPdf flag set to true');
    return true;
  }
  
  // Then try to detect PDFs using the isPdfFile helper
  let hasPdf = false;
  
  safeWorkOrder.value.attachments.forEach((att, index) => {
    if (!att) {
      console.log(`WorkOrder: Attachment ${index} is null or undefined`);
      return;
    }
    
    // Log key properties to help diagnose issues
    const name = att.file_name || att.name || att.path || att.url || 'unnamed';
    console.log(`WorkOrder: Checking attachment ${index} (${name})`);
    
    // Check if it's a PDF using the improved isPdfFile function
    const isPdf = isPdfFile(att);
    console.log(`WorkOrder: Attachment ${index} (${name}) isPDF = ${isPdf}`);
    
    if (isPdf) {
      console.log(`WorkOrder: PDF found in attachment ${index}: ${name}`);
      hasPdf = true;
      
      // Cache the result for future checks
      att._isPdf = true;
    }
  });
  
  console.log('WorkOrder: Final hasPdfAttachment result =', hasPdf);
  return hasPdf;
});
</script>

<style scoped>
.timeline-container {
  max-height: vh;
  /* Reduced to match the modal height */
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
  border: 1px solid rgba(255, 255,  255, 255, 0.1);
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