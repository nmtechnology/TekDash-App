<template>
  <div>
    <button @click="openCreateModal" class="btn flex items-center gap-2 px-4 py-2 font-bold text-sm text-blue-400 transition-all duration-300">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Add Work Order
    </button>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70">
      <div class="glossy-card rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full mt-4">
        <div class="glossy-header px-4 pt-5 pb-4">
          <div class="flex justify-between items-center">
            <h3 class="text-lime-400 text-2xl leading-6 font-medium" id="modal-title">
              Add Work Order
            </h3>
            <button 
              @click="showModal = false" 
              class="btn btn-circle btn-outline ml-4 text-gray-900 hover:text-lime-400 transition-colors duration-200 focus:outline-none"
              aria-label="Close modal"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Steps component -->
        <ul class="steps mb-4">
          <li v-for="step in totalSteps" :key="step" 
              class="step" 
              :class="{'step-info': step <= currentStep, 'step-error': step > currentStep}"
              :data-content="step === currentStep ? '✓' : ''">
            Step {{ step }}
          </li>
        </ul>

        <div class="overflow-y-auto px-4 py-5 bg-gray-900 bg-opacity-90">
          <form @submit.prevent="submitForm">
            <div v-if="currentStep === 1">
              <div class="glossy-section mb-4">
                <label for="customer_id" class="text-green-400 block text-sm font-medium">Customer</label><p class="text-sm text-white">Choose a customer from the dropdown menu below.</p>
                <select v-model="form.customer_id" id="customer_id" class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                  <option value="Advanced Project Solutions">Advanced Project Solutions</option>
                  <option value="Barrister Global Service Network">Barrister Global Service Network</option>
                  <option value="Bass-Security">Bass-Security</option>
                  <option value="Actron Security">Actron Security</option>
                  <option value="Field Nation">Field Nation</option>
                  <option value="Navco">Navco</option>
                  <option value="NuTech National">NuTech National</option>
                  <option value="Telaid">Telaid</option>
                </select>
              </div>
            </div>

            <div v-if="currentStep === 2">
              <div class="glossy-section mb-4">
                <h3 class="text-lime-400 text-lg font-medium mb-2">Title</h3>
                <!-- Work order title fields -->
                <p class="text-sm text-white">This section will generate a title for TekDash to find it in our system. Work order #'s cannot be used twice and the work order should have been duplicated if this is a return trip instead of creating a new work order. Location name is not an address, it is the name of the business the technician will be at, so for example if it is for Wal-Mart, then the tech knows to look for a WalMart when driving. </p>
                
                <div class="flex flex-col md:flex-row md:gap-4">
                  
                  <div class="mb-4 p-2 w-full">
                    <label for="workOrderNumber" class="block text-sm font-medium text-green-400">Work Order Number</label>
                    <div class="relative">
                      <input 
                        placeholder="WO123456-01" 
                        type="text" 
                        v-model="workOrderNumber" 
                        @blur="checkExistingWorkOrder" 
                        id="workOrderNumber" 
                        class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" 
                        :class="{'border-red-500': duplicateWorkOrderFound}"
                        required
                      >
                      <div v-if="checkingWorkOrder" class="absolute right-3 top-2">
                        <span class="text-yellow-400 text-xs">Checking...</span>
                      </div>
                      <div v-if="duplicateWorkOrderFound" class="text-red-500 text-xs mt-1">
                        Work order with this number already exists.
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-4 p-2 w-full">
                    <label for="workType" class="block text-sm font-medium text-green-400">Work Type</label>
                    <select v-model="workType" id="workType" class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                      <option value="" disabled>Select work type</option>
                      <option value="CCTV">CCTV</option>
                      <option value="ALARM">ALARM</option>
                      <option value="CABLING">CABLING</option>
                      <option value="POS">POS</option>
                      <option value="INTERCOM">INTERCOM</option>
                      <option value="FIRE">FIRE</option>
                      <option value="ACCESS-CONTROL">ACCESS-CONTROL</option>
                      <option value="TS">TS</option>
                      <option value="ESTIMATE">ESTIMATE</option>
                      <option value="WALK-THRU">WALK-THRU</option>
                      <option value="MEETING">MEETING</option>
                      <option value="APPT">APPT</option>
                      <option value="PERSONAL-LEAVE">PERSONAL-LEAVE</option>
                    </select>
                  </div>
                </div>
                  <div class="mb-4 p-2 w-full mr-1">
                  <label for="location" class="block text-sm font-medium text-green-400">Location/Business name</label>
                  <select v-model="location" id="location" class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required>
                    <option value="" disabled>Select business name</option>
                    <option value="Chili's">Chili's</option>
                    <option value="Brinks">Brinks</option>
                    <option value="Chase">Chase</option>
                    <option value="Dillard's">Dillard's</option>
                    <option value="DutchBros">DutchBros</option>
                    <option value="RaisingCaines">RaisingCaines</option>
                    <option value="ChaseATM">ChaseATM</option>
                    <option value="WalMart">WalMart</option>
                    <option value="Target">Target</option>
                    <option value="Whataburger">Whataburger</option>
                    <option value="MisterCarwash">MisterCarwash</option>
                    <option value="WellsFargo">WellsFargo</option>
                    <option value="NewSite">NewSite</option>
                  </select>
                  </div>
                <!-- Preview of combined title -->
                <div>
                  <label class="block text-sm font-medium text-green-400">Generated Title</label>
                  <div class="text-lime-400 mt-1 px-3 py-2 rounded-md glossy-content">
                    {{ formattedTitle }}
                  </div>
                </div>
              </div>
            </div>
            
            <div v-if="currentStep === 3" class="glossy-section mb-4">
              <label for="description" class="block text-sm font-medium text-green-400">Service Description</label><p class="text-sm text-white">Enter the details of what was requested on the work order sent by the customer, here's a hint, you can copy and paste the description from the work orders into here which saves you time.</p>
              <textarea v-model="form.description" id="description" placeholder="What is the Field Technician doing onsite?" class="glossy-content text-lime-400 inline-block mt-1 p-2 mr-3 rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" required></textarea>
            </div>

            <div v-if="currentStep === 4" class="glossy-section mb-4">
              <label for="date_time" class="block text-sm font-medium text-green-400">Date and Time Selection</label>

              <!-- Cally Date Picker -->
              <calendar-date 
                class="cally bg-base-100 border border-base-300 shadow-lg rounded-box mt-2"
                @input="(value) => form.date_time = value"
              >
                <svg aria-label="Previous" class="fill-current size-4" slot="previous" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M15.75 19.5 8.25 12l7.5-7.5"></path></svg>
                <svg aria-label="Next" class="fill-current size-4" slot="next" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path></svg>
                <calendar-month></calendar-month>
              </calendar-date>

              <!-- Time Selector -->
              <div class="flex items-center gap-2 mt-4">
                <select v-model="selectedTime.hour" class="glossy-content text-lime-400 rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm">
                  <option v-for="hour in 12" :key="hour" :value="hour">{{ hour }}</option>
                </select>
                <span class="text-white">:</span>
                <select v-model="selectedTime.minute" class="glossy-content text-lime-400 rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm">
                  <option v-for="minute in 60" :key="minute" :value="minute < 10 ? '0' + minute : minute">{{ minute < 10 ? '0' + minute : minute }}</option>
                </select>
                <select v-model="selectedTime.period" class="glossy-content text-lime-400 rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm">
                  <option value="AM">AM</option>
                  <option value="PM">PM</option>
                </select>
              </div>
            </div>

            <!-- New address field -->
            <div v-if="currentStep === 5" class="glossy-section mb-4">
              <label for="address" class="block text-sm font-medium text-green-400">Work Site Address</label><p class="text-sm text-white">Enter the address for the site where the technician needs to be ON TIME!</p>
              <input 
                type="text" 
                v-model="form.address" 
                id="address" 
                placeholder="Enter complete work site address" 
                class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-sm" 
                required
              >
            </div>

            <!-- New hours field -->
            <div v-if="currentStep === 6" class="glossy-section mb-4">
              <label for="hours" class="block text-sm font-medium text-green-400">Approved Hours</label>
              <p class="text-sm text-white mb-4">Enter the amount of hours approved by the customer, if we need more time onsite then we will call at that time to request the estimated hours to complete the WO. This is usually about 2-4 hours for our first trip.</p>
              
              <!-- Quick selection tiles -->
              <div class="grid grid-cols-4 gap-4 mb-6">
                <button 
                  v-for="hours in [4, 8, 12, 16]" 
                  :key="hours"
                  type="button"
                  @click="form.hours = hours"
                  :class="{
                    'bg-lime-600': form.hours === hours,
                    'hover:bg-lime-700': form.hours === hours,
                    'bg-gray-800 hover:bg-gray-700': form.hours !== hours
                  }"
                  class="glossy-content p-4 rounded-lg text-lime-400 font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-opacity-50"
                >
                  {{ hours }}h
                </button>
              </div>

              <!-- Slider -->
              <div class="mt-6">
                <div class="flex justify-between text-lime-400 text-sm mb-2">
                  <span>2h</span>
                  <span>16h</span>
                </div>
                <input 
                  type="range" 
                  v-model="form.hours" 
                  id="hours" 
                  min="2"
                  max="16"
                  step="2"
                  class="slider w-full"
                  required
                >
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                  <template v-for="value in [2, 4, 6, 8, 10, 12, 14, 16]" :key="value">
                    <span class="relative" style="left: -4px">|</span>
                  </template>
                </div>
              </div>

              <!-- Current value display -->
              <div class="text-center mt-4">
                <span class="text-lime-400 text-lg font-medium">{{ form.hours }} hours</span>
              </div>
            </div>
            
            <div v-if="currentStep === 7" class="glossy-section mb-4">
              <label for="hourlyRate" class="block text-sm font-medium text-green-400">Hourly Rate</label>
              <p class="text-sm text-white mb-4">Select the hourly rate. The total price will be calculated based on the approved hours.</p>
              
              <!-- Quick selection tiles -->
              <div class="grid grid-cols-4 gap-4 mb-6">
                <button 
                  v-for="rate in [95, 120, 130, 180]" 
                  :key="rate"
                  type="button"
                  @click="form.hourlyRate = rate"
                  :class="{
                    'bg-purple-600': form.hourlyRate === rate,
                    'hover:bg-purple-700': form.hourlyRate === rate,
                    'bg-gray-800 hover:bg-gray-700': form.hourlyRate !== rate
                  }"
                  class="glossy-content p-4 rounded-lg text-purple-400 font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50"
                >
                  ${{ rate }}/hr
                </button>
              </div>

              <!-- Slider with purple theme -->
              <div class="mt-6">
                <div class="flex justify-between text-purple-400 text-sm mb-2">
                  <span>$55/hr</span>
                  <span>$200/hr</span>
                </div>
                <input 
                  type="range" 
                  v-model="form.hourlyRate" 
                  id="hourlyRate" 
                  min="55"
                  max="200"
                  step="5"
                  class="slider-purple w-full"
                  required
                >
                <div class="flex justify-between text-xs text-gray-400 mt-1 overflow-x-auto">
                  <template v-for="value in rateValues" :key="value">
                    <span class="relative text-center whitespace-nowrap" style="min-width: 20px">|</span>
                  </template>
                </div>
              </div>

              <!-- Travel expense section -->
              <div class="mt-8 p-4 rounded-lg bg-gray-800/30 border border-gray-700">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input 
                    type="checkbox" 
                    v-model="form.includeTravel" 
                    class="checkbox checkbox-success"
                  />
                  <span class="text-white">Include Travel Expense</span>
                </label>
                
                <!-- Travel expense configuration (shows when checkbox is checked) -->
                <div v-if="form.includeTravel" class="mt-4">
                  <!-- Mileage rate quick selection tiles -->
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-purple-400 mb-2">Rate per Mile</label>
                    <div class="grid grid-cols-5 gap-2">
                      <button 
                        v-for="rate in [1.00, 0.75, 0.65, 0.50, 0.45]" 
                        :key="rate"
                        type="button"
                        @click="form.mileageRate = rate"
                        :class="{
                          'bg-purple-600': form.mileageRate === rate,
                          'hover:bg-purple-700': form.mileageRate === rate,
                          'bg-gray-800 hover:bg-gray-700': form.mileageRate !== rate
                        }"
                        class="glossy-content p-2 rounded-lg text-purple-400 font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 text-sm"
                      >
                        ${{ rate.toFixed(2) }}
                      </button>
                    </div>
                  </div>

                  <!-- Travel distance slider -->
                  <div class="mt-6">
                    <label class="block text-sm font-medium text-purple-400 mb-2">Distance (One Way)</label>
                    <div class="flex justify-between text-purple-400 text-sm mb-2">
                      <span>45 miles</span>
                      <span>400 miles</span>
                    </div>
                    <input 
                      type="range" 
                      v-model="form.travelMiles" 
                      min="45"
                      max="400"
                      step="5"
                      class="slider-purple w-full"
                    />
                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                      <template v-for="value in [45, 100, 150, 200, 250, 300, 350, 400]" :key="value">
                        <span class="relative" style="left: -4px">|</span>
                      </template>
                    </div>
                  </div>

                  <!-- Travel cost calculation -->
                  <div class="text-center mt-4 p-3 rounded-lg bg-gray-800/50">
                    <div class="flex flex-col gap-1 text-sm">
                      <div>
                        <span class="text-gray-400">{{ form.travelMiles }} miles × </span>
                        <span class="text-purple-400">${{ form.mileageRate.toFixed(2) }}/mile</span>
                        <span class="text-gray-400"> × 2 (round-trip)</span>
                      </div>
                      <div class="text-lg font-medium text-purple-400">
                        ${{ travelCost }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Current rate and total price display -->
              <div class="flex flex-col items-center mt-6 space-y-2">
                <span class="text-purple-400 text-lg font-medium">${{ form.hourlyRate }}/hr</span>
                <div class="text-white text-sm">
                  <div class="flex flex-col items-center gap-1">
                    <div>
                      <span class="text-purple-400">${{ form.hourlyRate }}</span> × 
                      <span class="text-lime-400">{{ form.hours }} hours</span> = 
                      <span class="text-purple-400">${{ laborCost }}</span>
                    </div>
                    <div v-if="form.includeTravel">
                      <span class="text-gray-400">+</span>
                      <span class="text-purple-400">${{ travelCost }}</span>
                      <span class="text-gray-400">travel</span>
                    </div>
                    <div class="text-xl font-bold mt-2">
                      <span class="text-gray-400">Total:</span>
                      <span class="text-purple-400">${{ totalPrice }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-if="currentStep === 8" class="glossy-section mb-4">
              <label class="block text-sm font-medium text-green-400 mb-4">Status</label>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <button 
                  v-for="status in ['Scheduled', 'In Progress', 'Part/Return', 'Complete', 'Cancelled']" 
                  :key="status"
                  type="button"
                  @click="form.status = status"
                  class="status-badge flex items-center justify-center gap-2 p-3 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-opacity-50"
                  :class="{
                    'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500': status === 'Scheduled' && form.status === status,
                    'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500': status === 'In Progress' && form.status === status,
                    'bg-orange-600 hover:bg-orange-700 focus:ring-orange-500': status === 'Part/Return' && form.status === status,
                    'bg-green-600 hover:bg-green-700 focus:ring-green-500': status === 'Complete' && form.status === status,
                    'bg-red-600 hover:bg-red-700 focus:ring-red-500': status === 'Cancelled' && form.status === status,
                    'bg-gray-800 hover:bg-gray-700': form.status !== status,
                    'glossy-content': true
                  }"
                >
                  <span class="text-white font-medium">{{ status }}</span>
                  <svg 
                    v-if="form.status === status" 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-5 w-5 text-white" 
                    viewBox="0 0 20 20" 
                    fill="currentColor"
                  >
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>
            
            <div v-if="currentStep === 9" class="glossy-section mb-4">
              <label class="block text-sm font-medium text-green-400 mb-2">Attachments</label>
              <p class="text-sm text-white mb-4">Upload any relevant files or documents for this work order.</p>
              
              <div class="form-control">
                <label class="label cursor-pointer flex items-center justify-center p-8 border-2 border-dashed border-gray-600 rounded-lg hover:border-lime-400 transition-colors duration-200">
                  <input 
                    type="file" 
                    @change="handleFileUpload" 
                    id="file_attachments" 
                    class="file-input file-input-bordered file-input-success w-full max-w-lg bg-gray-800/50 text-lime-400"
                    multiple
                  />
                </label>
                
                <!-- File list preview -->
                <div v-if="form.file_attachments && form.file_attachments.length > 0" class="mt-4">
                  <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div v-for="(file, index) in form.file_attachments" 
                         :key="index" 
                         class="relative group">
                      <div class="aspect-square rounded-lg overflow-hidden bg-gray-800/50">
                        <!-- PDF Preview -->
                        <PDFThumbnail
                          v-if="file.type === 'application/pdf'"
                          :file="file"
                          class="w-full h-full object-cover"
                        />
                        <!-- Image Preview -->
                        <img
                          v-else-if="file.type.startsWith('image/')"
                          :src="URL.createObjectURL(file)"
                          :alt="file.name"
                          class="w-full h-full object-cover"
                        />
                        <!-- Default File Icon -->
                        <div v-else class="w-full h-full flex items-center justify-center">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                          </svg>
                        </div>
                      </div>
                      
                      <!-- File Info Overlay -->
                      <div class="absolute inset-0 bg-gray-900 bg-opacity-0 group-hover:bg-opacity-70 transition-all duration-200 flex flex-col justify-between p-2 rounded-lg">
                        <div class="text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200 text-sm truncate">
                          {{ file.name }}
                        </div>
                        <button 
                          @click="removeFile(index)"
                          class="btn btn-circle btn-xs btn-error opacity-0 group-hover:opacity-100 transition-opacity duration-200 self-end"
                          type="button"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div v-if="form.errors.file_attachments" class="text-red-500 text-xs mt-2">
                {{ form.errors.file_attachments }}
              </div>
            </div>

            <!-- Add error message for user_id -->
            <div v-if="form.errors.user_id" class="text-red-500 text-xs mt-1 mb-4">
              {{ form.errors.user_id }}
            </div>

            <input type="hidden" v-model="form.user_id" />
            <progress v-if="form.progress" :value="form.progress.percentage" min="0" max="100" class="w-full rounded-md">
              {{ form.progress.percentage }}%
            </progress>

            <div class="flex justify-between mt-4">
              <button 
                v-if="currentStep > 1" 
                type="button" 
                @click="currentStep--" 
                class="btn btn-secondary"
              >
                Back
              </button>
              <button 
                v-if="currentStep < totalSteps" 
                type="button" 
                @click="currentStep++" 
                class="btn btn-primary"
              >
                Next
              </button>
              <button 
                v-if="currentStep === totalSteps" 
                type="submit" 
                class="btn btn-success"
              >
                Submit
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useForm, router } from '@inertiajs/vue3';
import NetworkStatusIndicator from '@/Components/NetworkStatusIndicator.vue';
import PDFThumbnail from '@/Components/PDFThumbnail.vue';
import axios from 'axios';
import 'cally';

export default {
  components: {
    NetworkStatusIndicator,
    PDFThumbnail,
  },
  props: {
    auth: Object,
  },
  data() {
    return {
      showModal: false,
      currentStep: 1,
      totalSteps: 9,
      workType: '',
      workOrderNumber: '',
      location: '',
      dateSelectionType: 'single',
      selectedDates: [{ date: new Date().toISOString().slice(0, 16) }],
      isSubmitting: false,
      checkingWorkOrder: false,
      duplicateWorkOrderFound: false,
      debounceTimer: null,
      form: useForm({
        customer_id: '',
        title: '',
        description: '',
        date_time: '',
        end_date: '',
        visit_dates: [],
        price: '120.00',
        hourlyRate: 120,
        status: 'Scheduled',
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
      }),
      selectedTime: {
        hour: 12,
        minute: '00',
        period: 'AM',
      },
    };
  },
  watch: {
    workOrderNumber(newValue) {
      // Clear previous validation
      this.duplicateWorkOrderFound = false;
      
      // Debounce the check to avoid too many requests
      clearTimeout(this.debounceTimer);
      
      // Only check if there's a value and it's at least 3 characters
      if (newValue && newValue.length >= 3) {
        this.debounceTimer = setTimeout(() => {
          this.checkExistingWorkOrder();
        }, 500);
      }
    },
    // Update form.date_time when time changes
    selectedTime: {
      handler(newTime) {
        const [year, month, day] = this.form.date_time.split('-');
        const hour = newTime.period === 'PM' && newTime.hour !== 12 ? newTime.hour + 12 : newTime.hour;
        const minute = newTime.minute;
        this.form.date_time = `${year}-${month}-${day}T${hour}:${minute}`;
      },
      deep: true,
    },
    // Update price when hourly rate changes
    'form.hourlyRate': {
      handler(newRate) {
        this.form.price = (newRate * this.form.hours).toFixed(2);
      },
      immediate: true
    },
    // Update price when hours change
    'form.hours': {
      handler(newHours) {
        this.form.price = (this.form.hourlyRate * newHours).toFixed(2);
      },
      immediate: true
    },
    'form.hourlyRate': {
      handler(newRate) {
        this.form.price = (newRate * this.form.hours).toFixed(2);
      },
      immediate: true
    },
    'form.hours': {
      handler(newHours) {
        this.form.price = (this.form.hourlyRate * newHours).toFixed(2);
      },
      immediate: true
    }
  },    computed: {
      formattedTitle() {
        // Combine the three fields into one title string
        if (!this.workType && !this.workOrderNumber && !this.location) return '';
        
        return `${this.workType || 'Unknown'} / ${this.workOrderNumber || 'No WO#'} / ${this.location || 'No Location'}`;
      },
      laborCost() {
        return (this.form.hourlyRate * this.form.hours).toFixed(2);
      },
      travelCost() {
        return this.form.includeTravel ? (this.form.travelMiles * this.form.mileageRate * 2).toFixed(2) : '0.00';
      },
      totalPrice() {
        const labor = this.form.hourlyRate * this.form.hours;
        const travel = this.form.includeTravel ? this.form.travelMiles * 1 * 2 : 0;
        return (labor + travel).toFixed(2);
      },
      rateValues() {
        const values = [];
        for (let rate = 55; rate <= 200; rate += 5) {
          values.push(rate);
        }
        return values;
      }
  },
  methods: {
    async checkExistingWorkOrder() {
      // Don't check if work order number is empty or too short
      if (!this.workOrderNumber || this.workOrderNumber.length < 3) return;
      
      // Skip this check in edit mode for the current work order
      if (this.mode === 'edit' && this.form.id) {
        const currentWorkOrderNumber = this.extractWorkOrderNumber(this.form.title);
        if (currentWorkOrderNumber === this.workOrderNumber) {
          this.duplicateWorkOrderFound = false;
          return;
        }
      }
      
      this.checkingWorkOrder = true;
      
      try {
        const response = await axios.get(`/api/check-work-order-exists`, {
          params: { workOrderNumber: this.workOrderNumber }
        });
        
        this.duplicateWorkOrderFound = response.data.exists;
        
      } catch (error) {
        console.error('Error checking work order:', error);
        // Don't block submission on API error
        this.duplicateWorkOrderFound = false;
      } finally {
        this.checkingWorkOrder = false;
      }
    },
    
    extractWorkOrderNumber(title) {
      // Extract work order number from title (format: "Type / WO# / Location")
      if (!title) return '';
      
      const parts = title.split('/');
      return parts.length > 1 ? parts[1].trim() : '';
    },
    
    openCreateModal() {
      this.mode = 'create';
      this.form.reset();
      this.workType = '';
      this.workOrderNumber = '';
      this.location = '';
      this.dateSelectionType = 'single';
      this.selectedDates = [{ date: new Date().toISOString().slice(0, 16) }];
      // Set user ID from auth immediately when opening modal
      this.form.user_id = this.$page.props.auth.user.id;
      this.form.file_attachments = []; // Reset to empty array
      this.showModal = true;
      
      // Clear the file input
      setTimeout(() => {
        const fileInput = document.getElementById('file_attachments');
        if (fileInput) fileInput.value = '';
      }, 50);
      this.duplicateWorkOrderFound = false;
      this.checkingWorkOrder = false;
    },
    openEditModal(workOrder) {
      this.mode = 'edit';
      this.form = useForm({
        ...workOrder,
        file_attachments: [],
        progress: null,
        user_id: this.$page.props.auth.user.id,
      });
      
      // Initialize date selection type and dates
      if (workOrder.visit_dates && workOrder.visit_dates.length > 0) {
        this.dateSelectionType = 'multiple';
        this.selectedDates = workOrder.visit_dates.map(date => ({ date }));
      } else if (workOrder.end_date) {
        this.dateSelectionType = 'range';
      } else {
        this.dateSelectionType = 'single';
        this.selectedDates = [{ date: workOrder.date_time || new Date().toISOString().slice(0, 16) }];
      }
      
      this.showModal = true;
      this.duplicateWorkOrderFound = false;
      this.checkingWorkOrder = false;
      
      // Extract work order number, type and location from title
      if (workOrder.title) {
        const parts = workOrder.title.split('/');
        if (parts.length >= 3) {
          this.workType = parts[0].trim();
          this.workOrderNumber = parts[1].trim();
          this.location = parts[2].trim();
        }
      }
    },
    submitForm() {
      // Prevent submission if duplicate work order is found
      if (this.duplicateWorkOrderFound || this.checkingWorkOrder) {
        return;
      }
      
      // Check for duplicates one more time before submission
      this.checkExistingWorkOrder().then(() => {
        if (this.duplicateWorkOrderFound) return;
        
        // Set the combined title before submitting
        this.form.title = this.formattedTitle;
        
        // Explicitly set the user ID again before submission to ensure it's present
        this.form.user_id = this.$page.props.auth.user.id;
        
        // Handle date field based on selection type
        if (this.dateSelectionType === 'single') {
          this.form.end_date = null;
          this.form.visit_dates = [this.form.date_time];
          this.submitSingleWorkOrder();
        } 
        else if (this.dateSelectionType === 'range') {
          this.form.visit_dates = this.generateDateRange(this.form.date_time, this.form.end_date);
          this.submitSingleWorkOrder();
        } 
        else if (this.dateSelectionType === 'multiple') {
          // Filter out any empty dates
          const validDates = this.selectedDates
            .map(d => d.date)
            .filter(date => date && date.trim() !== '')
            .sort();

          if (validDates.length === 0) {
            alert('Please select at least one valid date');
            return;
          }

          // Handle multiple dates as separate work orders
          this.submitMultipleWorkOrders(validDates);
        }
      });
    },
    
    // New method to submit a single work order
    submitSingleWorkOrder() {
      if (this.isSubmitting) return; // Prevent multiple submissions
      this.isSubmitting = true;
      
      // Always use post() directly with the form instance for proper file handling
      if (this.mode === 'create') {
        this.form.post('/work-orders', {
          onSuccess: () => {
            this.showModal = false;
            this.resetForm();
            const userName = this.$page.props.auth.user.name;
            const timestamp = new Date().toLocaleString();
            this.$emit('formSubmitted', `${userName} successfully created work order '${this.formattedTitle}' at ${timestamp}`);
            this.isSubmitting = false;
          },
          onError: (errors) => {
            console.error('Validation errors:', errors);
            this.isSubmitting = false;
            
            // Check if we have a CSRF token error (419)
            if (errors.response && errors.response.status === 419) {
              this.refreshCsrfTokenAndRetry();
            }
          },
          forceFormData: true
        });
      } else {
        this.form.post(`/work-orders/${this.form.id}?_method=PUT`, {
          onSuccess: () => {
            this.showModal = false;
            const userName = this.$page.props.auth.user.name;
            const timestamp = new Date().toLocaleString();
            this.$emit('formSubmitted', `${userName} successfully updated work order '${this.formattedTitle}' at ${timestamp}`);
            this.isSubmitting = false;
          },
          onError: (errors) => {
            console.error('Validation errors:', errors);
            this.isSubmitting = false;
            
            // Check if we have a CSRF token error (419)
            if (errors.response && errors.response.status === 419) {
              this.refreshCsrfTokenAndRetry();
            }
          },
          forceFormData: true
        });
      }
    },
    
    // New method to submit multiple work orders
    async submitMultipleWorkOrders(dates) {
      if (this.isSubmitting) return;
      this.isSubmitting = true;

      try {
        const totalDates = dates.length;
        let successCount = 0;

        for (let i = 0; i < dates.length; i++) {
          const date = dates[i];

          // Prepare the form data for this work order
          const formData = {
            customer_id: this.form.customer_id,
            title: `${this.formattedTitle} (${new Date(date).toLocaleDateString()})`,
            description: this.form.description,
            date_time: date,
            end_date: null, // Ensure end_date is null for single-date work orders
            address: this.form.address,
            hours: this.form.hours,
            price: this.form.price,
            status: this.form.status,
            user_id: this.form.user_id,
          };

          // Send the request to create the work order
          await axios.post('/work-orders', formData);

          successCount++;
        }

        // Close the modal and reset the form
        this.showModal = false;
        this.resetForm();
        const userName = this.$page.props.auth.user.name;
        const timestamp = new Date().toLocaleString();
        this.$emit('formSubmitted', `${userName} successfully created ${successCount} work orders for '${this.formattedTitle}' at ${timestamp}`);
      } catch (error) {
        console.error('Error creating multiple work orders:', error);
        alert('There was an error creating some work orders. Please check the input data and try again.');
      } finally {
        this.isSubmitting = false;
      }
    },
    
    // Add a method to refresh CSRF token
    async refreshCsrfTokenAndRetry() {
      try {
        // Get a fresh CSRF token
        const response = await axios.get('/csrf-token');
        if (response.data && response.data.csrfToken) {
          // Update the token in meta tag
          const tokenElement = document.querySelector('meta[name="csrf-token"]');
          if (tokenElement) {
            tokenElement.setAttribute('content', response.data.csrfToken);
          }
          
          // Retry submission after a short delay
          setTimeout(() => {
            this.isSubmitting = false;
            this.submitForm();
          }, 500);
        }
      } catch (error) {
        console.error('Failed to refresh CSRF token:', error);
        this.isSubmitting = false;
      }
    },
    handleFileUpload(event) {
      const files = event.target.files;
      
      if (files && files.length > 0) {
        this.form.file_attachments = Array.from(files);
      }
    },
    
    removeFile(index) {
      // Create a new FileList without the removed file
      const dtTransfer = new DataTransfer();
      
      this.form.file_attachments.forEach((file, i) => {
        if (i !== index) {
          dtTransfer.items.add(file);
        }
      });
      
      // Update the file input's files
      const fileInput = document.getElementById('file_attachments');
      if (fileInput) {
        fileInput.files = dtTransfer.files;
      }
      
      // Update the form's file attachments
      this.form.file_attachments = Array.from(dtTransfer.files);
    },
    
    resetForm() {
      // Clear the file input separately since form.reset() doesn't clear it
      const fileInput = document.getElementById('file_attachments');
      if (fileInput) fileInput.value = '';
      
      this.form.reset();
      this.form.clearErrors();
      this.workType = '';
      this.workOrderNumber = '';
      this.location = '';
      this.dateSelectionType = 'single';
      this.selectedDates = [{ date: new Date().toISOString().slice(0, 16) }];
      this.form.file_attachments = []; // Reset to empty array
    },
    duplicateWorkOrder() {
      router.post(`/work-orders/${this.form.id}/duplicate`, {}, {
        onSuccess: () => {
          this.showModal = false;
          this.$emit('formSubmitted', 'Work order duplicated successfully.');
        },
        onError: (errors) => {
          console.error('Error duplicating work order. Please try again:', errors);
        },
      });
    },
    deleteWorkOrder() {
      if (confirm('Are you sure you want to delete this work order?')) {
        router.delete(`/work-orders/${this.form.id}`, {
          onSuccess: () => {
            this.showModal = false;
            this.$emit('formSubmitted', 'Work order deleted successfully.');
          },
          onError: (errors) => {
            console.error('Error deleting work order. Please try again:', errors);
          },
        });
      }
    },
    // Functions for date selection
    addNewDate() {
      this.selectedDates.push({ date: new Date().toISOString().slice(0, 16) });
    },
    removeDate(index) {
      if (this.selectedDates.length > 1) {
        this.selectedDates.splice(index, 1);
      }
    },
    // Generate array of dates between start and end for range selection
    generateDateRange(start, end) {
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
    },
  }
};
</script>

<style scoped>
.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
}

.btn-primary {
  background-color: hsl(262, 69%, 44%);
  color: white;
}

.btn-secondary {
  background-color: #e5e7eb;
  color: #374151;
}

.fixed {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.7);
}

.bg-white {
  background-color: white;
}

.p-6 {
  padding: 1.5rem;
}

.rounded-lg {
  border-radius: 0.5rem;
}

.shadow-lg {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
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

.text-sm {
  font-size: 0.875rem;
}

.font-medium {
  font-weight: 500;
}

.text-gray-700 {
  color: #4a5568;
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
  background: linear-gradient(135deg, rgba(17, 24, 39, 0.95), rgba(31, 41, 55, 0.85));
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  flex-direction: column;
  height: auto; 
  max-height: 60vh;
  width: 95%;
  max-width: 650px;
}

.glossy-header {
  background: linear-gradient(180deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.85) 100%);
  position: relative;
  overflow: hidden;
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
  flex-shrink: 0;
  padding: 1rem;
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
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
  flex-shrink: 0;
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
  max-height: calc(75vh - 120px); /* Adjust space for header and footer */
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
  background-color: rgba(31, 41, 55, 0.6);
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
@media (max-width: 768px) {
  .glossy-section .flex {
    flex-direction: column;
  }
  
  .glossy-card {
    height: auto;
    max-height: 70vh;
    width: 95%;
  }
}

input, select, textarea {
  padding: 0.5rem;
  margin: 0.25rem 0;
}

/* Additional styles for validation */
.border-red-500 {
  border-color: #ef4444 !important;
  border-width: 2px !important;
}

.opacity-50 {
  opacity: 0.5;
}

.cursor-not-allowed {
  cursor: not-allowed;
}

/* Update existing styles or add at the end */

.glass-button {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.8);
  font-weight: 500;
}

.glass-button:hover {
  background: rgba(139, 92, 246, 0.3); /* Purple color with transparency */
  border-color: rgba(139, 92, 246, 0.5);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
  color: rgb(255, 255, 255); /* Matching purple text color */
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  .glass-button {
    background: rgba(30, 30, 30, 0.4);
    border-color: rgba(255, 255, 255, 0.1);
  }
  
  .glass-button:hover {
    background: rgba(139, 92, 246, 0.25);
    border-color: rgba(139, 92, 246, 0.4);
  }
}

/* Custom styles for the Cally date picker */
calendar-date {
  /* Primary colors */
  --cally-primary-color: #84cc16;
  --cally-primary-color-light: #a3e635;
  --cally-primary-color-dark: #65a30d;
  
  /* Text colors */
  --cally-text-color: #ffffff;
  --cally-text-color-secondary: #9ca3af;
  
  /* Background colors */
  --cally-background-color: rgba(17, 24, 39, 0.95);
  --cally-surface-color: rgba(31, 41, 55, 0.85);
  
  /* Border colors */
  --cally-border-color: rgba(255, 255, 255, 0.08);
  
  /* Hover and focus states */
  --cally-hover-color: rgba(132, 204, 22, 0.1);
  --cally-focus-ring-color: rgba(132, 204, 22, 0.5);
  
  /* Additional customization */
  --cally-border-radius: 0.5rem;
  --cally-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
  
  /* Add backdrop filter for glossy effect */
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

/* Style the calendar container to match glossy-card */
calendar-date::part(container) {
  background: linear-gradient(135deg, rgba(17, 24, 39, 0.95), rgba(31, 41, 55, 0.85));
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

/* Style selected date to use lime color */
calendar-date::part(selected) {
  background-color: #84cc16 !important;
  color: #ffffff !important;
}

/* Style today's date */
calendar-date::part(today) {
  border-color: #84cc16 !important;
}

/* Style hover state */
calendar-date::part(day):hover {
  background-color: rgba(132, 204, 22, 0.1) !important;
}

/* Style the range slider */
.slider {
  -webkit-appearance: none;
  appearance: none;
  height: 6px;
  background: linear-gradient(90deg, rgba(132, 204, 22, 0.2), rgba(132, 204, 22, 0.3));
  border-radius: 3px;
  outline: none;
}

.slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 18px;
  height: 18px;
  background: #84cc16;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 0 0 4px rgba(132, 204, 22, 0.1);
}

.slider::-webkit-slider-thumb:hover {
  background: #65a30d;
  transform: scale(1.1);
  box-shadow: 0 0 0 6px rgba(132, 204, 22, 0.2);
}

.slider::-moz-range-thumb {
  width: 18px;
  height: 18px;
  background: #84cc16;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 0 0 4px rgba(132, 204, 22, 0.1);
  border: none;
}

.slider::-moz-range-thumb:hover {
  background: #65a30d;
  transform: scale(1.1);
  box-shadow: 0 0 0 6px rgba(132, 204, 22, 0.2);
}

.slider::-moz-range-track {
  background: linear-gradient(90deg, rgba(132, 204, 22, 0.2), rgba(132, 204, 22, 0.3));
  border-radius: 3px;
  height: 6px;
}

/* Purple slider styles */
.slider-purple {
  -webkit-appearance: none;
  appearance: none;
  height: 6px;
  background: linear-gradient(90deg, rgba(147, 51, 234, 0.2), rgba(147, 51, 234, 0.3));
  border-radius: 3px;
  outline: none;
}

.slider-purple::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 18px;
  height: 18px;
  background: #9333ea;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 0 0 4px rgba(147, 51, 234, 0.1);
}

.slider-purple::-webkit-slider-thumb:hover {
  background: #7e22ce;
  transform: scale(1.1);
  box-shadow: 0 0 0 6px rgba(147, 51, 234, 0.2);
}

.slider-purple::-moz-range-thumb {
  width: 18px;
  height: 18px;
  background: #9333ea;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 0 0 4px rgba(147, 51, 234, 0.1);
  border: none;
}

.slider-purple::-moz-range-thumb:hover {
  background: #7e22ce;
  transform: scale(1.1);
  box-shadow: 0 0 0 6px rgba(147, 51, 234, 0.2);
}

.slider-purple::-moz-range-track {
  background: linear-gradient(90deg, rgba(147, 51, 234, 0.2), rgba(147, 51, 234, 0.3));
  border-radius: 3px;
  height: 6px;
}

/* Add glossy effect to quick selection tiles */
.glossy-content.p-4 {
  transition: all 0.3s ease;
  border: 1px solid rgba(132, 204, 22, 0.1);
}

.glossy-content.p-4:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  border-color: rgba(132, 204, 22, 0.3);
}

.glossy-content.p-4.bg-lime-600 {
  background: linear-gradient(135deg, #84cc16, #65a30d);
  border-color: rgba(163, 230, 53, 0.3);
}

.status-badge {
  backdrop-filter: blur(10px);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
  border: 1px solid rgba(255, 255, 255, 0.18);
  transform: translateY(0);
  transition: all 0.2s ease;
}

.status-badge:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 40px 0 rgba(31, 38, 135, 0.5);
}

.status-badge:active {
  transform: translateY(0);
}
</style>