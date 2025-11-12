<template>
  <div v-if="props.showModal">
    <!-- Background overlay -->
    <div @click="emit('close')" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60]"></div>

    <!-- Consolidated Work Order Modal -->
    <div class="fixed inset-0 flex items-center justify-center z-[70] p-2 sm:p-4 pointer-events-none">
      <div class="relative z-[80] w-full max-w-6xl h-[95vh] sm:h-[90vh] rounded-xl sm:rounded-2xl overflow-hidden shadow-2xl transform transition-all bg-white/5 backdrop-blur-xl border border-white/10 pointer-events-auto flex flex-col">
        
        <!-- Header section -->
        <div class="bg-gradient-to-r from-gray-900/90 to-gray-800/90 backdrop-blur-xl border-b border-white/20 p-3 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 sm:mb-6 gap-3">
            <div class="flex-grow relative group min-w-0">
              <div v-if="!editingField.title" @click="startEditing('title')"
                class="text-lg sm:text-xl font-bold cursor-pointer hover:text-lime-400 flex items-center bg-gradient-to-r from-lime-400 to-cyan-400 bg-clip-text text-transparent">
                <span class="truncate">{{ form.title || 'Untitled Work Order' }}</span>
                <svg xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4 ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 flex-shrink-0" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
              </div>
              <div v-else class="w-full">
                <input type="text" v-model="form.title" @blur="saveField('title')"
                  class="block w-full px-3 py-2 text-lg sm:text-xl font-bold bg-white/10 border border-white/20 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-lime-400/50 focus:border-lime-400/50"
                  ref="titleInput" @keyup.enter="saveField('title')" placeholder="Enter work order title" autofocus />
              </div>
            </div>
            <div class="flex space-x-2 items-center flex-shrink-0">
              <button v-if="isAnyFieldBeingEdited" @click="saveAllChanges"
                class="px-3 sm:px-4 py-2 rounded-xl bg-amber-400/20 hover:bg-amber-400/30 border border-amber-400/30 hover:border-amber-400/50 text-amber-400 hover:text-amber-300 font-semibold transition-all duration-200 text-sm sm:text-base"
                :class="{ 'animate-pulse': hasChanges }">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 sm:mr-2 inline" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                <span class="hidden sm:inline">Save Changes</span>
                <span class="sm:hidden">Save</span>
              </button>
              <button @click="emit('close')"
                class="px-3 sm:px-4 py-2 rounded-xl bg-red-500/20 hover:bg-red-500/30 border border-red-400/30 hover:border-red-400/50 text-red-400 hover:text-red-300 font-semibold transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Tab Navigation -->
          <div class="flex space-x-1 bg-white/5 p-1 rounded-xl overflow-x-auto">
            <button
              @click="activeTab = 'details'"
              :class="[
                'flex items-center gap-1 sm:gap-2 px-3 sm:px-4 py-2 rounded-lg font-medium transition-all duration-200 whitespace-nowrap flex-shrink-0',
                activeTab === 'details' 
                  ? 'bg-lime-400/20 text-lime-400 border border-lime-400/30' 
                  : 'text-gray-400 hover:text-white hover:bg-white/10'
              ]">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span class="text-sm sm:text-base">Details</span>
            </button>
            <button
              @click="activeTab = 'timeline'"
              :class="[
                'flex items-center gap-1 sm:gap-2 px-3 sm:px-4 py-2 rounded-lg font-medium transition-all duration-200 whitespace-nowrap flex-shrink-0',
                activeTab === 'timeline' 
                  ? 'bg-cyan-400/20 text-cyan-400 border border-cyan-400/30' 
                  : 'text-gray-400 hover:text-white hover:bg-white/10'
              ]">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="text-sm sm:text-base">Timeline</span>
            </button>
            <button
              @click="activeTab = 'notes'"
              :class="[
                'flex items-center gap-1 sm:gap-2 px-3 sm:px-4 py-2 rounded-lg font-medium transition-all duration-200 whitespace-nowrap flex-shrink-0',
                activeTab === 'notes' 
                  ? 'bg-purple-400/20 text-purple-400 border border-purple-400/30' 
                  : 'text-gray-400 hover:text-white hover:bg-white/10'
              ]">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
              <span class="text-sm sm:text-base">Notes & Messages</span>
            </button>
          </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 overflow-hidden">
          <!-- Details Tab -->
          <div v-show="activeTab === 'details'" class="h-full p-3 sm:p-6 overflow-y-auto">
            <!-- Work Order Status and Time -->
            <div class="mb-4 sm:mb-6 flex flex-col gap-3 sm:gap-4">
              <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                  <template v-if="!editingField.status">
                    <span :class="[
                      'px-3 py-1 text-xs font-semibold rounded-full cursor-pointer ring-1 ring-inset',
                      statusBadgeVariant
                    ]" @click="startEditing('status')">
                      {{ form.status || 'Scheduled' }}
                    </span>
                  </template>
                  <div v-else class="inline-block">
                    <select v-model="form.status" @blur="saveField('status')" @change="saveField('status')"
                      class="bg-white/10 border border-white/20 rounded-xl text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-lime-400/50 text-sm">
                      <option value="Scheduled">Scheduled</option>
                      <option value="In Progress">In Progress</option>
                      <option value="Pending">Pending</option>
                      <option value="Complete">Complete</option>
                      <option value="Cancelled">Cancelled</option>
                      <option value="Archived">Archived</option>
                    </select>
                  </div>
                  <span class="text-xs text-gray-400">ID: #{{ props.workOrder?.id || 'New' }}</span>
                </div>
                <div class="text-gray-300 text-xs sm:text-sm sm:ml-auto">
                  Created: {{ props.workOrder?.created_at ? new Date(props.workOrder.created_at).toLocaleDateString() : 'Now' }}
                </div>
              </div>
            </div>

            <!-- Work Order Details Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6">
              <div class="space-y-4 sm:space-y-6">
                <!-- Scheduled Date -->
                <div class="bg-white/5 backdrop-blur-xl rounded-xl p-4 border border-white/10">
                  <label class="block text-sm font-medium text-gray-300 mb-3">Scheduled Date</label>
                  <div v-if="!editingField.date_time" @click="startEditing('date_time')"
                    class="px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white cursor-pointer hover:bg-white/20 transition-colors flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ formatDate(form.date_time) || 'Pick a date' }}</span>
                  </div>
                  <div v-else>
                    <input type="datetime-local" v-model="form.date_time" @blur="saveField('date_time')"
                      class="block w-full px-3 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-lime-400/50" />
                  </div>
                </div>

                <!-- Description -->
                <div class="bg-white/5 backdrop-blur-xl rounded-xl p-4 border border-white/10">
                  <label class="block text-sm font-medium text-gray-300 mb-3">Description</label>
                  <div v-if="!editingField.description" @click="startEditing('description')" 
                    class="text-white cursor-pointer hover:text-lime-400 transition-colors min-h-[3rem] flex items-center px-2 py-1 rounded-lg hover:bg-white/5">
                    {{ form.description || 'Click to add description...' }}
                  </div>
                  <div v-else>
                    <textarea v-model="form.description" @blur="saveField('description')"
                      class="block w-full px-3 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-lime-400/50 resize-none"
                      rows="4" placeholder="Enter work order description..."></textarea>
                  </div>
                </div>

                <!-- Customer -->
                <div class="bg-white/5 backdrop-blur-xl rounded-xl p-4 border border-white/10">
                  <label class="block text-sm font-medium text-gray-300 mb-3">Customer</label>
                  <div v-if="!editingField.customer_id" @click="startEditing('customer_id')" 
                    class="text-white cursor-pointer hover:text-lime-400 transition-colors min-h-[2rem] flex items-center px-2 py-1 rounded-lg hover:bg-white/5">
                    {{ getCustomerName(form.customer_id) || 'Click to select customer...' }}
                  </div>
                  <div v-else>
                    <select v-model="form.customer_id" @blur="saveField('customer_id')" @change="saveField('customer_id')"
                      class="block w-full px-3 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-lime-400/50">
                      <option value="">Select customer</option>
                      <option v-for="customer in props.customers || []" :key="customer.id" :value="customer.id">
                        {{ customer.first_name }} {{ customer.last_name }}
                      </option>
                    </select>
                  </div>
                </div>

                <!-- Address -->
                <div class="bg-white/5 backdrop-blur-xl rounded-xl p-4 border border-white/10">
                  <label class="block text-sm font-medium text-gray-300 mb-3">Address</label>
                  <div v-if="!editingField.address" @click="startEditing('address')" 
                    class="text-white cursor-pointer hover:text-lime-400 transition-colors min-h-[2rem] flex items-center px-2 py-1 rounded-lg hover:bg-white/5">
                    {{ form.address || 'Click to add address...' }}
                  </div>
                  <div v-else>
                    <input type="text" v-model="form.address" @blur="saveField('address')"
                      class="block w-full px-3 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-lime-400/50"
                      placeholder="Enter work order address..." />
                  </div>
                  <!-- Map preview placeholder -->
                  <div v-if="form.address" class="mt-4">
                    <div class="w-full h-32 bg-gray-800/50 rounded-lg border border-white/10 flex items-center justify-center">
                      <span class="text-gray-400 text-sm">Map Preview</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="space-y-4 sm:space-y-6">
                <!-- Hours -->
                <div class="bg-white/5 backdrop-blur-xl rounded-xl p-4 border border-white/10">
                  <label class="block text-sm font-medium text-gray-300 mb-3">Hours</label>
                  <div v-if="!editingField.hours" @click="startEditing('hours')" 
                    class="text-white cursor-pointer hover:text-lime-400 transition-colors min-h-[2rem] flex items-center px-2 py-1 rounded-lg hover:bg-white/5">
                    {{ form.hours || 'Click to set hours...' }}
                  </div>
                  <div v-else>
                    <input type="number" v-model="form.hours" @blur="saveField('hours')"
                      class="block w-full px-3 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-lime-400/50"
                      placeholder="Hours required..." />
                  </div>
                </div>

                <!-- Assigned User -->
                <div class="bg-white/5 backdrop-blur-xl rounded-xl p-4 border border-white/10">
                  <label class="block text-sm font-medium text-gray-300 mb-3">Assigned To</label>
                  <div v-if="!editingField.user_id" @click="startEditing('user_id')" 
                    class="text-white cursor-pointer hover:text-lime-400 transition-colors min-h-[2rem] flex items-center px-2 py-1 rounded-lg hover:bg-white/5">
                    {{ getUserName(form.user_id) || 'Click to assign technician...' }}
                  </div>
                  <div v-else>
                    <select v-model="form.user_id" @blur="saveField('user_id')" @change="saveField('user_id')"
                      class="block w-full px-3 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-lime-400/50">
                      <option value="">Unassigned</option>
                      <option v-for="user in props.users || []" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                  </div>
                </div>

                <!-- Priority -->
                <div class="bg-white/5 backdrop-blur-xl rounded-xl p-4 border border-white/10">
                  <label class="block text-sm font-medium text-gray-300 mb-3">Priority</label>
                  <div v-if="!editingField.priority" @click="startEditing('priority')" 
                    class="text-white cursor-pointer hover:text-lime-400 transition-colors min-h-[2rem] flex items-center px-2 py-1 rounded-lg hover:bg-white/5">
                    {{ form.priority || 'Normal' }}
                  </div>
                  <div v-else>
                    <select v-model="form.priority" @blur="saveField('priority')" @change="saveField('priority')"
                      class="block w-full px-3 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-lime-400/50">
                      <option value="Low">Low</option>
                      <option value="Normal">Normal</option>
                      <option value="High">High</option>
                      <option value="Urgent">Urgent</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 sm:mt-8 flex flex-wrap gap-2 sm:gap-3">
              <button @click="activeTab = 'timeline'" class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-cyan-400/20 hover:bg-cyan-400/30 border border-cyan-400/30 hover:border-cyan-400/50 text-cyan-400 rounded-xl transition-all text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="hidden sm:inline">View</span> Timeline
              </button>
              <button @click="activeTab = 'notes'" class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-purple-400/20 hover:bg-purple-400/30 border border-purple-400/30 hover:border-purple-400/50 text-purple-400 rounded-xl transition-all text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <span class="hidden sm:inline">Add</span> Note
              </button>
              <button @click="duplicateWorkOrder" 
                class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-amber-400/20 hover:bg-amber-400/30 border border-amber-400/30 hover:border-amber-400/50 text-amber-400 rounded-xl transition-all text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Duplicate
              </button>
            </div>

            <!-- Attachments Section -->
            <div class="mt-6 sm:mt-8 bg-white/5 backdrop-blur-xl rounded-xl p-4 sm:p-6 border border-white/10">
              <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4">
                <h3 class="text-base sm:text-lg font-semibold text-white">Attachments</h3>
                <button @click="$refs.fileInput?.click()" 
                  class="px-3 sm:px-4 py-2 bg-lime-400/20 hover:bg-lime-400/30 border border-lime-400/30 hover:border-lime-400/50 text-lime-400 rounded-xl transition-all font-medium text-sm sm:text-base">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 sm:mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                  </svg>
                  Upload Files
                </button>
              </div>
              
              <!-- Hidden file input -->
              <input type="file" ref="fileInput" multiple class="hidden" @change="handleFileUpload"
                accept=".jpg,.jpeg,.png,.gif,.pdf,.heic,.docx" />

              <!-- Attachments Grid -->
              <div v-if="attachments && attachments.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3 sm:gap-4">
                <div v-for="attachment in attachments" :key="attachment.id"
                  class="relative group bg-white/10 rounded-xl overflow-hidden hover:bg-white/20 transition-all cursor-pointer"
                  @click="previewAttachment(attachment)">
                  
                  <!-- Image thumbnail -->
                  <div v-if="isImageFile(attachment)" class="aspect-square">
                    <img :src="attachment.url" :alt="attachment.filename"
                      class="w-full h-full object-cover" loading="lazy" />
                  </div>
                  
                  <!-- PDF thumbnail -->
                  <div v-else-if="isPdfFile(attachment)" class="aspect-square flex items-center justify-center bg-red-500/20">
                    <svg class="h-12 w-12 text-red-400" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                    </svg>
                  </div>
                  
                  <!-- Other file types -->
                  <div v-else class="aspect-square flex items-center justify-center bg-blue-500/20">
                    <svg class="h-12 w-12 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>

                  <!-- Filename overlay -->
                  <div class="absolute bottom-0 left-0 right-0 bg-black/80 p-2">
                    <p class="text-xs text-white truncate">{{ getFileName(attachment) }}</p>
                  </div>

                  <!-- Delete button -->
                  <button @click.stop="deleteAttachment(attachment)"
                    class="absolute top-2 right-2 w-6 h-6 bg-red-500/80 hover:bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- No attachments message -->
              <div v-else class="text-center py-8 text-gray-400">
                <svg class="h-12 w-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>
                <p>No attachments yet</p>
                <p class="text-sm mt-1">Click "Upload Files" to add attachments</p>
              </div>

              <!-- Upload progress -->
              <div v-if="isUploading" class="mt-4">
                <div class="flex justify-between mb-2">
                  <span class="text-sm text-gray-300">Uploading files...</span>
                  <span class="text-sm text-gray-300">{{ uploadProgress }}%</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2">
                  <div class="bg-lime-400 h-2 rounded-full transition-all duration-300" :style="{ width: uploadProgress + '%' }"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Timeline Tab -->
          <div v-show="activeTab === 'timeline'" class="h-full p-6 overflow-y-auto">
            <div class="mb-6">
              <h3 class="text-xl font-semibold text-white mb-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Work Order Timeline
              </h3>
              <p class="text-gray-400">Track all activities and changes for this work order</p>
            </div>
            
            <!-- Timeline content -->
            <div class="space-y-4">
              <!-- Sample timeline entries -->
              <div class="bg-white/5 backdrop-blur-xl rounded-xl p-4 border border-white/10 border-l-4 border-l-lime-400">
                <div class="flex items-start justify-between">
                  <div>
                    <h4 class="font-medium text-white">Work Order Created</h4>
                    <p class="text-sm text-gray-400 mt-1">Initial work order setup completed</p>
                  </div>
                  <span class="text-xs text-gray-500">{{ formatDate(props.workOrder.created_at) }}</span>
                </div>
              </div>
              
              <!-- Placeholder for timeline integration -->
              <div class="text-center text-gray-400 py-12 border-2 border-dashed border-gray-600 rounded-xl">
                <svg class="h-12 w-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-lg font-medium mb-2">Timeline Integration</p>
                <p class="text-sm">Full timeline functionality will be integrated here</p>
              </div>
            </div>
          </div>

          <!-- Notes Tab -->
          <div v-show="activeTab === 'notes'" class="h-full flex flex-col">
            <div class="p-6 pb-0">
              <h3 class="text-xl font-semibold text-white mb-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                Notes & Messages
              </h3>
              <p class="text-gray-400 mb-6">Add notes and send messages related to this work order</p>
            </div>
            
            <!-- Notes content area -->
            <div class="flex-1 px-6 pb-6">
              <!-- Notes/messages list -->
              <div class="flex-1 mb-6 space-y-4 max-h-96 overflow-y-auto">
                <!-- Sample note -->
                <div class="bg-white/5 backdrop-blur-xl rounded-xl p-4 border border-white/10">
                  <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 bg-lime-400/20 rounded-full flex items-center justify-center">
                        <span class="text-xs font-semibold text-lime-400">JD</span>
                      </div>
                      <span class="font-medium text-white">System</span>
                    </div>
                    <span class="text-xs text-gray-500">{{ formatDate(props.workOrder.created_at) }}</span>
                  </div>
                  <p class="text-gray-300 text-sm">Work order created and assigned.</p>
                </div>
                
                <!-- Placeholder for notes integration -->
                <div class="text-center text-gray-400 py-12 border-2 border-dashed border-gray-600 rounded-xl">
                  <svg class="h-12 w-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                  </svg>
                  <p class="text-lg font-medium mb-2">Notes & Messaging</p>
                  <p class="text-sm">Full notes and messaging functionality will be integrated here</p>
                </div>
              </div>
              
              <!-- Message input area -->
              <div class="bg-white/5 backdrop-blur-xl rounded-xl p-4 border border-white/10">
                <div class="flex gap-3">
                  <div class="flex-1">
                    <textarea 
                      v-model="newNote"
                      class="w-full bg-white/10 border border-white/20 rounded-xl px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-400/50 resize-none"
                      placeholder="Add a note or send a message..."
                      rows="3"
                    ></textarea>
                  </div>
                  <button 
                    @click="addNote"
                    class="px-4 py-2 bg-purple-400/20 hover:bg-purple-400/30 border border-purple-400/30 hover:border-purple-400/50 text-purple-400 rounded-xl transition-all self-end">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Action Bar -->
        <div class="bg-gradient-to-r from-gray-900/90 to-gray-800/90 backdrop-blur-xl border-t border-white/20 p-3 sm:p-4">
          <div class="flex flex-col sm:flex-row gap-3 sm:justify-between sm:items-center">
            <!-- Primary Actions -->
            <div class="flex flex-wrap gap-2 flex-1">
              <button @click="updateStatus"
                class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-indigo-400/20 hover:bg-indigo-400/30 border border-indigo-400/30 hover:border-indigo-400/50 text-indigo-400 rounded-xl transition-all font-medium text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 sm:mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span class="hidden sm:inline">Update </span>Status
              </button>

              <button v-if="props.workOrder?.status !== 'Complete'" @click="markComplete"
                class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-green-400/20 hover:bg-green-400/30 border border-green-400/30 hover:border-green-400/50 text-green-400 rounded-xl transition-all font-medium text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 sm:mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="hidden sm:inline">Mark </span>Complete
              </button>

              <button @click="getSignature" :disabled="!hasPdfAttachment || props.workOrder?.status === 'Scheduled'"
                class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-blue-400/20 hover:bg-blue-400/30 border border-blue-400/30 hover:border-blue-400/50 text-blue-400 rounded-xl transition-all font-medium disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 sm:mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 3.487a2.688 2.688 0 113.798 3.798L7.21 19.736a4.5 4.5 0 01-1.889 1.13l-2.7.9.9-2.7a4.5 4.5 0 011.13-1.89l12.75-12.75z" />
                </svg>
                <span class="hidden sm:inline">Get </span>Signature
              </button>
            </div>

            <!-- Secondary Actions -->
            <div class="flex flex-wrap gap-2 sm:flex-shrink-0">
              <button @click="archiveWorkOrder" :disabled="props.workOrder?.status !== 'Complete'"
                class="flex-1 sm:flex-none px-3 py-2 bg-amber-400/20 hover:bg-amber-400/30 border border-amber-400/30 hover:border-amber-400/50 text-amber-400 rounded-xl transition-all text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 sm:h-4 w-3 sm:w-4 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
                Archive
              </button>

              <button @click="deleteWorkOrder"
                class="flex-1 sm:flex-none px-3 py-2 bg-red-400/20 hover:bg-red-400/30 border border-red-400/30 hover:border-red-400/50 text-red-400 rounded-xl transition-all text-xs sm:text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 sm:h-4 w-3 sm:w-4 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, watch } from 'vue'
import axios from 'axios'

// Define props
const props = defineProps({
  showModal: Boolean,
  workOrder: Object,
  users: Array,
  customers: Array
})

// Define emits
const emit = defineEmits(['close'])

// State management
const activeTab = ref('details')
const newNote = ref('')

// Attachments state
const attachments = ref([])
const isUploading = ref(false)
const uploadProgress = ref(0)
const previewedAttachment = ref(null)

// Form state
const form = reactive({
  title: props.workOrder?.title || '',
  description: props.workOrder?.description || '',
  address: props.workOrder?.address || '',
  date_time: props.workOrder?.date_time || '',
  hours: props.workOrder?.hours || '',
  status: props.workOrder?.status || 'Scheduled',
  priority: props.workOrder?.priority || 'Normal',
  user_id: props.workOrder?.user_id || '',
  customer_id: props.workOrder?.customer_id || ''
})

// Editing state
const editingField = reactive({
  title: false,
  description: false,
  address: false,
  date_time: false,
  hours: false,
  status: false,
  priority: false,
  user_id: false,
  customer_id: false
})

// Computed properties
const isAnyFieldBeingEdited = computed(() => {
  return Object.values(editingField).some(editing => editing)
})

const hasChanges = computed(() => {
  return Object.keys(form).some(key => form[key] !== props.workOrder?.[key])
})

const statusBadgeVariant = computed(() => {
  switch (form.status?.toLowerCase()) {
    case 'scheduled':
      return 'bg-blue-500/20 text-blue-400 ring-blue-500/30'
    case 'in progress':
      return 'bg-yellow-500/20 text-yellow-400 ring-yellow-500/30'
    case 'complete':
      return 'bg-green-500/20 text-green-400 ring-green-500/30'
    case 'cancelled':
      return 'bg-red-500/20 text-red-400 ring-red-500/30'
    case 'pending':
      return 'bg-purple-500/20 text-purple-400 ring-purple-500/30'
    case 'archived':
      return 'bg-gray-500/20 text-gray-400 ring-gray-500/30'
    default:
      return 'bg-blue-500/20 text-blue-400 ring-blue-500/30'
  }
})

// Methods
const startEditing = (field) => {
  editingField[field] = true
}

const saveField = async (field) => {
  editingField[field] = false
  
  if (!props.workOrder?.id) {
    console.error('No work order ID available for saving')
    return
  }

  try {
    console.log(`Saving field ${field}:`, form[field])
    
    const response = await axios.post(`/work-orders/${props.workOrder.id}/update-field`, {
      [field]: form[field],
      '_token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    })

    if (response.data.success) {
      // Update the local workOrder object with the returned data if available
      if (response.data.workOrder) {
        Object.assign(props.workOrder, response.data.workOrder)
      }
      console.log(`Successfully saved ${field}`)
    }
  } catch (error) {
    console.error(`Error saving ${field}:`, error)
    // Reset the form value to the original if save failed
    if (props.workOrder?.[field] !== undefined) {
      form[field] = props.workOrder[field]
    }
  }
}

const saveAllChanges = async () => {
  if (!props.workOrder?.id) {
    console.error('No work order ID available for saving')
    return
  }

  const changedFields = Object.keys(form).filter(key => form[key] !== props.workOrder?.[key])
  
  if (changedFields.length === 0) {
    console.log('No changes to save')
    return
  }

  try {
    console.log('Saving all changes:', changedFields)
    
    const updateData = {}
    changedFields.forEach(field => {
      updateData[field] = form[field]
    })
    
    updateData._token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

    const response = await axios.post(`/work-orders/${props.workOrder.id}/update-multiple`, updateData)

    if (response.data.success) {
      // Update the local workOrder object
      if (response.data.workOrder) {
        Object.assign(props.workOrder, response.data.workOrder)
      }
      
      // Clear editing states
      Object.keys(editingField).forEach(field => {
        editingField[field] = false
      })
      
      console.log('Successfully saved all changes')
    }
  } catch (error) {
    console.error('Error saving all changes:', error)
    // Reset form to original values if save failed
    Object.keys(form).forEach(key => {
      if (props.workOrder?.[key] !== undefined) {
        form[key] = props.workOrder[key]
      }
    })
  }
}

const getUserName = (userId) => {
  const user = props.users?.find(u => u.id === userId)
  return user?.name || 'Unassigned'
}

const getCustomerName = (customerId) => {
  const customer = props.customers?.find(c => c.id === customerId)
  return customer ? `${customer.first_name} ${customer.last_name}` : 'Select customer'
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Attachment methods
const handleFileUpload = async (event) => {
  const files = event.target.files
  if (!files.length || !props.workOrder?.id) return

  isUploading.value = true
  uploadProgress.value = 0

  const formData = new FormData()
  for (let file of files) {
    formData.append('files[]', file)
  }
  formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))

  try {
    const response = await axios.post(`/work-orders/${props.workOrder.id}/attachments`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: (progressEvent) => {
        uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total)
      }
    })

    if (response.data.success) {
      // Add new attachments to the list
      attachments.value.push(...response.data.attachments)
      console.log('Files uploaded successfully')
    }
  } catch (error) {
    console.error('Error uploading files:', error)
  } finally {
    isUploading.value = false
    uploadProgress.value = 0
    // Clear the file input
    event.target.value = ''
  }
}

const isImageFile = (attachment) => {
  const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']
  const extension = attachment.filename?.split('.').pop()?.toLowerCase()
  return imageExtensions.includes(extension)
}

const isPdfFile = (attachment) => {
  const extension = attachment.filename?.split('.').pop()?.toLowerCase()
  return extension === 'pdf'
}

const getFileName = (attachment) => {
  return attachment.filename || attachment.name || 'Unknown file'
}

const previewAttachment = (attachment) => {
  // Open attachment in a new window/tab
  if (attachment.url) {
    window.open(attachment.url, '_blank')
  }
}

const deleteAttachment = async (attachment) => {
  if (!confirm('Are you sure you want to delete this attachment?')) return

  try {
    const response = await axios.delete(`/work-orders/${props.workOrder.id}/attachments/${attachment.id}`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    })

    if (response.data.success) {
      // Remove attachment from the list
      const index = attachments.value.findIndex(a => a.id === attachment.id)
      if (index !== -1) {
        attachments.value.splice(index, 1)
      }
      console.log('Attachment deleted successfully')
    }
  } catch (error) {
    console.error('Error deleting attachment:', error)
  }
}

const duplicateWorkOrder = async () => {
  if (!props.workOrder?.id) return

  try {
    const response = await axios.post(`/work-orders/${props.workOrder.id}/duplicate`, {
      '_token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    })

    if (response.data.success) {
      console.log('Work order duplicated successfully')
      // Optionally close modal and refresh the page or redirect
      emit('close')
      if (response.data.redirect) {
        window.location.href = response.data.redirect
      }
    }
  } catch (error) {
    console.error('Error duplicating work order:', error)
  }
}

// Load attachments when component mounts
const loadAttachments = async () => {
  if (!props.workOrder?.id) return

  try {
    const response = await axios.get(`/work-orders/${props.workOrder.id}/attachments`)
    if (response.data.success) {
      attachments.value = response.data.attachments || []
    }
  } catch (error) {
    console.error('Error loading attachments:', error)
  }
}

// Footer action methods
const updateStatus = async () => {
  // This could open a status selection modal or cycle through statuses
  const statuses = ['Scheduled', 'In Progress', 'Pending', 'Complete', 'Cancelled']
  const currentIndex = statuses.indexOf(form.status)
  const nextIndex = (currentIndex + 1) % statuses.length
  
  form.status = statuses[nextIndex]
  await saveField('status')
}

const markComplete = async () => {
  form.status = 'Complete'
  await saveField('status')
}

const getSignature = () => {
  // Open signature collection interface
  console.log('Opening signature collection...')
  // This would typically open a signature pad modal
}

const archiveWorkOrder = async () => {
  if (!confirm('Are you sure you want to archive this work order?')) return
  
  try {
    const response = await axios.post(`/work-orders/${props.workOrder.id}/archive`, {
      '_token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    })

    if (response.data.success) {
      console.log('Work order archived successfully')
      emit('close')
    }
  } catch (error) {
    console.error('Error archiving work order:', error)
  }
}

const deleteWorkOrder = async () => {
  if (!confirm('Are you sure you want to delete this work order? This action cannot be undone.')) return
  
  try {
    const response = await axios.delete(`/work-orders/${props.workOrder.id}`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    })

    if (response.data.success) {
      console.log('Work order deleted successfully')
      emit('close')
      // Optionally redirect or refresh the page
      if (response.data.redirect) {
        window.location.href = response.data.redirect
      }
    }
  } catch (error) {
    console.error('Error deleting work order:', error)
  }
}

// Check if has PDF attachment for signature collection
const hasPdfAttachment = computed(() => {
  return attachments.value.some(attachment => isPdfFile(attachment))
})

const getStatusColor = (status) => {
  switch (status?.toLowerCase()) {
    case 'scheduled': return 'bg-blue-500 text-blue-100'
    case 'in progress': return 'bg-yellow-500 text-yellow-100'
    case 'complete': return 'bg-green-500 text-green-100'
    case 'cancelled': return 'bg-red-500 text-red-100'
    case 'on hold': return 'bg-purple-500 text-purple-100'
    default: return 'bg-gray-500 text-gray-100'
  }
}

const addNote = async () => {
  if (!newNote.value.trim() || !props.workOrder?.id) return
  
  try {
    console.log('Adding note:', newNote.value)
    
    const response = await axios.post(`/work-orders/${props.workOrder.id}/notes`, {
      content: newNote.value,
      '_token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    })

    if (response.data.success) {
      newNote.value = ''
      console.log('Note added successfully')
    }
  } catch (error) {
    console.error('Error adding note:', error)
  }
}

// Update form when workOrder prop changes
watch(() => props.workOrder, (newWorkOrder) => {
  if (newWorkOrder) {
    Object.assign(form, {
      title: newWorkOrder.title || '',
      description: newWorkOrder.description || '',
      address: newWorkOrder.address || '',
      date_time: newWorkOrder.date_time || '',
      hours: newWorkOrder.hours || '',
      status: newWorkOrder.status || 'Scheduled',
      priority: newWorkOrder.priority || 'Normal',
      user_id: newWorkOrder.user_id || '',
      customer_id: newWorkOrder.customer_id || ''
    })
  }
}, { deep: true, immediate: true })

// Reset active tab when modal opens and load attachments
watch(() => props.showModal, (isOpen) => {
  if (isOpen) {
    activeTab.value = 'details'
    loadAttachments()
  }
})

// Load attachments when component mounts
onMounted(() => {
  if (props.workOrder?.id) {
    loadAttachments()
  }
})
</script>

<style scoped>
/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
  width: 4px;
}

::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
}

::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 2px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>