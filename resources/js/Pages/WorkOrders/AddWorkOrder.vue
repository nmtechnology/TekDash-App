<template>
  <div>
    <button @click="handleShowModal"
      class="btn flex items-center gap-2 px-4 py-2 font-bold text-sm bg-lime-500 hover:bg-lime-600 text-black transition-all duration-300 shadow-md hover:shadow-lg">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Work Order
    </button>

    <!-- Modal -->
    <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100"
      leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-show="showModal"
        class="fixed inset-0 z-[1000] flex items-center justify-center">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-70 transition-opacity" @click="handleHideModal"></div>

        <!-- Modal container -->
        <div class="flex items-center justify-center w-full h-full p-2 md:p-4">
          <div
            class="glossy-card rounded-lg shadow-xl w-full max-w-lg md:max-w-2xl flex flex-col h-[90vh] mx-auto responsive-modal"
            @click.stop>
            <!-- Fixed Header with Steps -->
            <div class="modal-header glossy-header px-4 py-3 border-b border-gray-700">
              <div class="flex flex-col gap-2 w-full">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between w-full gap-2">
                  <h3 class="text-lime-400 text-xl md:text-2xl font-bold" id="modal-title">
                    Create New Work Order
                  </h3>
                  <ul class="steps steps-horizontal w-full md:w-auto overflow-x-auto no-scrollbar md:ml-6">
                    <li v-for="step in totalSteps" :key="step" class="step transition-all duration-300 relative" :class="{
                      'step-lime-400 text-lime-400 font-bold': step === currentStep,
                      'step-success text-green-400': step < currentStep,
                      'text-gray-500': step > currentStep,
                      'after:!bg-green-400': step < currentStep && step < totalSteps,
                      'before:!bg-green-400': step <= currentStep && step > 1
                    }">
                      <!-- Step Icons (Checkmark for completed, Circle for current, Small circle for future) -->
                      <span v-if="step < currentStep" class="flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-400" viewBox="0 0 20 20"
                          fill="currentColor">
                          <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                        </svg>
                      </span>
                      <span v-else-if="step === currentStep" class="flex items-center justify-center animate-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-lime-400" viewBox="0 0 20 20"
                          fill="currentColor">
                          <circle cx="10" cy="10" r="6" />
                        </svg>
                      </span>
                      <span v-else class="flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20"
                          fill="currentColor">
                          <circle cx="10" cy="10" r="4" />
                        </svg>
                      </span>

                      <!-- Step Title - shown for all steps -->
                      <div class="text-xs">
                        {{
                          step === 1 ? 'Customer & Tech' :
                            step === 2 ? 'Work Order Title' :
                              step === 3 ? 'Service Description' :
                                step === 4 ? 'Date & Time' :
                                  step === 5 ? 'Location Address' :
                                    step === 6 ? 'Approved Hours' :
                                      step === 7 ? 'Rate & Expenses' :
                                        step === 8 ? 'Work Order Status' :
                                          step === 9 ? 'Attachments' :
                                            step === 10 ? 'Summary & Review' : ''
                        }}
                      </div>
                    </li>
                  </ul>
                  <button @click="handleHideModal"
                    class="btn ml-2 text-red-400 hover:text-red-400 transition-colors duration-200 focus:outline-none"
                    aria-label="Close modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Scrollable middle content -->
            <div class="modal-body flex-1 overflow-y-auto px-4 py-2">
              <form @submit.prevent="submitForm" class="flex flex-col flex-grow">
                <div class="bg-gray-900 bg-opacity-90 rounded-lg">
                  <!-- Step 1: Customer & Technician Selection -->
                  <div v-show="currentStep === 1">
                    <div class="glossy-section mb-2">
                      <label for="customer_id" class="text-green-400 block text-3xl font-extrabold">Customer</label>
                      <p class="text-md text-white font-semibold">Select a customer for this work order.</p>
                      <select v-model="form.customer_id" id="customer_id" name="customer_id"
                        class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-lg"
                        required @change="logSelectedCustomer">
                        <option value="" disabled>Select a customer ({{ safeCustomersArray.length }} active customers
                          available)</option>
                        <option v-for="customer in safeCustomersArray" :key="customer.id" :value="customer.id">
                          {{ customer.business_name || customer.name || `Customer #${customer.id}` }}
                        </option>
                      </select>
                      <div class="text-xs text-gray-400 mt-1">Note: Only active customers are displayed. Contact an
                        administrator if a customer is missing.</div>
                    </div>
                    <!-- Technician Selection -->
                    <div class="glossy-section mb-4">
                      <label for="technician_id" class="text-green-400 block text-3xl font-extrabold">Assign
                        Technician</label>
                      <p class="text-md text-white font-semibold">Select a technician for this work order.</p>
                      <div v-if="isLoadingTechnicians" class="text-center py-3">
                        <div
                          class="animate-spin inline-block w-6 h-6 border-2 border-lime-400 border-t-transparent rounded-full">
                        </div>
                        <span class="ml-2 text-lime-400">Loading technicians...</span>
                      </div>
                      <div v-else-if="safeTechniciansArray.length === 0"
                        class="mt-2 p-3 bg-red-900/30 border border-red-500/50 rounded-md">
                        <div class="flex items-center">
                          <span class="text-red-400">No technicians found. Please check back later.</span>
                        </div>
                      </div>
                      <select v-else v-model="form.technician_id" id="technician_id" name="technician_id"
                        class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-lg"
                        required>
                        <option value="" disabled>Select a technician ({{ safeTechniciansArray.length }} available)
                        </option>
                        <option v-for="technician in safeTechniciansArray" :key="technician.id" :value="technician.id">
                          {{ technician.first_name && technician.last_name ? (technician.first_name + ' ' +
                            technician.last_name + (technician.employee_id ? ' (' + technician.employee_id + ')' : '')) :
                            (technician.name || 'Technician #' + technician.id) }}
                        </option>
                      </select>
                    </div>
                    <!-- Selection Summary for Step 1 -->
                    <div class="mt-4 p-4 rounded-lg bg-gray-800/50 border border-gray-700">
                      <div class="text-center">
                        <div class="text-sm text-gray-400">Selected Customer:</div>
                        <div class="text-lime-400 font-bold text-4xl">
                          {{safeCustomersArray.find(c => c.id == form.customer_id)?.business_name ||
                            safeCustomersArray.find(c => c.id == form.customer_id)?.name || 'None selected'}}
                        </div>
                        <div class="text-sm text-gray-400 mt-2">Assigned Technician:</div>
                        <div class="text-lime-400 font-bold text-2xl">
                          {{safeTechniciansArray.find(t => t.id == form.technician_id)?.first_name &&
                            safeTechniciansArray.find(t => t.id == form.technician_id)?.last_name ?
                            (safeTechniciansArray.find(t => t.id == form.technician_id)?.first_name + ' ' +
                              safeTechniciansArray.find(t => t.id == form.technician_id)?.last_name +
                              (safeTechniciansArray.find(t => t.id == form.technician_id)?.employee_id ? ' (' +
                                safeTechniciansArray.find(t => t.id == form.technician_id)?.employee_id + ')' : '')) :
                            (safeTechniciansArray.find(t => t.id == form.technician_id)?.name || 'None selected')}}
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Step 2: Work Order Title -->
                  <div v-show="currentStep === 2">
                    <div class="glossy-section">
                      <h3 class="text-lime-400 text-2xl font-medium mb-2">Create A Title</h3>
                      <p class="text-2xl text-white">This section will generate a title for TekDash to enable it to be
                        searched in our system.
                        Work order #'s cannot be used twice and the work order should have been duplicated if this is a
                        return
                        trip instead of creating a new work order. Location name is not the address, itpis the name of the
                        business the technician will be at, so for example if it is for Wal-Mart, then the tech knows to
                        look
                        for a WalMart when driving. </p>
                      <div class="flex flex-col md:flex-row md:gap-4">
                        <div class="mb-4 p-2 w-full">
                          <label for="workOrderNumber" class="block text-sm font-medium text-green-400">Work Order
                            Number</label>
                          <div class="relative">
                            <input v-model="workOrderNumber" @input="debouncedCheckExistingWorkOrder" id="workOrderNumber"
                              name="workOrderNumber" type="text"
                              class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-lg pr-12"
                              required autocomplete="off" placeholder="Enter unique work order number" />
                            <span v-if="checkingWorkOrder"
                              class="absolute right-2 top-2 text-xs text-gray-400">Checking...</span>
                            <span v-else-if="duplicateWorkOrderFound"
                              class="absolute right-2 top-2 text-xs text-red-400">Duplicate!</span>
                            <!-- Only show 'Available' if checkmark is not shown -->
                            <span v-else-if="workOrderVerified && workOrderNumber && !duplicateWorkOrderFound"
                              class="absolute right-8 top-2 text-xs text-green-400">Available</span>
                            <!-- Green checkmark if validated and not duplicate, not checking, and not duplicate -->
                            <span
                              v-if="workOrderVerified && !duplicateWorkOrderFound && !checkingWorkOrder && workOrderNumber"
                              class="absolute right-2 top-1/2 -translate-y-1/2 transform">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-green-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                  d="M5 13l4 4L19 7" />
                              </svg>
                            </span>
                          </div>
                          <div v-if="duplicateWorkOrderFound" class="text-xs text-red-400 mt-1">This work order number is
                            already in use. Please enter a unique number.</div>
                        </div>
                        <div class="p-2 w-full">
                          <label for="workType" class="block text-sm font-medium text-green-400">Work Type</label>
                          <select v-model="workType" id="workType" name="workType"
                            class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-lg"
                            required>
                            <option value="" disabled>Select work type</option>
                            <option value="CCTV">CCTV</option>
                            <option value="ALARM">ALARM</option>
                            <option value="CABLING">CABLING</option>
                            <option value="POS">POS</option>
                            <option value="INTERCOM">INTERCOM</option>
                            <option value="FIRE">FIRE</option>
                            <option value="ACCESS-CONTROL">ACCESS-CONTROL</option>
                            <option value="TS">TS (Troubleshooting)</option>
                            <option value="PM">PM (Preventive Maintenance)</option>
                            <option value="INSTALL">INSTALL</option>
                            <option value="ESTIMATE">ESTIMATE</option>
                            <option value="WALK-THRU">WALK-THRU</option>
                            <option value="MEETING">MEETING</option>
                            <option value="APPT">APPT (Appointment)</option>
                            <option value="SERVICE">SERVICE</option>
                            <option value="REPAIR">REPAIR</option>
                            <option value="UPGRADE">UPGRADE</option>
                            <option value="PERSONAL-LEAVE">PERSONAL-LEAVE</option>
                            <option value="TRAINING">TRAINING</option>
                          </select>
                        </div>
                      </div>
                      <div class="p-2 w-full mr-1">
                        <label for="location" class="block text-sm font-medium text-green-400">Location/Business
                          name</label>
                        <select v-model="location" id="location" name="location"
                          class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-lg"
                          required>
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

                    </div>
                    <!-- Selection Summary -->
                    <div class="mt-4 p-4 rounded-lg bg-gray-800/50 border border-gray-700">
                      <div class="text-center">
                        <div class="text-sm text-gray-400">Generated Title:</div>
                        <div class="text-lime-400 font-bold text-4xl">{{ formattedTitle || 'No title generated' }}</div>
                      </div>
                    </div>
                  </div>

                  <!-- Step 3: Description -->
                  <div v-show="currentStep === 3" class="glossy-section mb-2">
                    <label for="description" class="block text-2xl font-medium text-green-400">Enter A Service
                      Description</label>
                    <p class="text-lg text-white">Enter the details of what was requested on the work order sent by the
                      customer, here's a hint, you can copy and paste the description from the work orders into here which
                      saves you time.</p>
                    <textarea v-model="form.description" id="description" name="description"
                      placeholder="What is the Field Technician doing onsite?"
                      class="glossy-content text-lime-400 mt-1 p-4 rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-lg w-full min-h-[250px] font-medium"
                      required></textarea>
                    <!-- Selection Summary -->
                    <div class="mt-4 p-4 rounded-lg bg-gray-800/50 border border-gray-700">
                      <div class="text-center">
                        <div class="text-lg text-gray-400">Entered Description:</div>
                        <div class="text-lime-400 text-lg font-bold whitespace-pre-line">{{ descriptionSummary }}</div>
                      </div>
                    </div>
                  </div>

                  <!-- Step 4: Date/Time -->
                  <div v-show="currentStep === 4" class="glossy-section mb-2">
                    <label class="block text-sm font-medium text-green-400">Date and Time Selection</label>
                    <p class="text-sm text-white mb-3">Select the date and time for this work order using our enhanced
                      date
                      picker.</p>
                    <div class="bg-lime-600/20 border border-lime-600/30 rounded-md p-3 mb-4">
                      <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-lime-500 mr-2" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m-1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm text-lime-500">We've upgraded our date picker for a better experience!</span>
                      </div>
                    </div>
                    <div class="mt-2 flex flex-col items-center gap-2">
                      <div class="flex gap-2 mb-2">
                        <button type="button" class="glass-button px-3 py-1"
                          @click="quickSelectDate('today')">Today</button>
                        <button type="button" class="glass-button px-3 py-1"
                          @click="quickSelectDate('tomorrow')">Tomorrow</button>
                        <button type="button" class="glass-button px-3 py-1" @click="quickSelectDate('nextWeek')">Next
                          Week</button>
                      </div>
                      <div class="w-full max-w-md relative">
                        <!-- ShadCN DatePicker -->
                        <DatePicker 
                          v-model="form.date_time" 
                          :min-date="new Date()" 
                          with-time
                          use12Hours
                          class="w-full"
                        />
                      </div>
                    </div>
                    <!-- Selected Date/Time Preview -->
                    <div class="mt-8 p-6 rounded-lg bg-gray-800/50 border-2 border-lime-400">
                      <div class="text-center">
                        <div class="text-lg text-gray-400">Selected Date & Time:</div>
                        <div class="text-3xl text-lime-400 font-extrabold mt-2">{{ formattedDateTime }}</div>
                      </div>
                    </div>
                  </div>

                  <!-- Step 5: Address -->
                  <div v-show="currentStep === 5" class="pb-2">
                    <div class="glossy-section mb-4">
                      <label for="address" class="block text-2xl font-extrabold text-green-400 mb-2">Enter The
                        Location</label>
                      <p class="text-xl text-white font-semibold mb-2">Enter the address for the work order location.</p>
                      <div class="relative">
                        <input v-model="form.address" id="address" name="address" type="text"
                          class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white text-2xl sm:text-2xl font-bold p-4"
                          @blur="geocodeAddress(form.address)"
                          placeholder="Enter complete address (street, city, state, zip)" required />

                        <!-- Search icon or processing indicator -->
                        <div class="absolute top-1/2 right-4 transform -translate-y-1/2">
                          <div v-if="mapboxLoading"
                            class="animate-spin w-6 h-6 border-2 border-lime-400 border-t-transparent rounded-full"></div>
                          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                          </svg>
                        </div>
                      </div>

                      <!-- Mapbox Static Map Preview -->
                      <div v-if="form.address" class="mt-4">
                        <!-- Show loading indicator while geocoding -->
                        <div v-if="mapboxLoading"
                          class="flex items-center justify-center p-4 bg-gray-800 rounded-lg border border-gray-700 min-h-[200px]">
                          <div
                            class="animate-spin inline-block w-6 h-6 border-2 border-lime-400 border-t-transparent rounded-full mr-2">
                          </div>
                          <span class="text-lime-400">Loading map preview...</span>
                        </div>

                        <!-- Show error if geocoding failed -->
                        <div v-else-if="mapboxError" class="p-4 bg-red-900/30 border border-red-500 rounded-lg">
                          <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400 mr-2" fill="none"
                              viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01M6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="text-red-400">{{ mapboxError }}</span>
                          </div>
                        </div>

                        <!-- Show map image if available -->
                        <a v-else-if="mapboxImageUrl" :href="mapboxMapsLink" target="_blank" rel="noopener"
                          title="Open in Map App">
                          <img :src="mapboxImageUrl" alt="Map snapshot"
                            class="rounded-lg shadow-md border border-gray-700 hover:opacity-90 transition-opacity cursor-pointer"
                            style="width: 100%; max-height: 300px; min-height: 180px; object-fit: cover; background: #222;" />
                          <div class="text-xs text-gray-400 mt-1">Click map to open location in browser</div>
                        </a>
                      </div>
                      <!-- Address Summary for Review -->
                      <div v-if="form.address" class="mt-6 p-6 rounded-lg bg-gray-900/80 border-2 border-lime-400">
                        <div class="text-2xl font-extrabold text-lime-400 mb-2">Address Review</div>
                        <div class="text-2xl text-white font-bold tracking-wide">{{ form.address }}</div>
                        <div class="text-lg text-gray-300 mt-2">Please review the address above and ensure it is correct
                          before proceeding.</div>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>

            <!-- Fixed Footer with button -->
            <div class="modal-footer glossy-footer px-4 py-3 border-t border-gray-700">
              <div class="flex justify-end space-x-3">
                <button
                  type="button"
                  @click="handleHideModal"
                  class="glass-button px-4 py-2 bg-gray-700/50 text-white hover:bg-gray-700"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="glass-button px-4 py-2 bg-purple-600/60 text-white hover:bg-purple-700"
                  @click="submitForm"
                >
                  Add Work Order
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
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
import DatePicker from '@/Components/ui/calendar/DatePicker.vue';

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
    if (e.response && e.response.status === 422) {
      duplicateWorkOrderFound.value = false;
      workOrderVerified.value = false;
    } else {
      console.error('Error checking work order number:', e);
      duplicateWorkOrderFound.value = false;
      workOrderVerified.value = false;
    }
  } finally {
    checkingWorkOrder.value = false;
  }
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
    case 6:
      return form.hours >= 2 && form.hours <= 16;
    case 7:
      return !!form.hourlyRate && form.hourlyRate >= 55;
    case 8:
      return !!form.status;
    case 9:
      return true; // Files are optional
    case 10:
      return !!form.grand_total && parseFloat(form.grand_total) > 0; // Ensure grand_total is set and valid
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
          const formElement = document.querySelector('.overflow-y-auto');
          if (formElement) {
            formElement.scrollTop = 0;
          }
        });
      }
    } finally {
      isLoading.value = false;
    }
  }
};

const prevStep = async () => {
  isLoading.value = true;
  try {
    await new Promise(resolve => setTimeout(resolve, 100));
    if (currentStep.value > 1) {
      currentStep.value--;
      nextTick(() => {
        const formElement = document.querySelector('.overflow-y-auto');
        if (formElement) {
          formElement.scrollTop = 0;
        }
      });
    }
  } finally {
    isLoading.value = false;
  }
};

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
      // ... other fields for debugging
    });

    // Use Inertia's post method to submit the form
    form.post('/work-orders', {
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
        console.error('Form submission errors:', errors);
        isSubmitting.value = false;
        // Handle validation errors
        if (errors) {
          const errorMessages = Object.values(errors).flat().join('\n');
          alert(`Please fix the following errors:\n${errorMessages}`);
        }
      },
      onFinish: () => {
        isSubmitting.value = false;
      }
    });
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

// Remove a file from attachments
const removeFile = (index) => {
  if (form.file_attachments && index >= 0 && index < form.file_attachments.length) {
    form.file_attachments.splice(index, 1);
    console.log('File removed at index:', index, 'Remaining attachments:', form.file_attachments.length);
  }
};

// Get object URL for file preview
const getFileObjectURL = (file) => {
  if (file && file instanceof File) {
    return URL.createObjectURL(file);
  }
  return '';
};
</script>

<style scoped>
.calendar-day-button {
  font-size: 1rem;
  min-height: 40px;
}

.calendar-day-button:hover {
  background-color: rgba(55, 65, 81, 0.5);
  /* bg-gray-700/50 */
}

.calendar-day-selected {
  border: 2px solid #a3e635;
  /* border-lime-400 */
  background-color: rgba(63, 98, 18, 0.4);
  /* bg-lime-900/40 */
  color: #bef264;
  /* text-lime-300 */
}

.calendar-day-today {
  border: 1px solid #a3e635;
  /* border-lime-400 */
  background-color: rgba(63, 98, 18, 0.3);
  /* bg-lime-800/30 */
  color: #fff;
}

.date-picker-container {
  background-color: #1f2937;
  /* bg-gray-800 */
  border-radius: 0.75rem;
  /* rounded-xl */
  padding: 1.5rem;
  /* p-6 */
  border: 2px solid rgba(0, 232, 77, 0.962);
  /* border-lime-400/50 */
}

/* Existing styles... */

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
  height: 95vh;
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

.text {
  font-size: 0.875rem;
}

.font-medium {
  font-weight: 500;
}

.text-gray-700 {
  color: #000000;
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
    font-size: 2rem;
  }
}

.modal-header {
  background: linear-gradient(to right, rgba(17, 24, 39, 0.9), rgba(31, 41, 55, 0.85));
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  top: 0;
  left: 0;
  right: 0;
  width: 100%;
  position: fixed;
  z-index: 1001;
  min-height: 120px;
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
  position: fixed;
  z-index: 1001;
  height: 80px;
  display: flex;
  align-items: center;
}

/* Ensure modal content does not go under header/footer */
.pt-[70px] {
  padding-top: 70px !important;
}
.pb-[80px] {
  padding-bottom: 80px !important;
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
</style>