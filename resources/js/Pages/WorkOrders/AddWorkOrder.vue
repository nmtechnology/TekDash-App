<template>
  <div>
    <!-- Modern Glass Trigger Button -->
    <button @click="handleShowModal"
      class="flex items-center gap-3 px-6 py-3 rounded-xl bg-lime-400/20 hover:bg-lime-400/30 border border-lime-400/30 hover:border-lime-400/50 text-lime-400 hover:text-lime-300 font-semibold transition-all duration-200 hover:scale-105 hover:shadow-lg hover:shadow-lime-400/25">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      <span class="hidden sm:inline">Create Work Order</span>
      <span class="sm:hidden">New WO</span>
    </button>

    <!-- Modern Glass Modal -->
    <Transition 
      enter-active-class="ease-out duration-300" 
      enter-from-class="opacity-0 scale-95" 
      enter-to-class="opacity-100 scale-100"
      leave-active-class="ease-in duration-200" 
      leave-from-class="opacity-100 scale-100" 
      leave-to-class="opacity-0 scale-95">
      <div v-show="showModal" class="fixed inset-0 z-[1000] flex items-center justify-center p-4">
        
        <!-- Animated Glass Backdrop -->
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="handleHideModal">
          <!-- Background Blobs -->
          <div class="absolute top-20 left-20 w-96 h-96 bg-lime-400/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
          <div class="absolute top-40 right-20 w-80 h-80 bg-cyan-400/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
          <div class="absolute bottom-20 left-1/3 w-72 h-72 bg-purple-400/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
        </div>

        <!-- Modal Container -->
        <div class="relative w-full max-w-4xl h-[90vh] bg-white/5 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl flex flex-col overflow-hidden" @click.stop>
          
          <!-- Modern Header with Steps -->
          <div class="relative bg-gradient-to-r from-gray-900/90 to-gray-800/90 backdrop-blur-xl border-b border-white/20 p-6">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-lime-400/20 border border-lime-400/30 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-xl font-bold bg-gradient-to-r from-lime-400 to-cyan-400 bg-clip-text text-transparent">
                    Create Work Order
                  </h3>
                  <p class="text-gray-400 text-sm">Step {{ currentStep }} of {{ totalSteps }}</p>
                </div>
              </div>
              <button @click="handleHideModal"
                class="w-10 h-10 rounded-xl bg-red-500/20 hover:bg-red-500/30 border border-red-400/30 hover:border-red-400/50 text-red-400 hover:text-red-300 transition-all flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            
            <!-- Modern Step Progress -->
            <div class="relative">
              <!-- Progress Bar Background -->
              <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-lime-400 to-cyan-400 rounded-full transition-all duration-500 ease-out"
                     :style="`width: ${(currentStep / totalSteps) * 100}%`"></div>
              </div>
              
              <!-- Step Indicators -->
              <div class="flex justify-between items-center mt-4 overflow-x-auto pb-2">
                <div v-for="step in totalSteps" :key="step" 
                     class="flex flex-col items-center min-w-0 flex-shrink-0 group"
                     :class="step < totalSteps ? 'mr-4 sm:mr-6' : ''">
                  <!-- Step Circle -->
                  <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all duration-200"
                       :class="[
                         step < currentStep 
                           ? 'bg-lime-400/30 border-lime-400 text-lime-400' 
                           : step === currentStep 
                             ? 'bg-cyan-400/30 border-cyan-400 text-cyan-400 animate-pulse' 
                             : 'bg-white/10 border-white/30 text-gray-400'
                       ]">
                    <svg v-if="step < currentStep" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                    <span v-else class="text-xs font-semibold">{{ step }}</span>
                  </div>
                  
                  <!-- Step Label -->
                  <div class="text-xs text-center mt-2 max-w-16 sm:max-w-20"
                       :class="[
                         step < currentStep ? 'text-lime-400' 
                         : step === currentStep ? 'text-cyan-400 font-semibold' 
                         : 'text-gray-500'
                       ]">
                    {{ getStepLabel(step) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Scrollable Content Area -->
          <div class="flex-1 overflow-y-auto p-6">
            <form @submit.prevent="submitForm" class="space-y-6">
              
              <!-- Step 1: Customer & Technician Selection -->
              <div v-show="currentStep === 1" class="space-y-6">
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-cyan-400/20 border border-cyan-400/30 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 010 7.75" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-white">Select Customer</h4>
                      <p class="text-gray-400 text-sm">Choose the customer for this work order</p>
                    </div>
                  </div>
                  <select v-model="form.customer_id" 
                          class="w-full p-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl text-white focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/25 transition-all"
                          required>
                    <option value="" disabled class="text-gray-500">Select a customer ({{ safeCustomersArray.length }} available)</option>
                    <option v-for="customer in safeCustomersArray" :key="customer.id" :value="customer.id" class="text-gray-900">
                      {{ customer.business_name || customer.name || `Customer #${customer.id}` }}
                    </option>
                  </select>
                </div>

                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-orange-400/20 border border-orange-400/30 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-white">Assign Technician</h4>
                      <p class="text-gray-400 text-sm">Choose the technician for this assignment</p>
                    </div>
                  </div>
                  <div v-if="isLoadingTechnicians" class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-orange-400"></div>
                    <span class="ml-3 text-orange-400">Loading technicians...</span>
                  </div>
                  <div v-else-if="safeTechniciansArray.length === 0" class="text-center py-8">
                    <div class="w-16 h-16 rounded-full bg-red-500/20 border border-red-400/30 flex items-center justify-center mx-auto mb-4">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                      </svg>
                    </div>
                    <p class="text-red-400 font-medium">No technicians available</p>
                    <p class="text-gray-500 text-sm">Please check back later</p>
                  </div>
                  <select v-else v-model="form.technician_id" 
                          class="w-full p-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl text-white focus:border-orange-400/50 focus:ring-2 focus:ring-orange-400/25 transition-all"
                          required>
                    <option value="" disabled class="text-gray-500">Select a technician ({{ safeTechniciansArray.length }} available)</option>
                    <option v-for="technician in safeTechniciansArray" :key="technician.id" :value="technician.id" class="text-gray-900">
                      {{ technician.first_name && technician.last_name ? 
                          `${technician.first_name} ${technician.last_name}${technician.employee_id ? ` (${technician.employee_id})` : ''}` :
                          (technician.name || `Technician #${technician.id}`) }}
                    </option>
                  </select>
                </div>

                <!-- Selection Summary -->
                <div class="bg-gradient-to-r from-lime-400/10 to-cyan-400/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                  <h5 class="text-lg font-semibold text-white mb-4">Selection Summary</h5>
                  <div class="grid sm:grid-cols-2 gap-4">
                    <div class="text-center p-4 rounded-xl bg-white/5">
                      <p class="text-gray-400 text-sm">Customer</p>
                      <p class="text-lime-400 font-semibold mt-1">
                        {{ safeCustomersArray.find(c => c.id == form.customer_id)?.business_name ||
                           safeCustomersArray.find(c => c.id == form.customer_id)?.name || 
                           'None selected' }}
                      </p>
                    </div>
                    <div class="text-center p-4 rounded-xl bg-white/5">
                      <p class="text-gray-400 text-sm">Technician</p>
                      <p class="text-cyan-400 font-semibold mt-1">
                        {{ safeTechniciansArray.find(t => t.id == form.technician_id)?.first_name &&
                           safeTechniciansArray.find(t => t.id == form.technician_id)?.last_name ?
                           `${safeTechniciansArray.find(t => t.id == form.technician_id)?.first_name} ${safeTechniciansArray.find(t => t.id == form.technician_id)?.last_name}` :
                           'None selected' }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 2: Work Order Title -->
              <div v-show="currentStep === 2" class="space-y-6">
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-400/20 border border-purple-400/30 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-white">Create Work Order Title</h4>
                      <p class="text-gray-400 text-sm">Generate a searchable title for this work order</p>
                    </div>
                  </div>
                  
                  <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-300 mb-2">Work Order Number</label>
                      <div class="relative">
                        <input v-model="workOrderNumber" 
                               @input="debouncedCheckExistingWorkOrder"
                               type="text"
                               class="w-full p-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl text-white focus:border-purple-400/50 focus:ring-2 focus:ring-purple-400/25 transition-all pr-12"
                               placeholder="Enter unique number"
                               required />
                        <div class="absolute right-4 top-1/2 -translate-y-1/2">
                          <div v-if="checkingWorkOrder" class="animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-gray-400"></div>
                          <svg v-else-if="workOrderVerified && !duplicateWorkOrderFound && workOrderNumber" 
                               xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                          </svg>
                          <svg v-else-if="duplicateWorkOrderFound" 
                               xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </div>
                      </div>
                      <p v-if="duplicateWorkOrderFound" class="text-red-400 text-xs mt-1">Number already in use</p>
                    </div>
                    
                    <div>
                      <label class="block text-sm font-medium text-gray-300 mb-2">Work Type</label>
                      <select v-model="workType" 
                              class="w-full p-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl text-white focus:border-purple-400/50 focus:ring-2 focus:ring-purple-400/25 transition-all"
                              required>
                        <option value="" disabled class="text-gray-500">Select work type</option>
                        <option value="CCTV" class="text-gray-900">CCTV</option>
                        <option value="ALARM" class="text-gray-900">ALARM</option>
                        <option value="CABLING" class="text-gray-900">CABLING</option>
                        <option value="POS" class="text-gray-900">POS</option>
                        <option value="INTERCOM" class="text-gray-900">INTERCOM</option>
                        <option value="FIRE" class="text-gray-900">FIRE</option>
                        <option value="ACCESS-CONTROL" class="text-gray-900">ACCESS-CONTROL</option>
                        <option value="TS" class="text-gray-900">TS (Troubleshooting)</option>
                        <option value="PM" class="text-gray-900">PM (Preventive Maintenance)</option>
                        <option value="INSTALL" class="text-gray-900">INSTALL</option>
                        <option value="ESTIMATE" class="text-gray-900">ESTIMATE</option>
                        <option value="WALK-THRU" class="text-gray-900">WALK-THRU</option>
                        <option value="MEETING" class="text-gray-900">MEETING</option>
                        <option value="APPT" class="text-gray-900">APPT (Appointment)</option>
                        <option value="SERVICE" class="text-gray-900">SERVICE</option>
                        <option value="REPAIR" class="text-gray-900">REPAIR</option>
                        <option value="UPGRADE" class="text-gray-900">UPGRADE</option>
                        <option value="PERSONAL-LEAVE" class="text-gray-900">PERSONAL-LEAVE</option>
                        <option value="TRAINING" class="text-gray-900">TRAINING</option>
                      </select>
                    </div>
                  </div>
                  
                  <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Location/Business Name</label>
                    <select v-model="location" 
                            class="w-full p-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl text-white focus:border-purple-400/50 focus:ring-2 focus:ring-purple-400/25 transition-all"
                            required>
                      <option value="" disabled class="text-gray-500">Select business name</option>
                      <option value="Chili's" class="text-gray-900">Chili's</option>
                      <option value="Brinks" class="text-gray-900">Brinks</option>
                      <option value="Chase" class="text-gray-900">Chase</option>
                      <option value="Dillard's" class="text-gray-900">Dillard's</option>
                      <option value="DutchBros" class="text-gray-900">DutchBros</option>
                      <option value="RaisingCaines" class="text-gray-900">RaisingCaines</option>
                      <option value="ChaseATM" class="text-gray-900">ChaseATM</option>
                      <option value="WalMart" class="text-gray-900">WalMart</option>
                      <option value="Target" class="text-gray-900">Target</option>
                      <option value="Whataburger" class="text-gray-900">Whataburger</option>
                      <option value="MisterCarwash" class="text-gray-900">MisterCarwash</option>
                      <option value="WellsFargo" class="text-gray-900">WellsFargo</option>
                      <option value="NewSite" class="text-gray-900">NewSite</option>
                    </select>
                  </div>
                </div>

                <!-- Generated Title Preview -->
                <div class="bg-gradient-to-r from-purple-400/10 to-pink-400/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                  <h5 class="text-lg font-semibold text-white mb-4">Generated Title Preview</h5>
                  <div class="text-center p-6 rounded-xl bg-white/5">
                    <p class="text-purple-400 font-bold text-xl">{{ formattedTitle || 'Configure fields to generate title' }}</p>
                  </div>
                </div>
              </div>

              <!-- Step 3: Service Description -->
              <div v-show="currentStep === 3" class="space-y-6">
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-400/20 border border-blue-400/30 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-white">Service Description</h4>
                      <p class="text-gray-400 text-sm">Describe what the technician will be doing</p>
                    </div>
                  </div>
                  <textarea v-model="form.description" 
                            class="w-full p-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl text-white focus:border-blue-400/50 focus:ring-2 focus:ring-blue-400/25 transition-all min-h-[200px] resize-none"
                            placeholder="Enter detailed description of the work to be performed..."
                            required></textarea>
                </div>

                <!-- Description Preview -->
                <div class="bg-gradient-to-r from-blue-400/10 to-indigo-400/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                  <h5 class="text-lg font-semibold text-white mb-4">Description Preview</h5>
                  <div class="p-4 rounded-xl bg-white/5">
                    <p class="text-blue-400 whitespace-pre-line">{{ descriptionSummary }}</p>
                  </div>
                </div>
              </div>

              <!-- Step 4: Date & Time -->
              <div v-show="currentStep === 4" class="space-y-6">
                <!-- Enhanced Glass Morphism Calendar Container -->
                <div class="relative bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10 overflow-hidden">
                  <!-- Gradient Overlay -->
                  <div class="absolute inset-0 bg-gradient-to-br from-emerald-400/5 via-transparent to-teal-400/5 pointer-events-none"></div>
                  
                  <!-- Header with Glass Effect -->
                  <div class="relative flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-emerald-400/20 backdrop-blur-lg border border-emerald-400/30 flex items-center justify-center shadow-lg shadow-emerald-400/25">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-xl font-bold bg-gradient-to-r from-emerald-400 to-teal-400 bg-clip-text text-transparent">
                        Schedule Date & Time
                      </h4>
                      <p class="text-gray-400 text-sm">Select when this work will be performed</p>
                    </div>
                  </div>
                  
                  <!-- Enhanced Quick Select Buttons with Glass Morphism -->
                  <div class="flex flex-wrap gap-3 mb-8">
                    <button 
                      v-for="quickOption in [
                        { key: 'today', label: 'Today', icon: 'M12 3v18m9-9H3' },
                        { key: 'tomorrow', label: 'Tomorrow', icon: 'M13 7l5 5-5 5M6 12h12' },
                        { key: 'nextWeek', label: 'Next Week', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' }
                      ]"
                      :key="quickOption.key"
                      type="button" 
                      @click="quickSelectDate(quickOption.key)"
                      class="group relative px-6 py-3 bg-white/10 backdrop-blur-lg border border-white/20 rounded-xl text-emerald-400 hover:bg-emerald-400/20 hover:border-emerald-400/40 transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-emerald-400/25">
                      <!-- Button Content -->
                      <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="quickOption.icon" />
                        </svg>
                        {{ quickOption.label }}
                      </div>
                      <!-- Glass reflection effect -->
                      <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-white/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    </button>
                  </div>
                  
                  <!-- Enhanced Glass Morphism Date/Time Inputs -->
                  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Enhanced Date Input with Glass Container -->
                    <div class="relative">
                      <label class="flex items-center gap-2 text-emerald-400 text-sm font-bold mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Select Date
                      </label>
                      <div class="relative group">
                        <input
                          v-model="selectedDateString"
                          type="date"
                          :min="todayString"
                          class="glass-date-input w-full p-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl text-white focus:border-emerald-400/60 focus:ring-2 focus:ring-emerald-400/30 focus:bg-white/15 transition-all duration-300 [color-scheme:dark] hover:bg-white/15 hover:border-emerald-400/40"
                          required
                        />
                        <!-- Focus Glow Effect -->
                        <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-emerald-400/20 via-teal-400/20 to-emerald-400/20 blur-sm opacity-0 group-focus-within:opacity-100 transition-all duration-500 pointer-events-none -z-10"></div>
                        <!-- Glass reflection -->
                        <div class="absolute top-2 left-2 right-2 h-8 bg-gradient-to-br from-white/20 via-white/5 to-transparent rounded-lg opacity-60 pointer-events-none"></div>
                      </div>
                    </div>
                    
                    <!-- Enhanced Time Input with Glass Container -->
                    <div class="relative">
                      <label class="flex items-center gap-2 text-emerald-400 text-sm font-bold mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Select Time
                      </label>
                      <div class="relative group">
                        <input
                          v-model="selectedTimeString"
                          type="time"
                          class="glass-time-input w-full p-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl text-white focus:border-emerald-400/60 focus:ring-2 focus:ring-emerald-400/30 focus:bg-white/15 transition-all duration-300 [color-scheme:dark] hover:bg-white/15 hover:border-emerald-400/40"
                          required
                        />
                        <!-- Focus Glow Effect -->
                        <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-emerald-400/20 via-teal-400/20 to-emerald-400/20 blur-sm opacity-0 group-focus-within:opacity-100 transition-all duration-500 pointer-events-none -z-10"></div>
                        <!-- Glass reflection -->
                        <div class="absolute top-2 left-2 right-2 h-8 bg-gradient-to-br from-white/20 via-white/5 to-transparent rounded-lg opacity-60 pointer-events-none"></div>
                      </div>
                    </div>
                  </div>

                  <!-- Common Time Presets with Glass Morphism -->
                  <div class="mt-6">
                    <label class="flex items-center gap-2 text-emerald-400 text-sm font-bold mb-3">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      Quick Time Selection
                    </label>
                    <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
                      <button 
                        v-for="timePreset in ['08:00', '09:00', '10:00', '13:00', '14:00', '15:00']"
                        :key="timePreset"
                        type="button"
                        @click="selectedTimeString = timePreset"
                        :class="[
                          'group relative px-3 py-2 rounded-lg backdrop-blur-lg border transition-all duration-300 hover:scale-105 text-sm font-semibold',
                          selectedTimeString === timePreset
                            ? 'bg-emerald-400/30 border-emerald-400/60 text-emerald-300 shadow-lg shadow-emerald-400/25'
                            : 'bg-white/10 border-white/20 text-gray-300 hover:bg-white/20 hover:border-emerald-400/30 hover:text-emerald-400'
                        ]">
                        {{ timePreset }}
                        <!-- Glass reflection effect -->
                        <div class="absolute inset-0 rounded-lg bg-gradient-to-br from-white/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Enhanced DateTime Preview with Glass Morphism -->
                <div class="relative bg-gradient-to-br from-emerald-400/10 via-white/5 to-teal-400/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20 overflow-hidden">
                  <!-- Animated Background Elements -->
                  <div class="absolute top-4 right-4 w-16 h-16 bg-emerald-400/10 rounded-full blur-xl animate-pulse"></div>
                  <div class="absolute bottom-4 left-4 w-12 h-12 bg-teal-400/10 rounded-full blur-lg animate-pulse animation-delay-1000"></div>
                  
                  <div class="relative">
                    <h5 class="flex items-center gap-2 text-lg font-bold text-white mb-4">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h6a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                      </svg>
                      Scheduled For
                    </h5>
                    <div class="relative p-6 rounded-xl bg-white/10 backdrop-blur-lg border border-white/20 text-center overflow-hidden">
                      <!-- Content gradient background -->
                      <div class="absolute inset-0 bg-gradient-to-br from-emerald-400/5 via-transparent to-teal-400/5"></div>
                      <div class="relative">
                        <p class="text-emerald-400 font-bold text-3xl mb-2 tracking-wide">{{ formattedDateTime || 'Select date and time' }}</p>
                        <div v-if="formattedDateTime" class="flex items-center justify-center gap-4 text-sm text-gray-300">
                          <span class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ selectedDateString }}
                          </span>
                          <span class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ selectedTimeString }}
                          </span>
                        </div>
                      </div>
                      <!-- Glass shine effect -->
                      <div class="absolute top-2 left-2 right-2 h-6 bg-gradient-to-r from-transparent via-white/20 to-transparent rounded-lg opacity-60 pointer-events-none"></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 5: Address -->
              <div v-show="currentStep === 5" class="space-y-6">
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-red-400/20 border border-red-400/30 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-white">Location Address</h4>
                      <p class="text-gray-400 text-sm">Enter the complete work site address</p>
                    </div>
                  </div>
                  
                  <div class="relative">
                    <input v-model="form.address" 
                           @blur="geocodeAddress(form.address)"
                           type="text"
                           class="w-full p-4 bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl text-white focus:border-red-400/50 focus:ring-2 focus:ring-red-400/25 transition-all pr-12"
                           placeholder="Enter complete address (street, city, state, zip)"
                           required />
                    <div class="absolute right-4 top-1/2 -translate-y-1/2">
                      <div v-if="mapboxLoading" class="animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-red-400"></div>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                      </svg>
                    </div>
                  </div>

                  <!-- Map Preview -->
                  <div v-if="form.address" class="mt-4">
                    <div v-if="mapboxLoading" class="flex items-center justify-center p-6 bg-white/5 rounded-xl min-h-[200px]">
                      <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-red-400 mr-3"></div>
                      <span class="text-red-400">Loading map preview...</span>
                    </div>
                    <div v-else-if="mapboxError" class="p-4 bg-red-500/20 border border-red-400/30 rounded-xl">
                      <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="text-red-400">{{ mapboxError }}</span>
                      </div>
                    </div>
                    <a v-else-if="mapboxImageUrl" :href="mapboxMapsLink" target="_blank" rel="noopener" class="block">
                      <img :src="mapboxImageUrl" alt="Map preview" class="w-full h-48 object-cover rounded-xl border border-white/20 hover:opacity-90 transition-opacity" />
                      <p class="text-xs text-gray-400 mt-2">Click map to open in browser</p>
                    </a>
                  </div>
                </div>

                <!-- Address Review -->
                <div v-if="form.address" class="bg-gradient-to-r from-red-400/10 to-orange-400/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                  <h5 class="text-lg font-semibold text-white mb-4">Address Review</h5>
                  <div class="text-center p-4 rounded-xl bg-white/5">
                    <p class="text-red-400 font-semibold">{{ form.address }}</p>
                  </div>
                </div>
              </div>

              <!-- Step 6: Hours & Duration -->
              <div v-show="currentStep === 6" class="space-y-6">
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-yellow-400/20 border border-yellow-400/30 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-white">Work Duration</h4>
                      <p class="text-gray-400 text-sm">Estimate how long this job will take</p>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Hours Input -->
                    <div>
                      <label class="block text-yellow-400 text-sm font-semibold mb-2">Hours Required</label>
                      <div class="relative">
                        <input
                          v-model.number="form.hours"
                          type="range"
                          min="2"
                          max="16"
                          step="0.5"
                          class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer slider"
                        />
                        <div class="flex justify-between text-xs text-gray-400 mt-1">
                          <span>2h</span>
                          <span>8h</span>
                          <span>16h</span>
                        </div>
                      </div>
                      <div class="mt-3 text-center">
                        <span class="text-2xl font-bold text-yellow-400">{{ form.hours }}</span>
                        <span class="text-gray-400 ml-1">{{ form.hours === 1 ? 'hour' : 'hours' }}</span>
                      </div>
                    </div>
                    
                    <!-- Travel Options -->
                    <div>
                      <label class="block text-yellow-400 text-sm font-semibold mb-2">Travel & Mileage</label>
                      <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                          <input
                            v-model="form.includeTravel"
                            type="checkbox"
                            class="w-4 h-4 rounded border-2 border-yellow-400/30 bg-white/10 text-yellow-400 focus:ring-yellow-400/25"
                          />
                          <span class="text-white">Include travel time & mileage</span>
                        </label>
                        
                        <div v-if="form.includeTravel" class="pl-7 space-y-3">
                          <div>
                            <label class="block text-xs text-gray-400 mb-1">Miles (round trip)</label>
                            <input
                              v-model.number="form.travelMiles"
                              type="number"
                              min="1"
                              max="500"
                              class="w-full p-2 bg-white/10 backdrop-blur-xl border border-white/20 rounded-lg text-white focus:border-yellow-400/50 focus:ring-2 focus:ring-yellow-400/25 transition-all text-sm"
                              placeholder="45"
                            />
                          </div>
                          <div>
                            <label class="block text-xs text-gray-400 mb-1">Rate per mile</label>
                            <input
                              v-model.number="form.mileageRate"
                              type="number"
                              step="0.01"
                              min="0.50"
                              max="2.00"
                              class="w-full p-2 bg-white/10 backdrop-blur-xl border border-white/20 rounded-lg text-white focus:border-yellow-400/50 focus:ring-2 focus:ring-yellow-400/25 transition-all text-sm"
                              placeholder="1.00"
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Duration Preview -->
                <div class="bg-gradient-to-r from-yellow-400/10 to-amber-400/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                  <h5 class="text-lg font-semibold text-white mb-4">Duration Summary</h5>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 rounded-xl bg-white/5">
                      <p class="text-yellow-400 font-bold text-xl">{{ form.hours }}h</p>
                      <p class="text-gray-400 text-sm">Work Time</p>
                    </div>
                    <div v-if="form.includeTravel" class="text-center p-4 rounded-xl bg-white/5">
                      <p class="text-yellow-400 font-bold text-xl">{{ form.travelMiles }}</p>
                      <p class="text-gray-400 text-sm">Miles RT</p>
                    </div>
                    <div class="text-center p-4 rounded-xl bg-white/5">
                      <p class="text-yellow-400 font-bold text-xl">${{ travelCost }}</p>
                      <p class="text-gray-400 text-sm">Travel Cost</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 7: Rates & Pricing -->
              <div v-show="currentStep === 7" class="space-y-6">
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-green-400/20 border border-green-400/30 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-white">Pricing & Rates</h4>
                      <p class="text-gray-400 text-sm">Set your hourly rate and calculate costs</p>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Hourly Rate Selection -->
                    <div>
                      <label class="block text-green-400 text-sm font-semibold mb-3">Hourly Rate</label>
                      <div class="grid grid-cols-4 gap-2 mb-4">
                        <button
                          v-for="rate in rateValues.slice(0, 8)"
                          :key="rate"
                          type="button"
                          @click="form.hourlyRate = rate; form.hourly_rate = rate"
                          :class="[
                            'p-3 rounded-lg transition-all text-sm font-semibold',
                            form.hourlyRate === rate
                              ? 'bg-green-400/30 border border-green-400/50 text-green-400'
                              : 'bg-white/10 border border-white/20 text-gray-300 hover:bg-white/20 hover:text-white'
                          ]"
                        >
                          ${{ rate }}
                        </button>
                      </div>
                      
                      <!-- Custom Rate Input -->
                      <div>
                        <label class="block text-xs text-gray-400 mb-2">Custom Rate</label>
                        <div class="relative">
                          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-green-400">$</span>
                          <input
                            v-model.number="form.hourlyRate"
                            @input="form.hourly_rate = form.hourlyRate"
                            type="number"
                            min="55"
                            max="300"
                            step="5"
                            class="w-full pl-8 pr-4 py-3 bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl text-white focus:border-green-400/50 focus:ring-2 focus:ring-green-400/25 transition-all"
                            placeholder="120"
                          />
                        </div>
                      </div>
                    </div>
                    
                    <!-- Cost Breakdown -->
                    <div>
                      <label class="block text-green-400 text-sm font-semibold mb-3">Cost Breakdown</label>
                      <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-white/5 rounded-lg">
                          <span class="text-gray-300">Labor ({{ form.hours }}h × ${{ form.hourlyRate }})</span>
                          <span class="text-green-400 font-semibold">${{ laborCost }}</span>
                        </div>
                        <div v-if="form.includeTravel" class="flex justify-between items-center p-3 bg-white/5 rounded-lg">
                          <span class="text-gray-300">Travel ({{ form.travelMiles }} mi × ${{ form.mileageRate }})</span>
                          <span class="text-green-400 font-semibold">${{ travelCost }}</span>
                        </div>
                        <div class="flex justify-between items-center p-4 bg-green-400/10 border border-green-400/30 rounded-lg">
                          <span class="text-white font-semibold">Total Estimate</span>
                          <span class="text-green-400 font-bold text-xl">${{ totalPrice }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 8: Status & Priority -->
              <div v-show="currentStep === 8" class="space-y-6">
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-400/20 border border-indigo-400/30 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-white">Status & Priority</h4>
                      <p class="text-gray-400 text-sm">Set the work order status and priority level</p>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Status Selection -->
                    <div>
                      <label class="block text-indigo-400 text-sm font-semibold mb-3">Work Order Status</label>
                      <div class="space-y-2">
                        <label v-for="status in ['Scheduled', 'In Progress', 'Pending', 'On Hold']" :key="status" 
                               class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-all"
                               :class="form.status === status ? 'bg-indigo-400/20 border border-indigo-400/30' : 'bg-white/5 hover:bg-white/10'">
                          <input
                            v-model="form.status"
                            type="radio"
                            :value="status"
                            class="w-4 h-4 text-indigo-400 bg-white/10 border-indigo-400/30 focus:ring-indigo-400/25"
                          />
                          <span class="text-white font-medium">{{ status }}</span>
                        </label>
                      </div>
                    </div>
                    
                    <!-- Priority Level -->
                    <div>
                      <label class="block text-indigo-400 text-sm font-semibold mb-3">Priority Level</label>
                      <select v-model="form.priority"
                              class="w-full p-3 bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl text-white focus:border-indigo-400/50 focus:ring-2 focus:ring-indigo-400/25 transition-all">
                        <option value="" disabled class="text-gray-500">Select priority...</option>
                        <option value="Low" class="text-gray-900">Low Priority</option>
                        <option value="Normal" class="text-gray-900">Normal Priority</option>
                        <option value="High" class="text-gray-900">High Priority</option>
                        <option value="Urgent" class="text-gray-900">Urgent</option>
                      </select>
                      
                      <!-- Priority Description -->
                      <div v-if="form.priority" class="mt-3 p-3 rounded-lg"
                           :class="{
                             'bg-green-400/10 border border-green-400/30': form.priority === 'Low',
                             'bg-blue-400/10 border border-blue-400/30': form.priority === 'Normal', 
                             'bg-yellow-400/10 border border-yellow-400/30': form.priority === 'High',
                             'bg-red-400/10 border border-red-400/30': form.priority === 'Urgent'
                           }">
                        <p class="text-sm"
                           :class="{
                             'text-green-400': form.priority === 'Low',
                             'text-blue-400': form.priority === 'Normal',
                             'text-yellow-400': form.priority === 'High', 
                             'text-red-400': form.priority === 'Urgent'
                           }">
                          {{ getPriorityDescription(form.priority) }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Status Preview -->
                <div class="bg-gradient-to-r from-indigo-400/10 to-purple-400/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                  <h5 class="text-lg font-semibold text-white mb-4">Status Summary</h5>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="text-center p-4 rounded-xl bg-white/5">
                      <p class="text-indigo-400 font-bold text-xl">{{ form.status || 'Not Set' }}</p>
                      <p class="text-gray-400 text-sm">Current Status</p>
                    </div>
                    <div class="text-center p-4 rounded-xl bg-white/5">
                      <p class="font-bold text-xl"
                         :class="{
                           'text-green-400': form.priority === 'Low',
                           'text-blue-400': form.priority === 'Normal',
                           'text-yellow-400': form.priority === 'High',
                           'text-red-400': form.priority === 'Urgent',
                           'text-gray-400': !form.priority
                         }">
                        {{ form.priority || 'Not Set' }}
                      </p>
                      <p class="text-gray-400 text-sm">Priority Level</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 9: File Attachments -->
              <div v-show="currentStep === 9" class="space-y-6">
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-400/20 border border-purple-400/30 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-white">File Attachments</h4>
                      <p class="text-gray-400 text-sm">Upload photos, documents, or reference files (optional)</p>
                    </div>
                  </div>
                  
                  <!-- File Drop Zone -->
                  <div 
                    class="border-2 border-dashed border-purple-400/30 rounded-xl p-8 text-center hover:border-purple-400/50 transition-all file-drop-zone"
                    @drop.prevent="handleFileDrop"
                    @dragover.prevent
                    @dragenter.prevent
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-purple-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="text-white font-medium mb-2">Drag and drop files here, or click to browse</p>
                    <p class="text-gray-400 text-sm">Supports: JPG, PNG, PDF, DOC, XLS (Max 10MB each)</p>
                    <input 
                      ref="fileInput"
                      type="file" 
                      multiple 
                      accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
                      @change="handleFileSelect"
                      class="hidden"
                    />
                    <button type="button" 
                            @click="$refs.fileInput.click()"
                            class="mt-4 px-6 py-2 bg-purple-400/20 hover:bg-purple-400/30 border border-purple-400/30 hover:border-purple-400/50 text-purple-400 rounded-lg transition-all">
                      Choose Files
                    </button>
                  </div>
                  
                  <!-- File List with Previews -->
                  <div v-if="form.file_attachments.length > 0" class="mt-4 space-y-2">
                    <div v-for="(file, index) in form.file_attachments" :key="index"
                         class="flex items-center justify-between p-3 bg-white/5 rounded-lg group hover:bg-white/10 transition-all">
                      <div class="flex items-center gap-3">
                        <!-- File Type Icon -->
                        <div class="w-12 h-12 rounded-lg bg-purple-400/20 border border-purple-400/30 flex items-center justify-center cursor-pointer hover:bg-purple-400/30 transition-all"
                             @click="showFilePreview(file, index)">
                          <!-- Image preview -->
                          <img v-if="file.type.startsWith('image/')" 
                               :src="file.preview" 
                               :alt="file.name"
                               class="w-full h-full object-cover rounded-lg" />
                          <!-- PDF icon -->
                          <svg v-else-if="file.type === 'application/pdf'" 
                               xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                          </svg>
                          <!-- Document icon -->
                          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                          </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                          <span class="text-white text-sm font-medium truncate block">{{ file.name }}</span>
                          <span class="text-gray-400 text-xs">{{ formatFileSize(file.size) }} • {{ getFileType(file.type) }}</span>
                        </div>
                      </div>
                      <button type="button" @click="removeFile(index)"
                              class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-300 transition-all p-1 rounded hover:bg-red-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Attachment Summary -->
                <div class="bg-gradient-to-r from-purple-400/10 to-pink-400/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                  <h5 class="text-lg font-semibold text-white mb-4">Attachments Summary</h5>
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 rounded-xl bg-white/5">
                      <p class="text-purple-400 font-bold text-xl">{{ form.file_attachments.length }}</p>
                      <p class="text-gray-400 text-sm">Total Files</p>
                    </div>
                    <div class="text-center p-4 rounded-xl bg-white/5">
                      <p class="text-purple-400 font-bold text-xl">{{ getImageCount() }}</p>
                      <p class="text-gray-400 text-sm">Images</p>
                    </div>
                    <div class="text-center p-4 rounded-xl bg-white/5">
                      <p class="text-purple-400 font-bold text-xl">{{ getDocumentCount() }}</p>
                      <p class="text-gray-400 text-sm">Documents</p>
                    </div>
                    <div class="text-center p-4 rounded-xl bg-white/5">
                      <p class="text-purple-400 font-bold text-xl">{{ formatFileSize(getTotalSize()) }}</p>
                      <p class="text-gray-400 text-sm">Total Size</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 10: Review & Summary -->
              <div v-show="currentStep === 10" class="space-y-6">
                <!-- QR Code Section -->
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-lime-400/20 border border-lime-400/30 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-white">Work Order QR Code</h4>
                      <p class="text-gray-400 text-sm">Quick access code for mobile technicians</p>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- QR Code Display -->
                    <div class="flex flex-col items-center">
                      <div class="bg-white p-4 rounded-xl shadow-lg">
                        <div ref="qrCodeElement" class="w-48 h-48"></div>
                      </div>
                      <div class="mt-4 text-center">
                        <p class="text-white font-medium">WO-{{ generateWorkOrderId() }}</p>
                        <p class="text-gray-400 text-sm">Scan for mobile access</p>
                      </div>
                    </div>
                    
                    <!-- QR Code Info -->
                    <div class="space-y-4">
                      <div class="p-4 bg-white/5 rounded-xl">
                        <h6 class="font-medium text-white mb-2">QR Code Contains:</h6>
                        <ul class="text-sm text-gray-300 space-y-1">
                          <li>• Work Order ID: WO-{{ generateWorkOrderId() }}</li>
                          <li>• Customer: {{ selectedCustomerName }}</li>
                          <li>• Address: {{ form.address }}</li>
                          <li>• Priority: {{ getPriorityDescription(form.priority) }}</li>
                          <li>• Mobile Access URL</li>
                        </ul>
                      </div>
                      
                      <div class="p-4 bg-gradient-to-r from-lime-400/10 to-cyan-400/10 rounded-xl border border-white/10">
                        <h6 class="font-medium text-white mb-2">Mobile Features:</h6>
                        <ul class="text-sm text-gray-300 space-y-1">
                          <li>• Update work order status</li>
                          <li>• Add photos and notes</li>
                          <li>• Record time tracking</li>
                          <li>• Customer signature capture</li>
                          <li>• GPS location verification</li>
                        </ul>
                      </div>
                      
                      <div class="p-4 bg-purple-400/10 rounded-xl border border-purple-400/20">
                        <div class="flex items-center gap-2 mb-2">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-1a2 2 0 00-2-2H6a2 2 0 00-2 2v1a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                          </svg>
                          <span class="font-medium text-purple-400 text-sm">Secure Access</span>
                        </div>
                        <p class="text-xs text-gray-400">QR code includes encrypted technician authentication for secure mobile access</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- GPS Location Preview Section -->
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-red-400/20 border border-red-400/30 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-white">Location Preview</h4>
                      <p class="text-gray-400 text-sm">GPS location for work order address</p>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Map Preview -->
                    <div class="bg-gray-800/50 rounded-xl overflow-hidden border border-white/10">
                      <div ref="mapContainer" class="w-full h-64 relative">
                        <iframe 
                          :src="googleMapsUrl"
                          class="w-full h-full border-0"
                          loading="lazy"
                          referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        <div v-if="!form.address" class="absolute inset-0 bg-gray-800/80 backdrop-blur-sm flex items-center justify-center">
                          <div class="text-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            <p class="text-sm">No address provided</p>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Location Details -->
                    <div class="space-y-4">
                      <div class="p-4 bg-white/5 rounded-xl">
                        <h6 class="font-medium text-white mb-2">Address Information:</h6>
                        <p class="text-sm text-gray-300">{{ form.address || 'No address provided' }}</p>
                      </div>
                      
                      <div class="p-4 bg-gradient-to-r from-red-400/10 to-orange-400/10 rounded-xl border border-white/10">
                        <h6 class="font-medium text-white mb-2">Navigation Options:</h6>
                        <div class="space-y-2">
                          <a v-if="form.address" 
                             :href="googleMapsDirectionsUrl" 
                             target="_blank"
                             class="inline-flex items-center gap-2 px-3 py-2 bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 text-sm rounded-lg transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            Google Maps
                          </a>
                          
                          <a v-if="form.address" 
                             :href="appleMapsUrl" 
                             target="_blank"
                             class="inline-flex items-center gap-2 px-3 py-2 bg-gray-500/20 hover:bg-gray-500/30 text-gray-400 text-sm rounded-lg transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            Apple Maps
                          </a>
                        </div>
                      </div>
                      
                      <div class="p-4 bg-green-400/10 rounded-xl border border-green-400/20">
                        <div class="flex items-center gap-2 mb-2">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          <span class="font-medium text-green-400 text-sm">GPS Ready</span>
                        </div>
                        <p class="text-xs text-gray-400">Location will be available for mobile technician navigation</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Print Actions Section -->
                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                  <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-400/20 border border-blue-400/30 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-white">Print Options</h4>
                      <p class="text-gray-400 text-sm">Print work order summary or QR code labels</p>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Print Work Order with Cost -->
                    <button type="button" 
                            @click="printWorkOrder('cost')"
                            class="group p-4 bg-gradient-to-r from-blue-400/10 to-cyan-400/10 hover:from-blue-400/20 hover:to-cyan-400/20 border border-blue-400/30 hover:border-blue-400/50 rounded-xl transition-all">
                      <div class="flex items-center gap-3 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400 group-hover:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        <h6 class="font-semibold text-white">Work Order (Cost)</h6>
                      </div>
                      <p class="text-sm text-gray-400">Complete work order with pricing details</p>
                    </button>

                    <!-- Print Work Order without Cost -->
                    <button type="button" 
                            @click="printWorkOrder('nocost')"
                            class="group p-4 bg-gradient-to-r from-purple-400/10 to-pink-400/10 hover:from-purple-400/20 hover:to-pink-400/20 border border-purple-400/30 hover:border-purple-400/50 rounded-xl transition-all">
                      <div class="flex items-center gap-3 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-400 group-hover:text-purple-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h6 class="font-semibold text-white">Work Order (No Cost)</h6>
                      </div>
                      <p class="text-sm text-gray-400">Work order for technician without pricing</p>
                    </button>

                    <!-- Print QR Code Label -->
                    <button type="button" 
                            @click="printQRLabel"
                            class="group p-4 bg-gradient-to-r from-green-400/10 to-emerald-400/10 hover:from-green-400/20 hover:to-emerald-400/20 border border-green-400/30 hover:border-green-400/50 rounded-xl transition-all">
                      <div class="flex items-center gap-3 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-400 group-hover:text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-6 4h4" />
                        </svg>
                        <h6 class="font-semibold text-white">QR Code Label</h6>
                      </div>
                      <p class="text-sm text-gray-400">QR code with work order summary</p>
                    </button>
                  </div>
                </div>

                <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                  <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-emerald-400/20 border border-emerald-400/30 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-white">Review & Summary</h4>
                      <p class="text-gray-400 text-sm">Verify all details before creating the work order</p>
                    </div>
                  </div>
                  
                  <!-- Summary Grid -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Customer & Basic Info -->
                    <div class="space-y-4">
                      <div class="bg-cyan-400/10 border border-cyan-400/30 rounded-lg p-4">
                        <h6 class="text-cyan-400 font-semibold mb-2">Customer & Technician</h6>
                        <p class="text-white text-sm">{{ selectedCustomerName }}</p>
                        <p class="text-gray-400 text-xs">{{ selectedTechnicianName }}</p>
                      </div>
                      
                      <div class="bg-purple-400/10 border border-purple-400/30 rounded-lg p-4">
                        <h6 class="text-purple-400 font-semibold mb-2">Work Order Details</h6>
                        <p class="text-white text-sm font-medium">{{ formattedTitle }}</p>
                        <p class="text-gray-400 text-xs">{{ descriptionSummary }}</p>
                      </div>
                      
                      <div class="bg-emerald-400/10 border border-emerald-400/30 rounded-lg p-4">
                        <h6 class="text-emerald-400 font-semibold mb-2">Schedule & Location</h6>
                        <p class="text-white text-sm">{{ formattedDateTime }}</p>
                        <p class="text-gray-400 text-xs">{{ form.address }}</p>
                      </div>
                    </div>
                    
                    <!-- Duration & Pricing -->
                    <div class="space-y-4">
                      <div class="bg-yellow-400/10 border border-yellow-400/30 rounded-lg p-4">
                        <h6 class="text-yellow-400 font-semibold mb-2">Duration & Travel</h6>
                        <p class="text-white text-sm">{{ form.hours }} hours of work</p>
                        <p v-if="form.includeTravel" class="text-gray-400 text-xs">{{ form.travelMiles }} miles travel included</p>
                        <p v-else class="text-gray-400 text-xs">No travel included</p>
                      </div>
                      
                      <div class="bg-green-400/10 border border-green-400/30 rounded-lg p-4">
                        <h6 class="text-green-400 font-semibold mb-2">Pricing Breakdown</h6>
                        <div class="space-y-1 text-sm">
                          <div class="flex justify-between">
                            <span class="text-gray-400">Labor:</span>
                            <span class="text-white">${{ laborCost }}</span>
                          </div>
                          <div v-if="form.includeTravel" class="flex justify-between">
                            <span class="text-gray-400">Travel:</span>
                            <span class="text-white">${{ travelCost }}</span>
                          </div>
                          <div class="flex justify-between font-bold border-t border-green-400/30 pt-1 mt-2">
                            <span class="text-green-400">Total:</span>
                            <span class="text-green-400">${{ totalPrice }}</span>
                          </div>
                        </div>
                      </div>
                      
                      <div class="bg-indigo-400/10 border border-indigo-400/30 rounded-lg p-4">
                        <h6 class="text-indigo-400 font-semibold mb-2">Status & Files</h6>
                        <p class="text-white text-sm">{{ form.status || 'Scheduled' }} Priority: {{ form.priority || 'Normal' }}</p>
                        <p class="text-gray-400 text-xs">{{ form.file_attachments.length }} file(s) attached</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </form>
          </div>

          <!-- Modern Footer with Navigation -->
          <div class="relative bg-gradient-to-r from-gray-900/90 to-gray-800/90 backdrop-blur-xl border-t border-white/20 p-6">
            <div class="flex items-center justify-between">
              <!-- Previous Button -->
              <button type="button" @click="previousStep" 
                      :disabled="currentStep === 1"
                      :class="[
                        'flex items-center gap-2 px-6 py-3 rounded-xl font-semibold transition-all',
                        currentStep === 1 
                          ? 'bg-gray-500/20 border border-gray-400/30 text-gray-500 cursor-not-allowed' 
                          : 'bg-gray-500/20 hover:bg-gray-500/30 border border-gray-400/30 hover:border-gray-400/50 text-gray-300 hover:text-white'
                      ]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span class="hidden sm:inline">Previous</span>
              </button>

              <!-- Step Counter -->
              <div class="text-center">
                <div class="text-sm text-gray-400">Step</div>
                <div class="text-lg font-semibold text-white">{{ currentStep }} / {{ totalSteps }}</div>
              </div>

              <!-- Next/Submit Button -->
              <button type="button" 
                      v-if="currentStep < totalSteps"
                      @click="nextStep" 
                      :disabled="!canProceedToNextStep"
                      :class="[
                        'flex items-center gap-2 px-6 py-3 rounded-xl font-semibold transition-all',
                        canProceedToNextStep 
                          ? 'bg-lime-400/20 hover:bg-lime-400/30 border border-lime-400/30 hover:border-lime-400/50 text-lime-400 hover:text-lime-300' 
                          : 'bg-gray-500/20 border border-gray-400/30 text-gray-500 cursor-not-allowed'
                      ]">
                <span class="hidden sm:inline">Next</span>
                <span class="sm:hidden">Next</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>

              <!-- Submit Button (Final Step) -->
              <button type="submit" 
                      v-else
                      @click="submitForm"
                      :disabled="!canSubmitForm"
                      :class="[
                        'flex items-center gap-2 px-6 py-3 rounded-xl font-semibold transition-all',
                        canSubmitForm 
                          ? 'bg-lime-400/20 hover:bg-lime-400/30 border border-lime-400/30 hover:border-lime-400/50 text-lime-400 hover:text-lime-300' 
                          : 'bg-gray-500/20 border border-gray-400/30 text-gray-500 cursor-not-allowed'
                      ]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="hidden sm:inline">Create Work Order</span>
                <span class="sm:hidden">Create</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>

  <!-- File Preview Modal -->
  <Transition name="modal" appear>
    <div v-if="filePreviewModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity" @click="closeFilePreview"></div>
        
        <!-- Modal panel -->
        <div class="relative inline-block align-bottom bg-gray-900/95 backdrop-blur-xl rounded-2xl border border-white/20 px-6 py-6 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:align-middle">
          <!-- Header -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-purple-400/20 border border-purple-400/30 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-white">{{ currentPreviewFile?.name }}</h3>
                <p class="text-sm text-gray-400">{{ formatFileSize(currentPreviewFile?.size || 0) }} • {{ getFileType(currentPreviewFile?.type || '') }}</p>
              </div>
            </div>
            
            <div class="flex items-center gap-2">
              <!-- Navigation buttons -->
              <button v-if="form.file_attachments.length > 1"
                      @click="previousFilePreview"
                      :disabled="previewIndex <= 0"
                      class="p-2 rounded-lg bg-gray-500/20 hover:bg-gray-500/30 border border-gray-400/30 text-gray-400 hover:text-white disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              
              <span v-if="form.file_attachments.length > 1" class="text-sm text-gray-400">
                {{ previewIndex + 1 }} / {{ form.file_attachments.length }}
              </span>
              
              <button v-if="form.file_attachments.length > 1"
                      @click="nextFilePreview"
                      :disabled="previewIndex >= form.file_attachments.length - 1"
                      class="p-2 rounded-lg bg-gray-500/20 hover:bg-gray-500/30 border border-gray-400/30 text-gray-400 hover:text-white disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
              
              <button @click="closeFilePreview"
                      class="p-2 rounded-lg bg-red-500/20 hover:bg-red-500/30 border border-red-400/30 text-red-400 hover:text-red-300 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          
          <!-- File content -->
          <div class="bg-white/5 backdrop-blur-xl rounded-xl p-6 border border-white/10 max-h-[70vh] overflow-auto">
            <!-- Image preview -->
            <div v-if="currentPreviewFile?.type.startsWith('image/')" class="flex justify-center">
              <img :src="currentPreviewFile.preview" 
                   :alt="currentPreviewFile.name"
                   class="max-w-full max-h-[60vh] object-contain rounded-lg shadow-lg" />
            </div>
            
            <!-- PDF preview placeholder -->
            <div v-else-if="currentPreviewFile?.type === 'application/pdf'" class="text-center py-12">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-purple-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
              <p class="text-white font-medium">PDF Document</p>
              <p class="text-gray-400 text-sm">{{ currentPreviewFile.name }}</p>
              <p class="text-gray-400 text-xs mt-2">PDF preview not available in modal.<br>File will be attached to work order.</p>
            </div>
            
            <!-- Other document types -->
            <div v-else class="text-center py-12">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-purple-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <p class="text-white font-medium">{{ getFileType(currentPreviewFile?.type || '') }}</p>
              <p class="text-gray-400 text-sm">{{ currentPreviewFile?.name }}</p>
              <p class="text-gray-400 text-xs mt-2">Document preview not available in modal.<br>File will be attached to work order.</p>
            </div>
          </div>
          
          <!-- Footer -->
          <div class="flex justify-between items-center mt-6">
            <button @click="removeFile(previewIndex)"
                    class="px-4 py-2 bg-red-500/20 hover:bg-red-500/30 border border-red-400/30 hover:border-red-400/50 text-red-400 hover:text-red-300 rounded-lg transition-all">
              Remove File
            </button>
            
            <div class="flex gap-2">
              <button @click="closeFilePreview"
                      class="px-6 py-2 bg-gray-500/20 hover:bg-gray-500/30 border border-gray-400/30 hover:border-gray-400/50 text-gray-400 hover:text-white rounded-lg transition-all">
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import NetworkStatusIndicator from '@/Components/NetworkStatusIndicator.vue';
import PdfThumbnail from '@/Components/PdfThumbnail.vue';
import PdfViewer from '@/Components/PdfViewer.vue';
import QRCodeVue from 'qrcode.vue'; // For Vue component
import QRCode from 'qrcode'; // For JavaScript library
import ApplicationMark from '@/Components/ApplicationMark.vue';
// Import html2pdf correctly
import html2pdf from 'html2pdf.js/dist/html2pdf.bundle.min.js';
import { Popover, PopoverTrigger, PopoverContent } from '@/Components/ui/popover';
import { format, isValid } from 'date-fns';

// Set up Axios to include CSRF token
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
axios.defaults.withCredentials = true;

// Define props for the component
const props = defineProps({
  customerId: {
    type: [String, Number],
    default: null
  },
  customerName: {
    type: String,
    default: ''
  }
});

// State management
const showModal = ref(false);

const handleShowModal = async () => {
  try {
    console.log('AddWorkOrder: handleShowModal called');
    resetForm();
    // Set modal visibility first
    showModal.value = true;

    // Initialize calendar days for the date picker
    await nextTick();
    //generateCalendarDays();

    // Load customers and technicians concurrently
    const [customersResult, techniciansResult] = await Promise.all([
      loadCustomers(),
      loadTechnicians()
    ]);

    console.log('AddWorkOrder: All data loaded', {
      customersCount: customersResult.length,
      techniciansCount: techniciansResult.length
    });
  } catch (err) {
    console.error('Error loading initial data:', err);
    // Keep the modal open even if data loading fails
  }
};

const handleHideModal = (event = null) => {
  // If event is present, check if it's a click on the backdrop
  if (event && event.target !== event.currentTarget) {
    return;
  }
  showModal.value = false;
  resetForm();
};

const currentStep = ref(1);
const totalSteps = 10;
const isDatePickerOpen = ref(false);
const customers = ref([]);
const customersArray = ref([]);
const loadError = ref(false);
const workType = ref('');
const workOrderNumber = ref('');
const location = ref('');
const isSubmitting = ref(false);
const checkingWorkOrder = ref(false);
const duplicateWorkOrderFound = ref(false);
const workOrderVerified = ref(false);
const debounceTimer = ref(null);

// Debounced check for existing work order number
const debouncedCheckExistingWorkOrder = () => {
  if (debounceTimer.value) clearTimeout(debounceTimer.value);
  debounceTimer.value = setTimeout(checkExistingWorkOrder, 500);
};

function quickSelectDate(option) {
  const now = new Date();
  let selected;
  if (option === 'today') {
    selected = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 9, 0, 0);
  } else if (option === 'tomorrow') {
    selected = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 9, 0, 0);
  } else if (option === 'nextWeek') {
    selected = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 7, 9, 0, 0);
  } else {
    selected = now;
  }
  // Directly assign the Date object since our new DatePicker accepts a Date object
  form.date_time = selected;
}

// checkExistingWorkOrder patched to handle 422 errors gracefully
const checkExistingWorkOrder = async () => {
  const number = workOrderNumber.value?.trim();
  if (!number) {
    duplicateWorkOrderFound.value = false;
    workOrderVerified.value = false;
    checkingWorkOrder.value = false;
    return;
  }
  
  // Temporarily disable API checking until backend route is implemented
  console.log('Work order number check disabled - assuming number is available:', number);
  checkingWorkOrder.value = false;
  duplicateWorkOrderFound.value = false;
  workOrderVerified.value = true;
  return;
  
  // TODO: Re-enable when backend API route /api/work-orders/check-number is implemented
  /*
  checkingWorkOrder.value = true;
  duplicateWorkOrderFound.value = false;
  workOrderVerified.value = false;
  try {
    const response = await axios.get(`/api/work-orders/check-number`, {
      params: { workOrderNumber: number }
    });
    if (response.data.exists) {
      duplicateWorkOrderFound.value = true;
      workOrderVerified.value = false;
    } else {
      duplicateWorkOrderFound.value = false;
      workOrderVerified.value = true;
    }
  } catch (e) {
    console.error('Error checking work order number:', e);
    // If the API is not available or returns an error, don't block the user
    // Assume the work order number is available
    duplicateWorkOrderFound.value = false;
    workOrderVerified.value = true;
  } finally {
    checkingWorkOrder.value = false;
  }
  */
};

const technicians = ref([]);
const showPdfViewer = ref(false);
const selectedPdf = ref(null);
const showPrintOptionsModal = ref(false); // New variable for print options modal
const qrCodeValue = computed(() => {
  // For new work orders, create a QR code that contains a scheme to open the app and includes metadata
  // This will make the QR code scannable and potentially linkable when scanned
  return 'tekdash://' +
    'workorder?' +
    'title=' + encodeURIComponent(formattedTitle.value || '') +
    (form.address ? '&address=' + encodeURIComponent(form.address) : '') +
    (formattedDateTime.value ? '&datetime=' + encodeURIComponent(formattedDateTime.value) : '') +
    '&app_url=' + encodeURIComponent(window.location.origin + '/work-orders/');
});
const selectedTechnicianName = computed(() => {
  const tech = safeTechniciansArray.value.find(t => t.id == form.technician_id);
  if (!tech) return 'None selected';
  if (tech.first_name && tech.last_name) {
    return tech.first_name + ' ' + tech.last_name + (tech.employee_id ? ' (' + tech.employee_id + ')' : '');
  }
  return tech.name || 'Technician #' + tech.id;
});
const selectedCustomerName = computed(() => {
  const customer = safeCustomersArray.value.find(c => c.id == form.customer_id);
  return customer ? (customer.business_name || customer.name || `Customer #${customer.id}`) : 'None selected';
});
const rateValues = ref([55, 60, 65, 70, 75, 80, 85, 90, 95, 100, 105, 110, 115, 120, 125, 130, 135, 140, 145, 150, 155, 160, 165, 170, 175, 180, 185, 190, 195, 200]);

// Loading states for UI
const isLoadingCustomers = ref(false);
const isLoadingTechnicians = ref(false);
const isLoading = ref(false);

const form = useForm({
  customer_id: '',
  title: '',
  description: '',
  date_time: null, // Ensure this is null, not an empty string
  end_date: '',
  visit_dates: [],
  price: '120.00',
  hourlyRate: 120, // used for UI
  hourly_rate: 120, // add this for backend compatibility
  status: 'Scheduled',
  priority: 'Normal', // Add priority field
  file_attachments: [],
  notes: '',
  progress: null,
  user_id: '',
  users_name: '',
  address: '',
  hours: 4,
  includeTravel: false,
  travelMiles: 45,
  mileageRate: 1.00,
  technician_id: '',
  grand_total: '',
});

// Mapbox loading and error state
const mapboxLoading = ref(false);
const mapboxError = ref(null);

// Mapbox integration
const mapboxAccessToken = 'pk.eyJ1Ijoibm10ZWNoIiwiYSI6ImNtYndzNG0yZTB2MTQycm9yMmxrZTJiOXYifQ.teJIWClLiWUJvvacQC3EFQ'; // TODO: Replace with your real Mapbox public token
const mapboxCoords = ref({ lat: null, lon: null });

watch(() => form.address, (newAddress) => {
  geocodeAddress(newAddress);
}, { immediate: false });

watch(() => form.date_time, (val, oldVal) => {
  if (typeof val === 'string') {
    const parsed = new Date(val);
    form.date_time = isNaN(parsed.getTime()) ? null : parsed;
  }
});

// Computed properties
const formattedTitle = computed(() => {
  if (!workType.value && !workOrderNumber.value && !location.value) {
    return '';
  }
  return `${workType.value || 'Unknown'} / ${workOrderNumber.value || 'No WO#'} / ${location.value || 'No Location'}`;
});

const laborCost = computed(() => {
  return (form.hours * form.hourlyRate).toFixed(2);
});

const travelCost = computed(() => {
  if (!form.includeTravel) return 0;
  return (form.travelMiles * form.mileageRate * 2).toFixed(2);
});

const totalPrice = computed(() => {
  const total = (parseFloat(laborCost.value) + parseFloat(travelCost.value)).toFixed(2);
  form.grand_total = total; // Ensure form field is updated
  return total;
});

// Date and time computed properties for the new inputs
const todayString = computed(() => {
  return format(new Date(), 'yyyy-MM-dd');
});

const selectedDateString = computed({
  get() {
    if (!form.date_time) return '';
    try {
      const date = form.date_time instanceof Date ? form.date_time : new Date(form.date_time);
      return isValid(date) ? format(date, 'yyyy-MM-dd') : '';
    } catch (e) {
      return '';
    }
  },
  set(value) {
    if (!value) {
      form.date_time = null;
      return;
    }
    
    // Keep existing time if we have one, otherwise default to 9:00 AM
    const existingTime = form.date_time instanceof Date ? form.date_time : null;
    const hours = existingTime ? existingTime.getHours() : 9;
    const minutes = existingTime ? existingTime.getMinutes() : 0;
    
    const newDate = new Date(value);
    newDate.setHours(hours, minutes, 0, 0);
    form.date_time = newDate;
  }
});

const selectedTimeString = computed({
  get() {
    if (!form.date_time) return '09:00';
    try {
      const date = form.date_time instanceof Date ? form.date_time : new Date(form.date_time);
      return isValid(date) ? format(date, 'HH:mm') : '09:00';
    } catch (e) {
      return '09:00';
    }
  },
  set(value) {
    if (!value) return;
    
    // Keep existing date if we have one, otherwise use today
    const existingDate = form.date_time instanceof Date ? form.date_time : new Date();
    const [hours, minutes] = value.split(':').map(Number);
    
    const newDate = new Date(existingDate);
    newDate.setHours(hours, minutes, 0, 0);
    form.date_time = newDate;
  }
});

const mapboxImageUrl = computed(() => {
  if (!mapboxCoords.value.lat || !mapboxCoords.value.lon) return null;

  const lat = mapboxCoords.value.lat;
  const lon = mapboxCoords.value.lon;
  const zoom = 14;
  const width = 600;
  const height = 300;
  const marker = `pin-l-circle+ff4400(${lon},${lat})`;

  return `https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/${marker}/${lon},${lat},${zoom},0/${width}x${height}@2x?access_token=${mapboxAccessToken}`;
});

const mapboxMapsLink = computed(() => {
  if (!mapboxCoords.value.lat || !mapboxCoords.value.lon) return '#';
  return `https://www.google.com/maps/search/?api=1&query=${mapboxCoords.value.lat},${mapboxCoords.value.lon}`;
});

const currentMonthName = computed(() => {
  return format(new Date(), 'MMMM yyyy');
});

const selectedDateFormatted = computed(() => {
  return isValid(new Date(form.date_time)) ? format(new Date(form.date_time), 'EEEE, MMMM d, yyyy') : '';
});

const formattedDateTime = computed(() => {
  if (!form.date_time) return '';
  try {
    // Handle both string and Date object
    const date = form.date_time instanceof Date ? form.date_time : new Date(form.date_time);
    if (isValid(date)) {
      return format(date, 'EEEE, MMMM d, yyyy \'at\' h:mm a');
    }
  } catch (e) {
    console.error('Error formatting date:', e);
  }
  return '';
});

const descriptionSummary = computed(() => {
  if (!form.description) return 'No description entered';
  return form.description.length > 100 ?
    form.description.substring(0, 100) + '...' :
    form.description;
});

// Safely filtered arrays to avoid null/undefined items
const safeCustomersArray = computed(() => {
  return customersArray.value.filter(customer => customer && customer.id);
});

const safeTechniciansArray = computed(() => {
  return technicians.value.filter(technician => technician && technician.id);
});

// Validation computed properties for navigation
const canProceedToNextStep = computed(() => {
  return validateCurrentStep();
});

const canSubmitForm = computed(() => {
  return validateCurrentStep() && currentStep.value === totalSteps;
});

// GPS Map URLs for location preview
const googleMapsUrl = computed(() => {
  if (!form.address) return '';
  const encodedAddress = encodeURIComponent(form.address);
  return `https://maps.google.com/maps?q=${encodedAddress}&t=&z=16&ie=UTF8&iwloc=&output=embed`;
});

const googleMapsDirectionsUrl = computed(() => {
  if (!form.address) return '#';
  const encodedAddress = encodeURIComponent(form.address);
  return `https://www.google.com/maps/dir/?api=1&destination=${encodedAddress}`;
});

const appleMapsUrl = computed(() => {
  if (!form.address) return '#';
  const encodedAddress = encodeURIComponent(form.address);
  return `https://maps.apple.com/?q=${encodedAddress}`;
});

// Helper computed property for step labels
const getStepLabel = computed(() => {
  return (step) => {
    const labels = {
      1: 'Customer & Technician',
      2: 'Title & Work Order',
      3: 'Service Description',
      4: 'Date & Time',
      5: 'Address & Location',
      6: 'Hours & Duration',
      7: 'Rates & Pricing',
      8: 'Status & Priority',
      9: 'Attachments & Files',
      10: 'Review & Summary'
    };
    return labels[step] || `Step ${step}`;
  };
});

// Helper function for priority descriptions
const getPriorityDescription = (priority) => {
  const descriptions = {
    'Low': 'Standard timeline, can be scheduled flexibly',
    'Normal': 'Regular priority, scheduled in normal queue',
    'High': 'Important work, should be prioritized',
    'Urgent': 'Critical issue, needs immediate attention'
  };
  return descriptions[priority] || '';
};

// Function to load customers from API
const loadCustomers = async () => {
  console.log('AddWorkOrder: loadCustomers started');
  isLoadingCustomers.value = true;
  try {
    const response = await axios.get('/api/customers', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    // Laravel resource collections return { data: [...] }
    const customersList = Array.isArray(response.data.data) ? response.data.data : response.data;
    console.log('AddWorkOrder: Customers loaded:', customersList.length);
    customers.value = customersList;
    customersArray.value = customersList;
    return customersList;
  } catch (error) {
    console.error('AddWorkOrder: Failed to load customers:', error);
    customers.value = [];
    customersArray.value = [];
    return [];
  } finally {
    isLoadingCustomers.value = false;
  }
};

// Function to load active technicians from API
const loadTechnicians = async () => {
  console.log('AddWorkOrder: loadTechnicians started');
  isLoadingTechnicians.value = true;
  try {
    const response = await axios.get('/api/technicians/active', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    console.log('AddWorkOrder: Technicians loaded:', response.data.length);
    technicians.value = response.data;
    return response.data;
  } catch (error) {
    console.error('AddWorkOrder: Failed to load technicians:', error);
    technicians.value = [];
    return [];
  } finally {
    isLoadingTechnicians.value = false;
  }
};

// Methods
const resetForm = () => {
  console.log('Resetting form');
  currentStep.value = 1;
  isDatePickerOpen.value = false;
  // Reset form fields
  form.customer_id = props.customerId || '';
  form.title = '';
  form.description = '';
  form.date_time = null; // Always reset to null
  form.end_date = '';
  form.visit_dates = [];
  form.price = '120.00';
  form.hourlyRate = 120;
  form.hourly_rate = 120;
  form.status = 'Scheduled';
  form.priority = 'Normal'; // Reset priority field
  form.file_attachments = [];
  form.notes = '';
  form.progress = null;
  form.user_id = '';
  form.users_name = '';
  form.address = '';
  form.hours = 4;
  form.includeTravel = false;
  form.travelMiles = 45;
  form.mileageRate = 1.00;
  form.technician_id = '';
  form.grand_total = '';
  // Reset other component state
  workType.value = '';
  workOrderNumber.value = '';
  location.value = '';
  duplicateWorkOrderFound.value = false;
  workOrderVerified.value = false;
  checkingWorkOrder.value = false;
};

const validateCurrentStep = () => {
  switch (currentStep.value) {
    case 1:
      return !!form.customer_id && String(form.customer_id).trim() !== '';
    case 2:
      return workType.value &&
        workOrderNumber.value &&
        location.value &&
        !duplicateWorkOrderFound.value;
    case 3:
      return !!form.description && form.description.trim() !== '';
    case 4:
      return !!form.date_time;
    case 5:
      return !!form.address && form.address.trim() !== '';
    case 6:
      return form.hours >= 2 && form.hours <= 16;
    case 7:
      return !!form.hourlyRate && form.hourlyRate >= 55;
    case 8:
      return !!form.status && !!form.priority;
    case 9:
      return true; // Files are optional
    case 10:
      return totalPrice.value > 0; // Check totalPrice computed value instead of form.grand_total
    default:
      return false;
  }
};

const nextStep = async () => {
  if (validateCurrentStep()) {
    isLoading.value = true;
    try {
      await new Promise(resolve => setTimeout(resolve, 100));
      if (currentStep.value < totalSteps) {
        currentStep.value++;
        // Use nextTick from Vue instead of this.$nextTick
        nextTick(() => {
          const formElement = document.querySelector('.modal-body');
          if (formElement) {
            formElement.scrollTop = 0;
          }
        });
      }
    } finally {
      isLoading.value = false;
    }
  } else {
    // Show validation error message
    const toast = useToast();
    toast.error('Please complete all required fields before proceeding.');
  }
};

const prevStep = async () => {
  isLoading.value = true;
  try {
    await new Promise(resolve => setTimeout(resolve, 100));
    if (currentStep.value > 1) {
      currentStep.value--;
      nextTick(() => {
        const formElement = document.querySelector('.modal-body');
        if (formElement) {
          formElement.scrollTop = 0;
        }
      });
    }
  } finally {
    isLoading.value = false;
  }
};

// Alias for template compatibility
const previousStep = prevStep;

// Geocode the address to get lat/lon from Mapbox
const geocodeAddress = async (address) => {
  if (!address || address.trim() === '') {
    mapboxCoords.value = { lat: null, lon: null };
    mapboxError.value = null;
    return;
  }
  mapboxLoading.value = true;
  mapboxError.value = null;
  try {
    const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(address)}.json?access_token=${mapboxAccessToken}&limit=1`;
    // Ensure withCredentials is false for Mapbox API to avoid CORS issues
    const response = await axios.get(url, { withCredentials: false });
    if (response.data.features && response.data.features.length > 0) {
      const [lon, lat] = response.data.features[0].center;
      mapboxCoords.value = { lat, lon };
    } else {
      mapboxCoords.value = { lat: null, lon: null };
      mapboxError.value = 'Address not found. Please try a more specific address.';
    }
  } catch (error) {
    console.error('Geocoding error:', error);
    mapboxCoords.value = { lat: null, lon: null };
    mapboxError.value = 'Error finding address. Please try again.';
  } finally {
    mapboxLoading.value = false;
  }
};

// Print Work Order function for PDF generation
// Generate PDF function - separated for better organization
const generatePDF = async (mode = 'cost') => {
  try {
    isLoading.value = true;
    // Generate formatted content first
    const { htmlContent, filename } = await generateWorkOrderContent(mode, true);

    // Create a temporary container for html2pdf to work with
    const element = document.createElement('div');
    // Use safe content with all modern CSS color functions replaced
    element.innerHTML = replaceModernCssColors(htmlContent);

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
        ignoreElements: (element) => {
          // Ignore elements that might cause problems
          return element.tagName === 'SCRIPT' ||
            element.classList.contains('ignore-pdf') ||
            window.getComputedStyle(element).display === 'none';
        },
        onclone: (clonedDoc) => {
          // Process the cloned document before rendering
          const styleElements = clonedDoc.querySelectorAll('style');
          styleElements.forEach(style => {
            if (style.textContent) {
              style.textContent = replaceModernCssColors(style.textContent);
            }
          });
          return clonedDoc;
        }
      },
      jsPDF: {
        unit: 'mm',
        format: 'a4',
        orientation: 'portrait',
        compress: true, // Compress the PDF
        hotfixes: ['px_scaling'] // Fix scaling issues
      }
    };

    // Generate and download PDF using html2pdf library with safer approach
    try {
      // Pre-process the element to handle any potential CSS issues
      const styleTags = element.querySelectorAll('style');
      styleTags.forEach(styleTag => {
        if (styleTag.textContent) {
          styleTag.textContent = replaceModernCssColors(styleTag.textContent);
        }
      });

      // Process inline styles on all elements
      const elementsWithStyle = element.querySelectorAll('[style]');
      elementsWithStyle.forEach(el => {
        if (el.getAttribute('style')) {
          el.setAttribute('style', replaceModernCssColors(el.getAttribute('style')));
        }
      });

      // Convert to blob with additional error handling
      try {
        const pdfBlob = await html2pdf()
          .set(options)
          .from(element)
          .outputPdf('blob');

        // Create a download link and trigger it
        const downloadLink = document.createElement('a');
        downloadLink.href = URL.createObjectURL(pdfBlob);
        downloadLink.download = filename;
        downloadLink.click();

        // Clean up the URL object to free memory
        setTimeout(() => {
          URL.revokeObjectURL(downloadLink.href);
        }, 100);
      } catch (innerError) {
        console.error('PDF generation failed:', innerError);

        // Try an alternative approach with further simplified content
        if (innerError.message && (innerError.message.includes('oklch') ||
          innerError.message.includes('color') ||
          innerError.message.includes('css'))) {

          // Try a more aggressive approach to strip all problematic styles
          const simplifiedElement = element.cloneNode(true);

          // Remove all style tags completely
          const styleElements = simplifiedElement.querySelectorAll('style');
          styleElements.forEach(styleEl => styleEl.remove());

          // Remove all inline styles
          const allElements = simplifiedElement.querySelectorAll('*');
          allElements.forEach(el => el.removeAttribute('style'));

          // Add basic styling directly
          const basicStyle = document.createElement('style');
          basicStyle.textContent = `
            body { font-family: Arial, sans-serif; color: #000; }
            .section { margin-bottom: 10px; padding: 10px; border: 1px solid #ccc; }
            .section-title { font-weight: bold; font-size: 16px; }
            .label { color: #666; font-size: 12px; }
            .value { font-weight: bold; }
          `;
          simplifiedElement.appendChild(basicStyle);

          try {
            // Try one more time with simplified content
            const pdfBlob = await html2pdf()
              .set(options)
              .from(simplifiedElement)
              .outputPdf('blob');

            const downloadLink = document.createElement('a');
            downloadLink.href = URL.createObjectURL(pdfBlob);
            downloadLink.download = filename;
            downloadLink.click();

            setTimeout(() => {
              URL.revokeObjectURL(downloadLink.href);
            }, 100);
          } catch (finalError) {
            console.error('Final PDF generation attempt failed:', finalError);
            alert('Unable to generate PDF due to style compatibility issues. Please use the print option instead.');
          }
        } else {
          alert('Unable to generate PDF. Please try the print option instead.');
        }
      }

      // Clean up the temporary element
      document.body.removeChild(element);
    } catch (error) {
      console.error('Error in generatePDF:', error);
      alert('There was an error generating the PDF. Please try again or use the print option.');
    }

    // Hide loading indicator
    isLoading.value = false;
  } catch (error) {
    console.error('Error in generatePDF:', error);
    alert('There was an error generating the PDF. Please try again.');
    isLoading.value = false;
  }
};

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
    console.log('QR code generated successfully:', qrImageUrl.substring(0, 30) + '...');
  } catch (e) {
    console.error('Error creating QR code data URL:', e);
    qrImageUrl = ''; // Empty if failed
  }

  // Use computed properties directly
  const customerName = selectedCustomerName.value;
  const technicianName = selectedTechnicianName.value;

  // Safely escape HTML content for description
  const escapeHtml = (unsafe) => {
    if (unsafe === undefined || unsafe === null) return '';
    return String(unsafe)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  };

  // Generate PDF filename based on work order title and current date
  const filename = `${escapeHtml(formattedTitle.value || 'WorkOrder').replace(/\s+/g, '_')}_${mode === 'cost' ? 'Cost' :
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
      <title>${escapeHtml(formattedTitle.value) || 'Work Order'} - ${mode === 'cost' ? 'Cost Breakdown' :
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
        <div class="label-title">${escapeHtml(formattedTitle.value) || 'Work Order'}</div>
        <div class="label-info">NM Technology</div>
        <div class="label-info">${escapeHtml(form.status) || 'Not set'}</div>
        <div class="label-info">${escapeHtml(formattedDateTime.value) ? 'Date: ' + escapeHtml(formattedDateTime.value) : ''}</div>
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
          <div class="title">Work Order: ${escapeHtml(formattedTitle.value) || 'No Title'}</div>
          <div style="font-size: 14px; color: #6b7280;">${escapeHtml(formattedDateTime.value) || 'Not scheduled'}</div>
        </div>
        <div>
          <div class="status">${escapeHtml(form.status) || 'Not set'}</div>
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
          <div class="value" style="white-space: pre-line;">${escapeHtml(form.description) || 'No description provided'}</div>
        </div>
      </div>

      <!-- Schedule & Location Section -->
      <div class="section">
        <div class="section-title">Schedule & Location</div>
        <div class="grid">
          <div>
            <div class="label">Date & Time:</div>
            <div class="value">${escapeHtml(formattedDateTime.value) || 'Not scheduled'}</div>
          </div>
          <div>
            <div class="label">Location:</div>
            <div class="value">${escapeHtml(form.address) || 'No address provided'}</div>
          </div>
        </div>
      </div>

      ${mode === 'cost' ? `
      <!-- Cost Breakdown Section -->
      <div class="section">
        <div class="section-title">Cost Breakdown</div>
        <div class="box">
          <div class="cost-item">
            <div class="cost-label">Labor (${form.hours} hours @ $${form.hourlyRate}/hr)</div>
            <div class="cost-value">$${laborCost.value || 0}</div>
          </div>
          ${form.includeTravel ? `
          <div class="cost-item">
            <div class="cost-label">Travel (${form.travelMiles} miles @ $${form.mileageRate}/mile)</div>
            <div class="cost-value">$${travelCost.value || 0}</div>
          </div>
          ` : ''}
          <div class="cost-item total-row">
            <div class="cost-label">Total</div>
            <div class="cost-value">$${totalPrice.value || 0}</div>
          </div>
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
    // Create and return the HTML content and filename
    const { htmlContent: printContent, filename: printFilename } = await generateWorkOrderContent(mode);

    // Open print window with blank target
    const printWindow = window.open('', '_blank');
    if (!printWindow) {
      alert('Please allow pop-up windows to print the work order');
      return;
    }

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

// Submit the work order form
const submitForm = async () => {
  if (!validateCurrentStep()) {
    console.error('Final validation failed');
    return;
  }

  if (isSubmitting.value) {
    console.log('Already submitting, preventing double submission');
    return;
  }

  try {
    isSubmitting.value = true;
    console.log('Submitting work order form...', form.data());

    // Get current user from Inertia props
    const { props } = usePage();
    const currentUser = props.auth?.user;
    
    if (!currentUser?.id) {
      throw new Error('User not authenticated');
    }

    // Ensure title is set
    const workOrderTitle = formattedTitle.value || `${workType.value || 'Work Order'} / ${workOrderNumber.value || 'No WO#'} / ${location.value || 'No Location'}`;

    // Update form with required fields before submission
    form.user_id = currentUser.id;
    form.title = workOrderTitle;
    form.work_type = workType.value;
    form.work_order_number = workOrderNumber.value;
    form.location = location.value;
    form.price = totalPrice.value;
    form.grand_total = totalPrice.value;
    form.hourly_rate = form.hourlyRate; // Ensure backend compatibility
    
    // Ensure required fields are not empty
    if (!form.customer_id) {
      alert('Customer is required');
      isSubmitting.value = false;
      return;
    }
    
    if (!form.technician_id) {
      alert('Technician is required');
      isSubmitting.value = false;
      return;
    }
    
    if (!form.description || form.description.trim() === '') {
      alert('Description is required');
      isSubmitting.value = false;
      return;
    }
    
    // Format date if it's a Date object
    if (form.date_time instanceof Date) {
      const pad = (n) => n.toString().padStart(2, '0');
      const year = form.date_time.getFullYear();
      const month = pad(form.date_time.getMonth() + 1);
      const day = pad(form.date_time.getDate());
      const hours = pad(form.date_time.getHours());
      const minutes = pad(form.date_time.getMinutes());
      form.date_time = `${year}-${month}-${day} ${hours}:${minutes}:00`;
    }

    console.log('Form data before submission:', {
      user_id: form.user_id,
      title: form.title,
      customer_id: form.customer_id,
      technician_id: form.technician_id,
      date_time: form.date_time,
      address: form.address,
      description: form.description?.substring(0, 50) + '...',
      hours: form.hours,
      hourlyRate: form.hourlyRate,
      hourly_rate: form.hourly_rate,
      status: form.status,
      priority: form.priority,
      price: form.price,
      grand_total: form.grand_total,
      work_type: workType.value,
      work_order_number: workOrderNumber.value,
      location: location.value,
      file_attachments_count: form.file_attachments ? form.file_attachments.length : 0
    });

    // Handle file attachments properly - remove empty file_attachments array
    console.log('File attachments check:', {
      exists: !!form.file_attachments,
      length: form.file_attachments ? form.file_attachments.length : 'undefined',
      first_file_type: form.file_attachments && form.file_attachments.length > 0 ? typeof form.file_attachments[0] : 'none',
      first_file_constructor: form.file_attachments && form.file_attachments.length > 0 ? form.file_attachments[0].constructor.name : 'none'
    });
    
    // Always start with a clean form copy without file attachments
    const formData = { ...form };
    delete formData.file_attachments;
    
    // Only include files if they exist and are valid
    const hasValidFiles = form.file_attachments && 
                         form.file_attachments.length > 0 && 
                         form.file_attachments.every(file => file instanceof File);
    
    if (!hasValidFiles) {
      console.log('Submitting without files (no valid files found)');
      
      // Use Inertia's post method to submit the form without file attachments
      router.post('/work-orders', formData, {
        onSuccess: (page) => {
          console.log('Work order created successfully:', page);
          // Reset form and close modal on success
          resetForm();
          showModal.value = false;
          // Show success message
          alert('Work order created successfully!');
          
          // Use Inertia router to refresh the current page with fresh data
          router.reload();
        },
        onError: (errors) => {
          console.error('Form submission errors (no files):', errors);
          console.error('Detailed errors:', JSON.stringify(errors, null, 2));
          isSubmitting.value = false;
          // Handle validation errors
          if (errors) {
            const errorMessages = Object.entries(errors).map(([field, messages]) => {
              const messageArray = Array.isArray(messages) ? messages : [messages];
              return `${field}: ${messageArray.join(', ')}`;
            }).join('\n');
            alert(`Please fix the following errors:\n${errorMessages}`);
          }
        },
        onFinish: () => {
          isSubmitting.value = false;
        }
      });
    } else {
      console.log('Submitting with files:', {
        file_count: form.file_attachments.length,
        files: form.file_attachments.map((file, index) => ({
          index,
          name: file.name,
          type: file.type,
          size: file.size,
          isFile: file instanceof File
        }))
      });
      
      // Validate file types
      const validTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
      const invalidFiles = form.file_attachments.filter(file => {
        return !validTypes.includes(file.type.toLowerCase());
      });
      
      if (invalidFiles.length > 0) {
        console.error('Invalid files found:', invalidFiles);
        alert('Please upload only PDF, JPG, or PNG files');
        isSubmitting.value = false;
        return;
      }
      
      // Add files to the clean form data
      formData.file_attachments = form.file_attachments;
      
      // Use Inertia's form.post method when there are files
      const fileForm = useForm(formData);
      fileForm.post('/work-orders', {
        onSuccess: (page) => {
          console.log('Work order created successfully:', page);
          // Reset form and close modal on success
          resetForm();
          showModal.value = false;
          // Show success message
          alert('Work order created successfully!');
          
          // Use Inertia router to refresh the current page with fresh data
          // This will update all components (calendar, revenue stats, etc.) without a full page reload
          router.reload();
        },
        onError: (errors) => {
          console.error('Form submission errors (with files):', errors);
          console.error('Detailed errors:', JSON.stringify(errors, null, 2));
          isSubmitting.value = false;
          // Handle validation errors
          if (errors) {
            const errorMessages = Object.entries(errors).map(([field, messages]) => {
              const messageArray = Array.isArray(messages) ? messages : [messages];
              return `${field}: ${messageArray.join(', ')}`;
            }).join('\n');
            alert(`Please fix the following errors:\n${errorMessages}`);
          }
        },
        onFinish: () => {
          isSubmitting.value = false;
        }
      });
    }
  } catch (error) {
    console.error('Error submitting work order:', error);
    isSubmitting.value = false;
    alert('There was an error creating the work order. Please try again.');
  }
};

// Handle file upload for attachments
const handleFileUpload = (event) => {
  const files = Array.from(event.target.files);
  if (files.length > 0) {
    // Add new files to existing attachments
    form.file_attachments = [...(form.file_attachments || []), ...files];
    console.log('Files uploaded:', files.length, 'Total attachments:', form.file_attachments.length);
  }
};

// Get object URL for file preview
const getFileObjectURL = (file) => {
  if (file && file instanceof File) {
    return URL.createObjectURL(file);
  }
  return '';
};

// File handling methods for Step 9
const handleFileDrop = (event) => {
  const files = Array.from(event.dataTransfer.files);
  processFiles(files);
};

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files);
  processFiles(files);
};

const processFiles = (files) => {
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf', 
                     'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                     'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
  const maxSize = 10 * 1024 * 1024; // 10MB

  files.forEach(file => {
    if (!validTypes.includes(file.type)) {
      alert(`File type ${file.type} is not supported. Please use JPG, PNG, PDF, DOC, or XLS files.`);
      return;
    }
    
    if (file.size > maxSize) {
      alert(`File ${file.name} is too large. Maximum size is 10MB.`);
      return;
    }

    // Add preview URL for images
    if (file.type.startsWith('image/')) {
      file.preview = URL.createObjectURL(file);
    }

    form.file_attachments.push(file);
  });
};

// Remove file from attachments
const removeFile = (index) => {
  const file = form.file_attachments[index];
  if (file && file.preview) {
    URL.revokeObjectURL(file.preview);
  }
  form.file_attachments.splice(index, 1);
};

// File utility functions
const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getFileType = (mimeType) => {
  const typeMap = {
    'image/jpeg': 'JPEG Image',
    'image/jpg': 'JPG Image', 
    'image/png': 'PNG Image',
    'image/gif': 'GIF Image',
    'application/pdf': 'PDF Document',
    'application/msword': 'Word Document',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'Word Document',
    'application/vnd.ms-excel': 'Excel Spreadsheet',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'Excel Spreadsheet'
  };
  return typeMap[mimeType] || 'Unknown';
};

const getImageCount = () => {
  return form.file_attachments.filter(file => file.type.startsWith('image/')).length;
};

const getDocumentCount = () => {
  return form.file_attachments.filter(file => !file.type.startsWith('image/')).length;
};

const getTotalSize = () => {
  return form.file_attachments.reduce((total, file) => total + file.size, 0);
};

// File preview modal state and methods
const filePreviewModal = ref(false);
const currentPreviewFile = ref(null);
const previewIndex = ref(-1);

const showFilePreview = (file, index) => {
  currentPreviewFile.value = file;
  previewIndex.value = index;
  filePreviewModal.value = true;
};

const closeFilePreview = () => {
  filePreviewModal.value = false;
  currentPreviewFile.value = null;
  previewIndex.value = -1;
};

const nextFilePreview = () => {
  if (previewIndex.value < form.file_attachments.length - 1) {
    previewIndex.value++;
    currentPreviewFile.value = form.file_attachments[previewIndex.value];
  }
};

const previousFilePreview = () => {
  if (previewIndex.value > 0) {
    previewIndex.value--;
    currentPreviewFile.value = form.file_attachments[previewIndex.value];
  }
};

// QR Code generation and management for Step 10
const qrCodeElement = ref(null);
const mapContainer = ref(null);
// Generate a consistent work order ID for this session
const workOrderId = ref(Math.floor(Math.random() * 100000).toString().padStart(5, '0'));

const generateWorkOrderId = () => {
  return workOrderId.value;
};

const workOrderQRValue = computed(() => {
  const woId = `WO-${generateWorkOrderId()}`;
  const baseUrl = window.location.origin;
  const mobileUrl = `${baseUrl}/mobile/work-orders/${woId}`;
  
  // Use simple URL format for better QR code compatibility
  return mobileUrl;
});

// Generate QR code when step 10 is reached
watch(currentStep, async (newStep) => {
  if (newStep === 10) {
    // Use nextTick to ensure the DOM element is ready
    await nextTick();
    if (qrCodeElement.value) {
      try {
        await generateQRCode();
      } catch (error) {
        console.error('Failed to generate QR code in watch:', error);
      }
    }
  }
});

// Also generate QR code on component mount if already on step 10
onMounted(async () => {
  if (currentStep.value === 10) {
    await nextTick();
    if (qrCodeElement.value) {
      try {
        await generateQRCode();
      } catch (error) {
        console.error('Failed to generate QR code on mount:', error);
      }
    }
  }
});

const generateQRCode = async () => {
  console.log('Generating QR code, element exists:', !!qrCodeElement.value);
  console.log('QR value:', workOrderQRValue.value);
  
  if (!qrCodeElement.value) {
    console.warn('QR code element not found');
    return;
  }
  
  try {
    // Clear existing QR code
    qrCodeElement.value.innerHTML = '';
    
    // Validate QR code value
    const qrValue = workOrderQRValue.value;
    if (!qrValue || qrValue.trim() === '') {
      throw new Error('QR code value is empty');
    }
    
    console.log('Generating QR code for value:', qrValue);
    
    // Use SVG generation for better compatibility
    const qrSvg = await QRCode.toString(qrValue, {
      type: 'svg',
      width: 192,
      margin: 1,
      errorCorrectionLevel: 'M',
      color: {
        dark: '#000000',
        light: '#FFFFFF'
      }
    });
    
    // Create a container div for the SVG
    const svgContainer = document.createElement('div');
    svgContainer.innerHTML = qrSvg;
    svgContainer.style.width = '192px';
    svgContainer.style.height = '192px';
    svgContainer.style.display = 'flex';
    svgContainer.style.alignItems = 'center';
    svgContainer.style.justifyContent = 'center';
    
    // Append the SVG container to the element
    qrCodeElement.value.appendChild(svgContainer);
    
    console.log('QR code generated successfully as SVG');
  } catch (error) {
    console.error('Error generating QR code:', error);
    // Show a fallback message with manual generation option
    qrCodeElement.value.innerHTML = `
      <div class="text-white text-sm text-center p-4 bg-yellow-500/10 rounded-lg border border-yellow-400/20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="mb-2">QR code generation failed</p>
        <p class="text-xs text-yellow-300 mb-3">Error: ${error.message}</p>
        <button onclick="this.closest('.text-center').dispatchEvent(new CustomEvent('retry-qr'))" class="px-3 py-1 bg-yellow-400/20 text-yellow-300 rounded text-xs hover:bg-yellow-400/30 transition-colors">
          Retry Generation
        </button>
      </div>
    `;
    
    // Add event listener for retry
    qrCodeElement.value.addEventListener('retry-qr', async () => {
      await generateQRCode();
    });
  }
};

const downloadQRCode = async () => {
  try {
    let dataUrl = null;
    
    // Try to get data URL from existing SVG or image element
    const svgElement = qrCodeElement.value?.querySelector('svg');
    const imgElement = qrCodeElement.value?.querySelector('img');
    
    if (svgElement) {
      // Convert SVG to data URL
      const svgString = new XMLSerializer().serializeToString(svgElement);
      const svgBlob = new Blob([svgString], { type: 'image/svg+xml' });
      
      // Create a canvas to convert SVG to PNG
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d');
      const img = new Image();
      
      return new Promise((resolve, reject) => {
        img.onload = () => {
          canvas.width = 300;
          canvas.height = 300;
          ctx.fillStyle = 'white';
          ctx.fillRect(0, 0, 300, 300);
          ctx.drawImage(img, 0, 0, 300, 300);
          
          const link = document.createElement('a');
          link.download = `WorkOrder-QR-${generateWorkOrderId()}.png`;
          link.href = canvas.toDataURL('image/png');
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          resolve();
        };
        
        img.onerror = reject;
        img.src = URL.createObjectURL(svgBlob);
      });
    } else if (imgElement && imgElement.src.startsWith('data:')) {
      dataUrl = imgElement.src;
    } else {
      // Generate QR code as data URL
      dataUrl = await QRCode.toDataURL(workOrderQRValue.value, {
        width: 300,
        margin: 2,
        errorCorrectionLevel: 'M',
        color: {
          dark: '#000000',
          light: '#FFFFFF'
        }
      });
    }
    
    if (dataUrl) {
      const link = document.createElement('a');
      link.download = `WorkOrder-QR-${generateWorkOrderId()}.png`;
      link.href = dataUrl;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  } catch (error) {
    console.error('Error downloading QR code:', error);
    alert('Error downloading QR code: ' + error.message);
  }
};

const printQRCode = async () => {
  try {
    let dataUrl = null;
    
    // Try to get data URL from existing image element
    const img = qrCodeElement.value?.querySelector('img');
    if (img && img.src.startsWith('data:')) {
      dataUrl = img.src;
    } else {
      // Generate QR code as data URL
      dataUrl = await QRCode.toDataURL(workOrderQRValue.value, {
        width: 400,
        margin: 2,
        errorCorrectionLevel: 'M',
        color: {
          dark: '#000000',
          light: '#FFFFFF'
        }
      });
    }
    
    const printWindow = window.open('', '_blank');
    if (!printWindow) {
      alert('Please allow pop-ups to print the QR code');
      return;
    }
    
    printWindow.document.write(`
      <html>
        <head>
          <title>Work Order QR Code</title>
          <style>
            body { 
              display: flex; 
              justify-content: center; 
              align-items: center; 
              height: 100vh; 
              margin: 0;
              font-family: Arial, sans-serif;
            }
            .container { text-align: center; }
            h2 { margin-bottom: 20px; }
            img { border: 1px solid #ddd; padding: 10px; }
            p { margin-top: 15px; font-size: 14px; color: #666; }
          </style>
        </head>
        <body>
          <div class="container">
            <h2>Work Order QR Code</h2>
            <img src="${dataUrl}" style="width: 300px; height: 300px;">
            <p>WO-${generateWorkOrderId()}</p>
            <p>Scan to access mobile work order</p>
          </div>
        </body>
      </html>
    `);
    printWindow.document.close();
    setTimeout(() => {
      printWindow.print();
    }, 100);
  } catch (error) {
    console.error('Error printing QR code:', error);
    alert('Error printing QR code: ' + error.message);
  }
};

const printQRLabel = async () => {
  try {
    let dataUrl = null;
    
    // Try to get data URL from existing SVG or generate new one
    const svgElement = qrCodeElement.value?.querySelector('svg');
    
    if (svgElement) {
      // Convert SVG to data URL
      const svgString = new XMLSerializer().serializeToString(svgElement);
      const svgBlob = new Blob([svgString], { type: 'image/svg+xml' });
      
      // Create a canvas to convert SVG to data URL
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d');
      const img = new Image();
      
      dataUrl = await new Promise((resolve, reject) => {
        img.onload = () => {
          canvas.width = 200;
          canvas.height = 200;
          ctx.fillStyle = 'white';
          ctx.fillRect(0, 0, 200, 200);
          ctx.drawImage(img, 0, 0, 200, 200);
          resolve(canvas.toDataURL('image/png'));
        };
        
        img.onerror = reject;
        img.src = URL.createObjectURL(svgBlob);
      });
    } else {
      // Generate QR code as data URL
      dataUrl = await QRCode.toDataURL(workOrderQRValue.value, {
        width: 200,
        margin: 1,
        errorCorrectionLevel: 'M',
        color: {
          dark: '#000000',
          light: '#FFFFFF'
        }
      });
    }
    
    const printWindow = window.open('', '_blank');
    if (!printWindow) {
      alert('Please allow pop-ups to print the QR label');
      return;
    }
    
    const workOrderId = generateWorkOrderId();
    const customerName = selectedCustomerName.value || 'No Customer Selected';
    const technicianName = selectedTechnicianName.value || 'No Technician Assigned';
    const address = form.address || 'No Address Provided';
    const priority = getPriorityDescription(form.priority);
    const dateTime = formattedDateTime.value;
    
    printWindow.document.write(`
      <html>
        <head>
          <title>Work Order QR Label - WO-${workOrderId}</title>
          <style>
            @page {
              size: 4in 3in;
              margin: 0.2in;
            }
            body { 
              font-family: Arial, sans-serif;
              margin: 0;
              padding: 10px;
              font-size: 10px;
              line-height: 1.2;
            }
            .label-container {
              width: 100%;
              height: 100%;
              border: 2px solid #000;
              padding: 8px;
              box-sizing: border-box;
            }
            .header {
              text-align: center;
              border-bottom: 1px solid #000;
              padding-bottom: 5px;
              margin-bottom: 8px;
            }
            .header h1 {
              margin: 0;
              font-size: 14px;
              font-weight: bold;
            }
            .header h2 {
              margin: 2px 0 0 0;
              font-size: 12px;
              font-weight: bold;
            }
            .content {
              display: flex;
              gap: 8px;
            }
            .qr-section {
              flex: 0 0 120px;
              text-align: center;
            }
            .qr-section img {
              width: 120px;
              height: 120px;
              border: 1px solid #ddd;
            }
            .details-section {
              flex: 1;
              font-size: 9px;
            }
            .detail-row {
              margin-bottom: 3px;
              display: flex;
            }
            .detail-label {
              font-weight: bold;
              min-width: 50px;
              margin-right: 5px;
            }
            .detail-value {
              flex: 1;
            }
            .priority {
              background: #f0f0f0;
              padding: 2px 4px;
              border-radius: 3px;
              font-weight: bold;
            }
            .footer {
              text-align: center;
              margin-top: 8px;
              padding-top: 5px;
              border-top: 1px solid #000;
              font-size: 8px;
            }
          </style>
        </head>
        <body>
          <div class="label-container">
            <div class="header">
              <h1>TekDash Work Order</h1>
              <h2>WO-${workOrderId}</h2>
            </div>
            
            <div class="content">
              <div class="qr-section">
                <img src="${dataUrl}" alt="QR Code">
                <div style="font-size: 8px; margin-top: 2px;">Scan for Mobile Access</div>
              </div>
              
              <div class="details-section">
                <div class="detail-row">
                  <div class="detail-label">Customer:</div>
                  <div class="detail-value">${customerName}</div>
                </div>
                
                <div class="detail-row">
                  <div class="detail-label">Tech:</div>
                  <div class="detail-value">${technicianName}</div>
                </div>
                
                <div class="detail-row">
                  <div class="detail-label">Address:</div>
                  <div class="detail-value">${address}</div>
                </div>
                
                <div class="detail-row">
                  <div class="detail-label">DateTime:</div>
                  <div class="detail-value">${dateTime}</div>
                </div>
                
                <div class="detail-row">
                  <div class="detail-label">Priority:</div>
                  <div class="detail-value">
                    <span class="priority">${priority}</span>
                  </div>
                </div>
                
                <div class="detail-row">
                  <div class="detail-label">Total:</div>
                  <div class="detail-value">$${totalPrice.value}</div>
                </div>
              </div>
            </div>
            
            <div class="footer">
              Generated: ${new Date().toLocaleString()} | TekDash Management System
            </div>
          </div>
        </body>
      </html>
    `);
    printWindow.document.close();
    setTimeout(() => {
      printWindow.print();
    }, 100);
  } catch (error) {
    console.error('Error printing QR label:', error);
    alert('Error printing QR label: ' + error.message);
  }
};
</script>

<style scoped>
/* Enhanced Glass Morphism Calendar Styles */
.glass-date-input, .glass-time-input {
  position: relative;
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  background: rgba(255, 255, 255, 0.1);
  box-shadow: 
    0 8px 32px 0 rgba(31, 38, 135, 0.37),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.glass-date-input:focus, .glass-time-input:focus {
  background: rgba(255, 255, 255, 0.15);
  box-shadow: 
    0 8px 32px 0 rgba(16, 185, 129, 0.25),
    0 0 0 1px rgba(16, 185, 129, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.15);
  transform: translateY(-1px);
}

.glass-date-input:hover, .glass-time-input:hover {
  background: rgba(255, 255, 255, 0.125);
  box-shadow: 
    0 8px 32px 0 rgba(31, 38, 135, 0.5),
    inset 0 1px 0 rgba(255, 255, 255, 0.12);
}

/* Enhanced Calendar Input Styling */
.glass-date-input::-webkit-calendar-picker-indicator,
.glass-time-input::-webkit-calendar-picker-indicator {
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" fill="%2310b981" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>') no-repeat center;
  background-size: 20px;
  padding: 8px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  filter: drop-shadow(0 2px 4px rgba(16, 185, 129, 0.2));
}

.glass-time-input::-webkit-calendar-picker-indicator {
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" fill="%2310b981" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>') no-repeat center;
  background-size: 20px;
}

.glass-date-input::-webkit-calendar-picker-indicator:hover,
.glass-time-input::-webkit-calendar-picker-indicator:hover {
  background-color: rgba(16, 185, 129, 0.1);
  transform: scale(1.1);
  filter: drop-shadow(0 4px 8px rgba(16, 185, 129, 0.3));
}

/* Date/Time Input Text Styling */
.glass-date-input::-webkit-datetime-edit,
.glass-time-input::-webkit-datetime-edit {
  color: #ffffff;
  font-weight: 500;
}

.glass-date-input::-webkit-datetime-edit-fields-wrapper,
.glass-time-input::-webkit-datetime-edit-fields-wrapper {
  display: flex;
  align-items: center;
  gap: 4px;
}

.glass-date-input::-webkit-datetime-edit-year-field,
.glass-date-input::-webkit-datetime-edit-month-field,
.glass-date-input::-webkit-datetime-edit-day-field,
.glass-time-input::-webkit-datetime-edit-hour-field,
.glass-time-input::-webkit-datetime-edit-minute-field {
  color: #10b981;
  font-weight: 600;
  background: rgba(16, 185, 129, 0.1);
  border-radius: 4px;
  padding: 2px 4px;
  transition: all 0.3s ease;
}

.glass-date-input::-webkit-datetime-edit-text,
.glass-time-input::-webkit-datetime-edit-text {
  color: rgba(255, 255, 255, 0.6);
  font-weight: 400;
}

/* Animation classes */
.animation-delay-1000 {
  animation-delay: 1s;
}

/* Glass morphism button enhancements */
.group:hover .bg-gradient-to-br {
  opacity: 1;
}

/* Focus glow animation */
@keyframes focus-glow {
  0%, 100% { opacity: 0; transform: scale(0.95); }
  50% { opacity: 1; transform: scale(1.05); }
}

.group:focus-within .absolute.inset-0.rounded-xl.bg-gradient-to-r {
  animation: focus-glow 2s ease-in-out infinite;
}

/* Pulse animation for background elements */
@keyframes pulse-gentle {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.1); }
}

.animate-pulse {
  animation: pulse-gentle 3s ease-in-out infinite;
}

/* Enhanced glass reflection effects */
.group:hover .bg-gradient-to-br {
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.1) 0%,
    rgba(255, 255, 255, 0.05) 50%,
    transparent 100%
  );
}

/* Existing calendar styles */
.calendar-day-button {
  font-size: 1rem;
  min-height: 40px;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.calendar-day-button:hover {
  background: rgba(55, 65, 81, 0.5);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-color: rgba(16, 185, 129, 0.3);
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.2);
}

.calendar-day-selected {
  border: 2px solid #10b981;
  background: rgba(16, 185, 129, 0.2);
  color: #6ee7b7;
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  box-shadow: 
    0 4px 16px rgba(16, 185, 129, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.calendar-day-today {
  border: 1px solid #10b981;
  background: rgba(16, 185, 129, 0.15);
  color: #fff;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
}

.date-picker-container {
  background: rgba(31, 41, 55, 0.8);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-radius: 0.75rem;
  padding: 1.5rem;
  border: 2px solid rgba(16, 185, 129, 0.3);
  box-shadow: 
    0 20px 25px -5px rgba(0, 0, 0, 0.3),
    0 10px 10px -5px rgba(0, 0, 0, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

/* Enhanced existing styles with glass morphism */
.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.75rem;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-primary {
  background: linear-gradient(135deg, rgba(139, 69, 197, 0.8), rgba(124, 58, 237, 0.8));
  color: white;
  border-color: rgba(139, 69, 197, 0.3);
}

.btn-secondary {
  background: rgba(229, 231, 235, 0.1);
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.2);
}

/* Rest of existing styles... */
.fixed {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 95vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

.bg-white {
  background-color: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}

.p-6 {
  padding: 1.5rem;
}

.rounded-lg {
  border-radius: 0.5rem;
}

.shadow-lg {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.05);
}

.mb-4 {
  margin-bottom: 1rem;
}

.text-xl {
  font-size: 1.25rem;
}

.font-bold {
  font-weight: 700;
}

.block {
  display: block;
}

.text {
  font-size: 0.875rem;
}

.font-medium {
  font-weight: 500;
}

.text-gray-700 {
  color: #374151;
}

.mt-1 {
  margin-top: 0.25rem;
}

.w-full {
  width: 90%;
}

.border {
  border-width: 1px;
}

.border-gray-300 {
  border-color: #d2d6dc;
}

.rounded-md {
  border-radius: 0.375rem;
}

.shadow-sm {
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.focus\:ring-indigo-500:focus {
  --tw-ring-color: outline-green-400;
}

.focus\:border-indigo-500:focus {
  border-color: #6cae00
}

.sm\:text-sm {
  font-size: 0.875rem;
}

.flex {
  display: flex;
}

.justify-end {
  justify-content: flex-end;
}

.mr-2 {
  margin-right: 0.5rem;
}

/* Glass morphism styles */
.glossy-card {
  display: flex;
  flex-direction: column;
  background: rgba(15, 23, 42, 0.85);
  max-width: 1400px;
  width: 98vw;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.glossy-header {
  background: linear-gradient(to right, rgba(17, 24, 39, 0.9), rgba(31, 41, 55, 0.85));
}

.glossy-footer {
  background: linear-gradient(to right, rgba(20, 30, 48, 0.95), rgba(30, 41, 59, 0.92));
  box-shadow: 0 -4px 10px -1px rgba(0, 0, 0, 0.2), 0 -2px 6px -1px rgba(0, 0, 0, 0.12);
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
  z-index: 40;
  position: sticky;
  bottom: 0;
  border-top: 1px solid rgba(75, 85, 99, 0.3);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  padding: 12px 16px;
  margin-top: auto;
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

/* Scrollable content area */
.overflow-y-auto {
  flex-grow: 1;
  overflow-y: auto;
  scrollbar-color: rgba(75, 85, 99, 0.5) rgba(17, 24, 39, 0.3);
  scrollbar-width: thin;
  padding: 0.75rem;
  max-height: 85vh;
  /* Allow more space in the modal */
  scroll-behavior: smooth;
  padding-bottom: 90px;
  /* Extra padding to account for the footer */
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: rgba(17, 24, 39, 0.3);
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: rgba(75, 85, 99, 0.5);
  border-radius: 3px;
}

/* Progress bar styling */
progress {
  height: 8px;
  border-radius: 4px;
  overflow: hidden;
}

progress::-webkit-progress-bar {
  background-color: rgba(18, 237, 84, 0.6);
  border-radius: 4px;
}

progress::-webkit-progress-value {
  background: linear-gradient(90deg, #84cc16, #65a30d);
  border-radius: 4px;
}

progress::-moz-progress-bar {
  background: linear-gradient(90deg, #84cc16, #65a30d);
  border-radius: 4px;
}

/* Responsive form fields */
@media (max-width: 1124px) {
  .glossy-section .flex {
    flex-direction: column;
  }

  .glossy-card {
    height: auto;
    max-height: 95vh;
    width: 95%;
  }

  /* Adjust padding for smaller screens */
  .px-6 {
    padding-left: 1rem;
    padding-right: 1rem;
  }
}

/* Enhanced glossy card and footer styles */
.glossy-card {
  display: flex;
  flex-direction: column;
  background: rgba(15, 23, 42, 0.85);
  max-width: 1400px;
  width: 98vw;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.glossy-header {
  background: linear-gradient(to right, rgba(17, 24, 39, 0.9), rgba(31, 41, 55, 0.85));
}

.glossy-footer {
  background: linear-gradient(to right, rgba(20, 30, 48, 0.95), rgba(30, 41, 59, 0.92));
  box-shadow: 0 -4px 10px -1px rgba(0, 0, 0, 0.2), 0 -2px 6px -1px rgba(0, 0, 0, 0.12);
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
  z-index: 40;
  position: sticky;
  bottom: 0;
  border-top: 1px solid rgba(75, 85, 99, 0.3);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  padding: 12px 16px;
  margin-top: auto;
}

/* Glass button styling */
.glass-button {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.8);
  font-weight: 500;
  transition: all 0.2s ease;
}

.glass-button:hover {
  background: rgba(139, 92, 246, 0.3);
  border-color: rgba(139, 92, 246, 0.5);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
  color: rgb(255, 255, 255);
}

.glass-button:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.6);
}

.glass-button:active {
  transform: translateY(0);
  background: rgba(139, 92, 246, 0.4);
}

/* Add additional space for content */
.glossy-section {
  margin-bottom: 1rem;
}

/* Enhanced step component styling */
.steps-horizontal {
  margin-top: 0.75rem;
  padding-bottom: 1.5rem;
  margin-bottom: 0.75rem;
}

.steps .step:before {
  background-color: rgba(75, 85, 99, 0.4);
  border-color: rgba(75, 85, 99, 0.6);
  z-index: 1;
}

.steps .step:after,
.steps .step>.step-icon {
  height: 1.5rem;
  width: 1.5rem;
  font-weight: bold;
  transition: all 0.3s ease;
  box-shadow: 0 0 0 3px rgba(132, 204, 22, 0.05);
  z-index: 5;
}

.steps .step.step-success:before {
  background-color: #84cc16;
  border-color: #65a30d;
}

.steps .step.step-lime-400:after,
.steps .step.step-lime-400>.step-icon {
  background-color: #84cc16;
  border-color: #65a30d;
  color: #111827;
  box-shadow: 0 0 10px rgba(132, 204, 22, 0.5);
}

.steps .step.step-success:after,
.steps .step.step-success>.step-icon {
  background-color: #4ade80;
  border-color: #22c55e;
  color: #111827;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Step title styles for better visibility */
.steps .step .absolute {
  background-color: rgba(17, 24, 39, 0.7);
  padding: 2px 6px;
  border-radius: 4px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
  font-weight: 600;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* PDF Thumbnail styles */
.pdf-thumbnail-wrapper {
  width: 100%;
  position: relative;
  transition: all 0.2s ease;
}

.pdf-thumbnail {
  width: 100%;
  border-radius: 0.375rem;
  overflow: hidden;
  background-color: rgba(31, 41, 55, 0.6);
  border: 1px solid rgba(75, 85, 99, 0.5);
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 150px;
  transition: all 0.2s ease;
  cursor: pointer;
}

.pdf-thumbnail:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
  border-color: rgba(132, 204, 22, 0.5);
}

.pdf-thumbnail .thumbnail-canvas {
  max-width: 100%;
  max-height: 120px;
  object-fit: contain;
}

.pdf-thumbnail .pdf-icon {
  width: 36px;
  height: 36px;
  color: #84cc16;
  margin-bottom: 8px;
}

.pdf-thumbnail .filename {
  font-size: 0.75rem;
  color: white;
  text-align: center;
  padding: 4px 8px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 90%;
  background-color: rgba(31, 41, 55, 0.8);
  border-radius: 4px;
  position: absolute;
  bottom: 8px;
}

/* QR Code styling */
.qr-code-container {
  background: white;
  padding: 12px;
  border-radius: 8px;
  display: inline-flex;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  transition: transform 0.2s ease;
}

.qr-code-container:hover {
  transform: scale(1.02);
}

/* Status badge in Step 10 */
.summary-status-badge {
  padding: 6px 12px;
  font-weight: 500;
  border-radius: 6px;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
  font-size: 0.875rem;
}

/* Responsive modal width and padding */
.responsive-modal {
  max-width: 100vw;
  width: 100vw;
  min-width: 0;
  border-radius: 0.75rem;
}
@media (min-width: 640px) {
  .responsive-modal {
    max-width: 600px;
    width: 95vw;
  }
}
@media (min-width: 768px) {
  .responsive-modal {
    max-width: 900px;
    width: 90vw;
  }
}
@media (min-width: 1024px) {
  .responsive-modal {
    max-width: 1200px;
    width: 80vw;
  }
}

/* Ensure modal is usable on small screens */
@media (max-width: 640px) {
  .modal-header {
    padding-bottom: 10px;
    padding-left: 1rem;
    padding-right: 1rem;
  }
  .modal-body {
    margin-top: 90px;
    padding-left: 1rem;
    padding-right: 1rem;
  }
  .modal-footer {
    padding-left: 1rem;
    padding-right: 1rem;
    height: 64px;
  }
}

/* Steps horizontal scroll for mobile */
.steps-horizontal {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.no-scrollbar {
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.no-scrollbar::-webkit-scrollbar {
  display: none;
}

/* Responsive form scroll area */
.responsive-form-scroll {
  max-height: calc(90vh - 140px);
  min-height: 300px;
}
@media (max-width: 600px) {
  .responsive-form-scroll {
    max-height: calc(90vh - 100px);
    padding-bottom: 60px;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
  }
}

/* Responsive header/footer paddings */
.glossy-header, .glossy-footer {
  padding-left: 0.5rem;
  padding-right: 0.5rem;
}
@media (min-width: 640px) {
  .glossy-header, .glossy-footer {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }
}

/* Responsive font sizes for steps and labels */
@media (max-width: 600px) {
  .steps-horizontal .step .text-xs {
    font-size: 0.7rem;
  }
  .glossy-header h3 {
    font-size: 1.2rem;
  }
}
@media (min-width: 640px) {
  .steps-horizontal .step .text-xs {
    font-size: 0.85rem;
  }
  .glossy-header h3 {
    font-size: 1.5rem;
  }
}
@media (min-width: 1024px) {
  .steps-horizontal .step .text-xs {
    font-size: 1rem;
  }
  .glossy-header h3 {
    font-size: 1.8rem;
  }
}

.modal-header {
  background: linear-gradient(to right, rgba(17, 24, 39, 0.9), rgba(31, 41, 55, 0.85));
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  top: 0;
  left: 0;
  right: 0;
  width: 100%;
  position: absolute;
  z-index: 1001;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  justify-content: flex-start;
}

.modal-footer {
  background: linear-gradient(to right, rgba(20, 30, 48, 0.9), rgba(30, 41, 59, 0.85));
  box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  bottom: 0;
  left: 0;
  right: 0;
  width: 100%;
  position: absolute;
  z-index: 1001;
  height: 72px;
  display: flex;
  align-items: center;
}

/* Modal content positioning helpers */
.responsive-modal {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-body::-webkit-scrollbar {
  width: 5px;
}

.modal-body::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
}

.modal-body::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}

.scrollable-modal-content {
  overflow-y: auto;
  flex: 1 1 auto;
  min-height: 0;
  max-height: calc(90vh - 200px);
}
@media (max-width: 600px) {
  .scrollable-modal-content {
    max-height: calc(90vh - 180px);
  }
}

/* Custom range slider styles for glass morphism */
.slider {
  -webkit-appearance: none;
  appearance: none;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  outline: none;
  border-radius: 8px;
}

.slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: linear-gradient(135deg, #eab308, #f59e0b);
  cursor: pointer;
  border: 2px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 4px 12px rgba(234, 179, 8, 0.3);
}

.slider::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: linear-gradient(135deg, #eab308, #f59e0b);
  cursor: pointer;
  border: 2px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 4px 12px rgba(234, 179, 8, 0.3);
}

.slider::-webkit-slider-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  height: 8px;
}

.slider::-moz-range-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  height: 8px;
}

/* Priority indicator styles */
.priority-low { color: #4ade80; }
.priority-normal { color: #60a5fa; }
.priority-high { color: #facc15; }
.priority-urgent { color: #f87171; }

/* File upload styles */
.file-drop-zone:hover {
  border-color: rgba(168, 85, 247, 0.5);
  background-color: rgba(168, 85, 247, 0.05);
}

/* Step transition animations */
.step-enter-active,
.step-leave-active {
  transition: all 0.3s ease-in-out;
}

.step-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.step-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}
</style>