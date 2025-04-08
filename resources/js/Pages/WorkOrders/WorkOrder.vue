<template>
  <div v-if="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center modal-animation">
    <!-- Semi-transparent overlay with enhanced blur -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md"></div>
    
    <!-- Modal container with fixed header and scrollable content -->
    <div class="mt-10 glossy-card flex flex-col w-full max-w-4xl mx-4 sm:mx-auto rounded-lg text-left shadow-xl transform transition-all relative z-[10000] max-h-[80vh]">
       
      <!-- Fixed header -->
      <div class="glossy-header sticky top-0 z-30 px-4 pt-3 pb-3 sm:p-4 border-b border-gray-700/50">
        <!-- Controls row -->
       <div class="flex justify-end">
          
          <!-- Edit/Save/Close buttons -->
          <div class="flex space-x-2">
            
            <button @click="closeModal" class="text-red-500 btn hover:text-black hover:bg-red-500 glossy-close-btn">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        <!-- Title and Status -->
        <div class="mb-3">
          <h3 class="text-3xl font-bold text-white truncate max-w-full" id="modal-title">
            <span v-if="!editingField.title" class="flex items-center">
              <span class="truncate">{{ workOrder.title }}</span>
              <span 
                class="ml-2 inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset shrink-0"
                :class="getStatusClasses(workOrder.status)"
              >
                {{ workOrder.status }}
              </span><NetworkStatusIndicator :workOrderId="workOrder.id" class="ml-4" />
            <button v-if="!editingField.title" @click="startEditing('title')" class="glossy-icon-btn btn-sm ml-4 text-lime-400 hover:text-lime-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
              </svg>
            </button>
            <button v-else @click="saveField('title')" class="glossy-icon-btn btn-sm ml-4 text-green-400 hover:text-green-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </button></span>
            <input 
              v-else 
              type="text" 
              v-model="form.title" 
              class=" w-95 rounded-md bg-gray-900 border-lime-400 shadow-sm focus:border-lime-600 focus:ring-lime-600 dark:focus:border-lime-400 dark:focus:ring-lime-400 text-white text-3xl font-bold px-4 py-2"
            />
          </h3><!-- Network Status Indicator -->
          <div class="flex items-center">
            
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
                  <select v-else v-model="form.status" ref="statusSelect" class="mt-1 block w-full rounded-md bg-gray-900 border-gray-400 shadow-sm focus:border-indigo-600 focus:ring-indigo-600 text-white sm:text-sm">
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
            
            <!-- Add address and hours sections after the status section -->
            <div class="flex justify-between items-start glossy-section">
              <!-- Address field -->
              <div class="w-2/3">
                <p class="text-sm text-lime-400">Work Site Address:</p>
                <p class="text-sm text-white">Location where the technician needs to be ON TIME!</p>
                <div class="flex items-start">
                  <div class="flex-grow">
                    <span v-if="!editingField.address" class="text-white">{{ workOrder.address || 'No address set' }}</span>
                    <input 
                      v-else 
                      type="text" 
                      v-model="form.address" 
                      placeholder="Enter complete work site address"
                      class="mt-1 block w-full rounded-md bg-gray-900 border-gray-800 shadow-sm focus:border-lime-600 focus:ring-lime-600 text-white sm:text-sm"
                    />
                  </div>
                  <span class="ml-2">
                    <button v-if="!editingField.address" @click="startEditing('address')" class="text-lime-400 btn hover:text-lime-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                      </svg>
                    </button>
                    <button v-else @click="saveField('address')" class="text-green-400 btn hover:text-green-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                  </span>
                </div>
              </div>
              
              <!-- Hours field -->
              <div class="w-1/3 pl-4">
                <p class="text-sm text-lime-400">Approved Hours:</p>
                <p class="text-sm text-white">Hours approved by customer</p>
                <div class="flex items-start">
                  <div class="flex-grow">
                    <span v-if="!editingField.hours" class="text-white">{{ workOrder.hours || 'No hours set' }}</span>
                    <input 
                      v-else 
                      type="number" 
                      v-model="form.hours" 
                      step="0.5"
                      min="0"
                      placeholder="Enter number of hours"
                      class="mt-1 block w-full rounded-md bg-gray-900 border-gray-800 shadow-sm focus:border-lime-600 focus:ring-lime-600 text-white sm:text-sm"
                    />
                  </div>
                  <span class="ml-2">
                    <button v-if="!editingField.hours" @click="startEditing('hours')" class="text-lime-400 btn hover:text-lime-300">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                      </svg>
                    </button>
                    <button v-else @click="saveField('hours')" class="text-green-400 btn hover:text-green-300">
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
              </div>
              <ul role="list" class="mt-1 divide-y divide-gray-700 rounded-md border border-gray-700">
                <li v-for="(attachment, index) in getAllAttachments()" :key="index" class="flex items-center justify-between py-2 pl-3 pr-4 text-sm">
                  <div class="flex w-0 flex-1 items-center">
                    <!-- Make thumbnail clickable for images -->
                    <div 
                      class="flex-shrink-0 h-10 w-10 mr-3"
                      :class="{ 'cursor-pointer hover:opacity-75': isImageFile(attachment) || isPdfFile(attachment) }"
                      @click="isImageFile(attachment) || isPdfFile(attachment) ? handlePreviewAttachment(attachment) : null"
                    >
                      <img 
                        v-if="isImageFile(attachment)" 
                        :src="`/storage/${attachment}`" 
                        alt="Attachment thumbnail" 
                        class="h-full w-full object-cover rounded-md"
                      />
                      <PdfThumbnail
                        v-else-if="isPdfFile(attachment)"
                        :pdf-url="`/storage/${attachment}`"
                        class="h-10 w-10 rounded-md"
                      />
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <span class="ml-2 flex-1 truncate text-white">{{ getFileName(attachment) }}</span>
                  </div>
                  <div class="ml-4 flex-shrink-0 flex space-x-2">
                    <a :href="`/storage/${attachment}`" class="btn rounded p-1 hover:text-gray-900" download>Download</a>
                    <!-- Add delete button -->
                    <button 
                      v-if="$page.props.auth.user && $page.props.auth.user.role !== 'guest'"
                      @click="deleteAttachment(attachment)" 
                      class="text-red-500 hover:bg-red-400 hover:text-gray-900 btn rounded"
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
    </div>
  </div>


  <div v-if="showDuplicateDateModal" class="fixed inset-0 z-[10002] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="cancelDuplicate"></div>
    <div class="relative bg-gray-900 rounded-lg p-6 max-w-md w-full mx-4 glossy-card">
      <h3 class="text-lg font-medium text-lime-400 mb-4">Select Date for Duplicate</h3>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">New Work Order Date</label>
          <input 
            type="datetime-local"
            v-model="duplicateDate"
            class="w-full rounded-md bg-gray-800 border-gray-700 text-white focus:border-lime-500 focus:ring-lime-500"
          />
        </div>
        <div class="flex justify-end space-x-3">
          <button 
            @click="cancelDuplicate"
            class="px-4 py-2 text-sm text-red-400 hover:bg-red-400 hover:text-black rounded-md border border-red-400"
          >
            Cancel
          </button>
          <button 
            @click="confirmDuplicate"
            class="px-4 py-2 text-sm text-lime-400 hover:bg-lime-400 hover:text-black rounded-md border border-lime-400 disabled:opacity-50"
            :disabled="isDuplicating"
          >
            <div class="flex items-center">
              <svg v-if="isDuplicating" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ isDuplicating ? 'Duplicating...' : 'Duplicate' }}</span>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import { ref, watch, onMounted, computed, nextTick } from 'vue';
  import { format, parseISO } from 'date-fns';
  import { useForm, usePage, router } from '@inertiajs/vue3';  // Updated import
  import Messenger from '@/Components/Messenger.vue';
  import PdfViewer from '@/Components/PdfViewer.vue';
  import PdfThumbnail from '@/Components/PdfThumbnail.vue';
  import NetworkStatusIndicator from '@/Components/NetworkStatusIndicator.vue';
  import axios from 'axios';

  export default {
    name: 'WorkOrder',
    components: {
      Messenger,
      PdfViewer, 
      PdfThumbnail,
      NetworkStatusIndicator, 
    },
    emits: ['close', 'work-order-archived'],  // Add this line to declare emits
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
        default: () => [], // Make users prop optional with empty array default
      },
    },
    setup(props, { emit }) {
      // Improved error handling for missing or invalid props
      if (!props.workOrder) {
        console.error('WorkOrder prop is missing or invalid.');
        return {
          closeModal: () => emit('close'),
          // Provide minimal required return values to prevent errors
          getStatusClasses: () => 'bg-gray-800 text-gray-300 ring-gray-700',
          workOrder: {},
          form: useForm({}),
          formatDate: () => 'Invalid Date',
          getProgressBarColor: () => 'bg-gray-600',
          getStatusProgress: () => 0,
          VALID_STATUSES: [],
          // Add empty functions for all methods used in the template
          startEditing: () => {},
          saveField: () => {},
          getAllAttachments: () => [],
          getUserName: () => 'Unknown User',
          getUserAvatar: () => '/images/avatars/default.png',
          handleImageUpload: () => {}, // Add missing function
          isPdfFile: () => false,
          isImageFile: () => false,
          isDocumentFile: () => false,
          getFileName: () => '',
          handlePreviewAttachment: () => {},
          closePreview: () => {},
          formatMultipleDates: () => '',
          formatDateShort: () => '',
          dateSelectionType: ref('single'),
          selectedDates: ref([]),
          addNewDate: () => {},
          removeDate: () => {},
          archiveWorkOrder: () => {},
          deleteAttachment: () => {},
          isUploading: ref(false),
          uploadProgress: ref(0),
          uploadError: ref(''),
          showDuplicateDateModal: ref(false),
          duplicateDate: ref(''),
          isDuplicating: ref(false),
          duplicateWorkOrder: () => {},
          cancelDuplicate: () => {},
          confirmDuplicate: () => {},
        };
      }

      // Add this near the top of setup(), with other refs
      const showPdfViewer = ref(false);
      const previewAttachment = ref(null);
      const isUploading = ref(false);
      const uploadProgress = ref(0);
      const uploadError = ref('');
      const showDuplicateDateModal = ref(false);
      const duplicateDate = ref('');
      const isDuplicating = ref(false);
      
      // Configure Axios - Add this at the top of setup() with better error handling
      let csrf;
      try {
        csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrf) {
          console.warn('CSRF token not found in the document. Some features might not work correctly.');
        }
        
        axios.defaults.withCredentials = true; 
        axios.defaults.headers.common = {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf || '',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        };
      } catch (error) {
        console.error('Error setting up Axios defaults:', error);
      }

      // Define closePreview function here to avoid reference errors
      const closePreview = () => {
        previewAttachment.value = null;
      };
      
      // Helper functions for file types - defined early to avoid reference errors
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

      const isDocumentFile = (filename) => {
        if (!filename) return false;
        const lowerFilename = filename.toLowerCase();
        return lowerFilename.endsWith('.docx') ||
               lowerFilename.endsWith('.doc');
      };
      
      const getFileName = (path) => {
        if (!path) return '';
        return path.split('/').pop();
      };
      
      const handlePreviewAttachment = (attachment) => {
        if (isImageFile(attachment) || isPdfFile(attachment) || isDocumentFile(attachment)) {
          previewAttachment.value = attachment;
        }
      };
      
      // ADD THE MISSING handleImageUpload FUNCTION
      const handleImageUpload = async (event) => {
        try {
          if (!event.target.files || event.target.files.length === 0) {
            console.warn('No files selected in handleImageUpload');
            return;
          }
          
          form.images = Array.from(event.target.files);
          console.log(`Selected ${form.images.length} files for upload`);
          
          // Automatically start the upload
          await saveField('images');
          
          // Clear the input value to allow selecting the same file again
          event.target.value = '';
        } catch (error) {
          console.error('Error in handleImageUpload:', error);
          alert('Failed to process selected files. Please try again.');
        }
      };
      
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
        images: false,
        address: false,
        hours: false,
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
        user_id: props.workOrder?.user_id || '',
        address: props.workOrder?.address || '',
        hours: props.workOrder?.hours || '',
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
          form.address = newVal.address || '';
          form.hours = newVal.hours || '';
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
        // Special handling for hours field - send only the value directly
        else if (field === 'hours') {
          const hoursValue = parseFloat(form.hours) || 0;
          try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await axios.post(
              route('work-orders.update-hours', props.workOrder.id), 
              { hours: hoursValue },
              {
                headers: {
                  'X-CSRF-TOKEN': csrf,
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
                }
              }
            );
            
            if (response.data.success) {
              props.workOrder.hours = hoursValue;
              editingField.value[field] = false;
            } else {
              throw new Error(response.data.message || 'Failed to update hours');
            }
          } catch (error) {
            console.error('Error updating hours:', error);
            alert(error.response?.data?.message || 'Failed to update hours');
          }
          return;
        }
        // Special handling for address field
        else if (field === 'address') {
          // Send the data in the format the backend expects with a dedicated endpoint
          try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            // Make a separate request specifically for address
            const response = await axios.post(`/work-orders/${props.workOrder.id}/update-address`, {
              address: form.address
            }, {
              headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              }
            });
            
            if (response.data.success) {
              // Update local state with the new address value
              props.workOrder.address = form.address;
              editingField.value[field] = false;
            } else {
              console.error('Failed to update address:', response.data.message);
              alert(response.data.message || 'Failed to update address');
            }
            return;
          } catch (error) {
            console.error('Error updating address:', error);
            if (error.response) {
              console.error('Response data:', error.response.data);
              console.error('Response status:', error.response.status);
              alert(error.response.data?.message || 'Failed to update address');
            } else {
              alert(`Error: ${error.message}`);
            }
            return;
          }
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

        // Add field_name to all requests for better backend validation
        data.field_name = field;

        // Log what we're sending for debugging
        console.log(`Updating ${field} with:`, data);

        try {
          // Get fresh CSRF token
          const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          // Use web route instead of API route with explicit headers
          const response = await axios.post(`/work-orders/${props.workOrder.id}/update-field`, {
            ...data,
            field: field // Send field name separately
          }, {
            headers: {
              'X-CSRF-TOKEN': csrf,
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            }
          });
          
          if (response.data.success) {
            // Update local state
            if (field === 'hours') {
              props.workOrder.hours = data.hours;
            } else {
              Object.keys(data).forEach(key => {
                props.workOrder[key] = data[key];
              });
            }
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

      // Add these new refs near the top with other refs
      // Replace the existing duplicateWorkOrder function with this version    
      const duplicateWorkOrder = (event) => {
        console.log('Duplicate button clicked');
        console.log('Current status:', props.workOrder.status);
        // Prevent default if it's a button click
        if (event) {
          event.preventDefault();
        }

        // Check if status is Part Needed
        if (props.workOrder.status !== 'Part Needed') {
          console.log('Wrong status - cannot duplicate');
          return;
        }

        // Set initial date value to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        duplicateDate.value = tomorrow.toISOString().slice(0, 16);
        console.log('Opening duplicate modal with date:', duplicateDate.value);      
        showDuplicateDateModal.value = true;
      };

      // Add these new functions
      const cancelDuplicate = () => {
        showDuplicateDateModal.value = false;
        duplicateDate.value = '';
      };

      const confirmDuplicate = async () => {
        if (isDuplicating.value) return;
        
        isDuplicating.value = true;
        
        try {
          const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          if (!csrf) {
            throw new Error('CSRF token not found');
          }

          // Ensure we have a valid date
          if (!duplicateDate.value) {
            throw new Error('Please select a valid date');
          }

          // Format the date properly
          const formattedDate = new Date(duplicateDate.value).toISOString();

          // Create focused payload with only necessary data and explicit date handling
          const newWorkOrderData = {
            customer_id: props.workOrder.customer_id,
            title: props.workOrder.title,
            description: props.workOrder.description,
            price: props.workOrder.price,
            user_id: props.workOrder.user_id,
            user_name: props.workOrder.user_name,
            address: props.workOrder.address,
            hours: props.workOrder.hours,
            original_id: props.workOrder.id,
            // Date related fields - make sure these are explicitly set
            date_time: formattedDate,
            visit_dates: [formattedDate],
            end_date: null,
            status: 'Scheduled',
          };

          console.log('Sending duplicate request with date:', formattedDate);
          const response = await axios.post(
            `/work-orders/${props.workOrder.id}/duplicate`,
            newWorkOrderData,
            {
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
              }
            }
          );

          if (response.data.success) {
            console.log('Duplicate response:', response.data);
            showDuplicateDateModal.value = false;
            alert('Work order duplicated successfully with new date!');
            window.location.reload();
          } else {
            throw new Error(response.data.message || 'Failed to duplicate work order');
          }
        } catch (error) {
          console.error('Error duplicating work order:', error);
          console.log('Error details:', error.response?.data);
          alert(error.response?.data?.message || error.message || 'Failed to duplicate work order');
        } finally {
          isDuplicating.value = false;
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

        try {
          const formData = new FormData();
          // Log the files being uploaded
          console.log('Files to upload:', form.images);
          
          // Append each file to FormData with the correct field name
          form.images.forEach(file => {
            formData.append('attachments[]', file);
            console.log(`Appending file: ${file.name}, size: ${file.size}, type: ${file.type}`);
          });

          // For debugging - log all entries in FormData
          for (let pair of formData.entries()) {
            console.log('FormData entry:', pair[0], pair[1]);
          }

          const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          if (!csrfToken) {
            throw new Error('CSRF token not found. Please refresh the page and try again.');
          }

          const response = await axios.post(
            `/work-orders/${props.workOrder.id}/attachments`,
            formData,
            {
              headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                // Let the browser set the Content-Type with boundary
                'Content-Type': 'multipart/form-data',
              },
              // Prevent axios from trying to transform the data
              transformRequest: [(data) => data],
              onUploadProgress: (progressEvent) => {
                const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                uploadProgress.value = percentCompleted;
              },
            }
          );

          console.log('Upload response:', response.data);

          if (response.data.success) {
            const attachments = response.data.attachments || [];
            props.workOrder.images = [...(props.workOrder.images || []), ...attachments];
            form.images = [];
            editingField.value.images = false;
          } else {
            uploadError.value = response.data.message || 'Upload failed. Please try again.';
          }
        } catch (error) {
          console.error('Error details:', {
            message: error.message,
            response: error.response?.data,
            status: error.response?.status,
          });

          if (error.response?.status === 422 && error.response?.data?.errors) {
            const validationErrors = error.response.data.errors;
            const errorMessages = Object.entries(validationErrors).map(([field, messages]) => {
              // Check if the error is related to attachments
              const fieldName = field.replace('attachments.', 'File ');
              return `${fieldName}: ${messages.join(', ')}`;
            });
            uploadError.value = errorMessages.join('\n');
          } else {
            uploadError.value = error.response?.data?.message || 'An error occurred during the upload.';
          }
        } finally {
          isUploading.value = false;
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
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        try {
          button.disabled = true;
          button.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Archiving...';
          const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          if (!csrf) {
            throw new Error('CSRF token not found. Please refresh the page and try again.');
          }

          const response = await axios.post(
            `/work-orders/${props.workOrder.id}/archive`,
            { archive: true },
            {
              headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              }
            }
          );

          if (response.data.success) {
            emit('close');
            emit('work-order-archived', {
              workOrderId: props.workOrder.id,
              message: response.data.message || 'Work order has been successfully archived.'
            });
            window.location.reload();
          } else {
            throw new Error(response.data.message || 'Failed to archive work order');
          }
        } catch (error) {
          console.error('Error archiving work order:', error);
          const errorMessage = error.response?.data?.message || error.message;
          alert(`Failed to archive work order: ${errorMessage}`);
        } finally {
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

      // Update the handleDocumentUpload function:
      const handleDocumentUpload = async (data) => {
        try {
          console.log('Document uploaded:', data);
          if (!data || (!data.path && !data.url)) {
            throw new Error('No document path received from upload');
          }

          // Extract the path, handling both full URLs and storage paths
          let attachmentPath = '';
          if (data.path) {
            attachmentPath = data.path.replace(/^\/storage\//, '');
          } else if (data.url) {
            const pathMatch = data.url.match(/\/storage\/(.*)/);
            attachmentPath = pathMatch ? pathMatch[1] : '';
          }

          if (!attachmentPath) {
            throw new Error('Could not extract valid attachment path');
          }

          // Send request to update attachments on the server
          const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          const response = await axios.post(
            `/work-orders/${props.workOrder.id}/update-attachments`,
            { 
              attachment_path: attachmentPath,
              action: 'add'
            },
            {
              headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              }
            }
          );

          if (response.data.success) {
            // Update local state
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
            console.log('Document successfully added:', attachmentPath);
          } else {
            throw new Error(response.data.message || 'Failed to update attachments on server');
          }
        } catch (error) {
          console.error('Error handling document upload:', error);
          alert(`Failed to process the uploaded document: ${error.message}`);
        }
      };

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

      const deleteWorkOrder = async () => {
        if (!confirm('Are you sure you want to delete this work order?')) {
          return;
        }
      
        try {
          const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          if (!csrf) {
            throw new Error('CSRF token not found');
          }
      
          const response = await axios.delete(`/work-orders/${props.workOrder.id}`, {
            headers: {
              'X-CSRF-TOKEN': csrf,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          });
      
          if (response.data.success) {
            emit('close');
            window.location.reload();
          } else {
            throw new Error(response.data.message || 'Failed to delete work order');
          }
        } catch (error) {
          console.error('Error deleting work order:', error);
          alert(error.response?.data?.message || error.message || 'Failed to delete work order');
        }
      };

      const statusSelect = ref(null);

      const updateStatus = () => {
        if (!editingField.value.status) {
          startEditing('status');
          // Use nextTick to ensure the select element is rendered before focusing
          nextTick(() => {
            if (statusSelect.value) {
              statusSelect.value.focus();
              // Optionally scroll the status field into view
              statusSelect.value.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          });
        } else {
          saveField('status');
        }
      };

      const startEditing = (field) => {
        if (field === 'images') {
          // Directly trigger file input click instead of setting editing mode
          return;
        }
        Object.keys(editingField.value).forEach(key => {
          editingField.value[key] = false;
        });
        editingField.value[field] = true;
      };

      // Add the computed property for PDF attachment check
      const hasPdfAttachment = computed(() => {
        const attachments = getAllAttachments();
        return attachments.some(attachment => isPdfFile(attachment));
      });

      // Add computed property to get most recent PDF attachment
      const mostRecentPdfAttachment = computed(() => {
        const attachments = getAllAttachments();
        const pdfAttachments = attachments.filter(attachment => isPdfFile(attachment));
        return pdfAttachments[pdfAttachments.length - 1];
      });

      // Add getSignature method
      const getSignature = () => {
        if (mostRecentPdfAttachment.value) {
          handlePreviewAttachment(mostRecentPdfAttachment.value);
        }
      };

      return {
        getStatusClasses,
        isEditing,
        editingField,
        form,
        formatDate,
        closeModal: () => emit('close'),
        startEditing,
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
        showDuplicateDateModal,
        duplicateDate,
        cancelDuplicate,
        confirmDuplicate,
        isDuplicating,
        deleteWorkOrder,
        updateStatus,
        statusSelect,
        hasPdfAttachment,
        mostRecentPdfAttachment,
        getSignature,
      };
    },
    // Add an errorCaptured hook to handle and log errors
    errorCaptured(err, instance, info) {
      console.error('Error captured in WorkOrder component:', err);
      console.log('Error instance:', instance);
      console.log('Error info:', info);
      // Return false to prevent the error from propagating
      return false;
    }
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

.image-icon {
  @apply text-blue-400;
}

.file-icon {
  @apply flex-shrink-0 h-5 w-5;
}

.pdf-icon {
  @apply text-red-400;
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
  border: 1px solid rgba(115, 115, 115, 0.1);
}

.glossy-btn::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(49, 49, 49, 0.223) 0%, transparent 80%);
  transform: rotate(45deg);
  opacity: 0;
  transition: opacity 0.3s ease;
}

/* Modal and scrollbar styles */
.modal-animation {
  animation: modalFadeInSlide 0.5s ease-out;
}

.modal-body {
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  height: 100%;
  width: 100%;
  max-height: calc(80vh - 125px);
  max-width: calc(50vh - 200px);
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
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
}

/* Scrollable content area */
.overflow-y-auto {
  flex-grow: 1;
}

/* Fixed footer styling */
.glossy-footer {
  flex-shrink: 0;
  border-bottom-right-radius: 0.5rem;
  border-bottom-left-radius: 0.5rem;
  background: linear-gradient(0deg, rgba(31, 41, 55, 0.95) 0%, rgba(17, 24, 39, 0.9) 100%);
}
</style>