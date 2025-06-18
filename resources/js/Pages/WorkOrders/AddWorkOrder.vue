<template>
  <div>
    <button @click="handleShowModal"
      class="btn flex items-center gap-2 px-2 py-2 font-bold text-sm text-blue-400 transition-all duration-300">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Work Order
    </button>

    <div v-show="showModal" class="fixed inset-0 mt-4 z-50 flex items-center justify-center bg-black bg-opacity-70"
      @click="handleHideModal">
      <div
        class="glossy-card flex-auto rounded-lg overflow-hidden shadow-xl transform transition-all md:max-w-3xl lg:max-w-5xl w-full h-[95vh] flex flex-col"
        @click.stop>
        <div class="glossy-header px-4 pt-3 pb-2">
          <div class="flex justify-between items-center min-h-0">
            <h3 class="text-lime-400 text-xl leading-5 font-medium" id="modal-title">
              Add Work Order
            </h3>
            <button @click="handleHideModal"
              class="btn btn-circle btn-outline ml-2 text-gray-900 hover:text-lime-400 transition-colors duration-200 focus:outline-none"
              aria-label="Close modal">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Steps component -->
        <ul class="steps px-6 mb-2">
          <li v-for="step in totalSteps" :key="step" class="step"
            :class="{'step-info': step <= currentStep, 'step-error': step > currentStep}"
            :data-content="step === currentStep ? '✓' : ''">
            Step {{ step }}
          </li>
        </ul>

        <form @submit.prevent="submitForm" class="flex flex-col flex-grow">
          <div class="overflow-y-auto px-6 py-5 bg-gray-900 bg-opacity-90 flex-grow">
           
           
            <!-- Step 1: Customer & Technician Selection -->
            <div v-show="currentStep === 1">
              <div class="glossy-section mb-4">
                <label for="customer_id" class="text-green-400 block text-3xl font-extrabold">Select Customer</label>
                <p class="text-md text-white font-semibold">Choose a customer from the dropdown menu below.</p>
                <div v-if="isLoadingCustomers" class="text-center py-3">
                  <div
                    class="animate-spin inline-block w-6 h-6 border-2 border-lime-400 border-t-transparent rounded-full">
                  </div>
                  <span class="ml-2 text-lime-400">Loading customers...</span>
                </div>
                <div v-else-if="customersArray.length === 0 || loadError"
                  class="mt-2 p-3 bg-red-900/30 border border-red-500/50 rounded-md">
                  <div class="flex items-center">
                    <!-- <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400 mr-2" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg> -->
                    <!-- <span class="text-red-400">{{ loadError ? 'Error loading customers. Please try again.' : 'No customers found. Please add customers first.' }}</span> -->
                  </div>
                  <button @click="loadCustomers"
                    class="mt-2 px-3 py-1 bg-red-500/30 hover:bg-red-500/50 text-white rounded-md text-sm flex items-center"
                    type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Retry Loading Customers
                  </button>
                </div>
                <select v-else v-model="form.customer_id" id="customer_id" name="customer_id"
                  class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-lg"
                  required @change="logSelectedCustomer">
                  <option value="" disabled>Select a customer ({{ customersArray.length }} available)</option>
                  <option v-for="customer in customersArray" :key="customer.id" :value="customer.id">
                    {{ customer.business_name || customer.name || `Customer #${customer.id}` }}
                  </option>
                </select>

              </div>

              <!-- Technician Selection (moved from step 10) -->
              <div class="glossy-section mb-4">
                <label for="technician_id" class="text-green-400 block text-3xl font-extrabold">Assign Technician</label>
                <p class="text-md text-white font-semibold">Select a technician for this work order.</p>
                <div v-if="isLoadingTechnicians" class="text-center py-3">
                  <div
                    class="animate-spin inline-block w-6 h-6 border-2 border-lime-400 border-t-transparent rounded-full">
                  </div>
                  <span class="ml-2 text-lime-400">Loading technicians...</span>
                </div>
                <div v-else-if="technicians.length === 0"
                  class="mt-2 p-3 bg-red-900/30 border border-red-500/50 rounded-md">
                  <div class="flex items-center">
                    <!-- <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400 mr-2" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg> -->
                    <!-- <span class="text-red-400">No technicians found. Please check back later.</span> -->
                  </div>
                </div>
                <select v-else v-model="form.technician_id" id="technician_id" name="technician_id"
                  class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-lg"
                  required>
                  <option value="" disabled>Select a technician ({{ technicians.length }} available)</option>
                  <option v-for="technician in technicians" :key="technician.id" :value="technician.id">
                    {{ technician.name }}
                  </option>
                </select>
              </div>

              <!-- Selection Summary for Step 1 -->
              <div class="mt-4 p-4 rounded-lg bg-gray-800/50 border border-gray-700">
                <div class="text-center">
                  <div class="text-sm text-gray-400">Selected Customer:</div>
                  <div class="text-lime-400 font-bold text-4xl">
                    {{ customersArray.find(c => c.id == form.customer_id)?.business_name || customersArray.find(c => c.id == form.customer_id)?.name || 'None selected' }}
                  </div>
                  <div class="text-sm text-gray-400 mt-2">Assigned Technician:</div>
                  <div class="text-lime-400 font-bold text-4xl">
                    {{ technicians.find(t => t.id == form.technician_id)?.name || 'None selected' }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 2: Work Order Title -->
            <div v-show="currentStep === 2">
              <div class="glossy-section mb-4">
                <h3 class="text-lime-400 text-2xl font-medium mb-2">Create A Title</h3>
                <!-- Work order title fields -->
                <p class="text-lg text-white">This section will generate a title for TekDash to find it in our system.
                  Work order #'s cannot be used twice and the work order should have been duplicated if this is a return
                  trip instead of creating a new work order. Location name is not an address, it is the name of the
                  business the technician will be at, so for example if it is for Wal-Mart, then the tech knows to look
                  for a WalMart when driving. </p>

                <div class="flex flex-col md:flex-row md:gap-4">

                  <div class="mb-4 p-2 w-full">
                    <label for="workOrderNumber" class="block text-sm font-medium text-green-400">Work Order
                      Number</label>
                    <div class="relative">
                      <input placeholder="WO123456-01" type="text" v-model="workOrderNumber"
                        @blur="checkExistingWorkOrder" id="workOrderNumber" name="workOrderNumber"
                        class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-lg"
                        :class="{'border-red-500': duplicateWorkOrderFound}"
                        required>
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
                <div>
                  <label class="block text-sm font-medium text-green-400">Generated Title</label>
                  <div class="text-lime-400 mt-1 px-3 py-2 rounded-md glossy-content">
                    {{ formattedTitle }}
                  </div>
                </div>
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
            <div v-show="currentStep === 3" class="glossy-section mb-4">
              <label for="description" class="block text-2xl font-medium text-green-400">Enter A Service Description</label>
              <p class="text-lg text-white">Enter the details of what was requested on the work order sent by the
                customer, here's a hint, you can copy and paste the description from the work orders into here which
                saves you time.</p>
              <textarea v-model="form.description" id="description" name="description"
                placeholder="What is the Field Technician doing onsite?"
                class="glossy-content text-lime-400 inline-block mt-1 p-2 mr-3 rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white sm:text-lg"
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
            <div v-show="currentStep === 4" class="glossy-section mb-4">
              <label class="block text-sm font-medium text-green-400">Date and Time Selection</label>
              <p class="text-sm text-white mb-3">Select the date and time for this work order using our enhanced date
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

              <!-- Enhanced Date Picker (shadcn style, large and modern) -->
              <div class="mt-2 flex flex-col items-center">
                <div class="w-full max-w-md">
                  <Popover class="relative w-full">
                    <PopoverButton
                      class="glossy-content flex justify-between items-center w-full p-4 rounded-lg border-2 border-lime-400 focus:outline-none focus:ring-2 focus:ring-lime-500 text-lime-400 text-lg font-bold transition-all duration-200">
                      <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ selectedDateFormatted || 'Select a date' }}</span>
                      </div>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </PopoverButton>
                    <PopoverPanel
                      class="absolute z-10 w-full bg-gray-800 border-2 border-lime-400 mt-2 rounded-lg shadow-2xl p-4">
                      <!-- Calendar UI -->
                      <div class="p-2">
                        <!-- Month Navigation -->
                        <div class="flex justify-between items-center mb-6">
                          <button @click="prevMonth"
                            class="p-2 rounded-full hover:bg-gray-700 text-lime-400 hover:text-white text-lg" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                              stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                          </button>
                          <div class="text-lime-400 font-extrabold text-xl">{{ currentMonthName }}</div>
                          <button @click="nextMonth"
                            class="p-2 rounded-full hover:bg-gray-700 text-lime-400 hover:text-white text-lg" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                              stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                          </button>
                        </div>
                        <!-- Days of Week Header -->
                        <div class="grid grid-cols-7 mb-2">
                          <div v-for="day in ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']" :key="day"
                            class="text-center text-base text-gray-400 py-2 font-bold">
                            {{ day }}
                          </div>
                        </div>
                        <!-- Calendar Grid -->
                        <div class="grid grid-cols-7 gap-2">
                          <div v-for="(day, index) in calendarDays" :key="index" class="aspect-square relative">
                            <button v-if="day" @click="selectDate(day)" type="button" :class="[
                                'w-full h-full flex items-center justify-center rounded-lg text-lg font-bold transition-colors duration-200 calendar-day-button',
                                isSameDate(day, selectedDate) 
                                  ? 'calendar-day-selected border-2 border-lime-400 bg-lime-900/40 text-lime-300' 
                                  : checkIsToday(day) 
                                    ? 'calendar-day-today border border-lime-400 bg-lime-800/30 text-white' 
                                    : 'hover:bg-gray-700 text-white border border-gray-700'
                              ]">
                              {{ day.getDate() }}
                            </button>
                          </div>
                        </div>
                        <!-- Quick Select Buttons -->
                        <div class="mt-6 grid grid-cols-3 gap-3">
                          <button @click="quickSelectDate('today')" type="button"
                            class="glossy-content p-3 rounded-md text-base text-lime-400 hover:bg-lime-600 hover:text-white transition-colors duration-200 font-bold">
                            Today
                          </button>
                          <button @click="quickSelectDate('tomorrow')" type="button"
                            class="glossy-content p-3 rounded-md text-base text-lime-400 hover:bg-lime-600 hover:text-white transition-colors duration-200 font-bold">
                            Tomorrow
                          </button>
                          <button @click="quickSelectDate('nextWeek')" type="button"
                            class="glossy-content p-3 rounded-md text-base text-lime-400 hover:bg-lime-600 hover:text-white transition-colors duration-200 font-bold">
                            Next Week
                          </button>
                        </div>
                      </div>
                    </PopoverPanel>
                  </Popover>
                </div>
                <!-- Time Selector -->
                <div class="mt-8 w-full max-w-md">
                  <label class="block text-lg font-medium text-green-400 mb-2">Select Time</label>
                  <div class="flex items-center gap-4 justify-center">
                    <select 
                      v-model="selectedTime.hour"
                      class="bg-gray-800 text-lime-400 p-3 rounded-lg border-2 border-lime-400 focus:outline-none focus:ring-2 focus:ring-lime-500 text-lg font-bold"
                    >
                      <option v-for="hour in 12" :key="hour" :value="hour">{{ hour < 10 ? '0' + hour : hour }}</option>
                    </select>
                    <span class="text-white font-bold text-2xl">:</span>
                    <select 
                      v-model="selectedTime.minute"
                      class="bg-gray-800 text-lime-400 p-3 rounded-lg border-2 border-lime-400 focus:outline-none focus:ring-2 focus:ring-lime-500 text-lg font-bold"
                    >
                      <option v-for="minute in ['00', '15', '30', '45']" :key="minute" :value="minute">{{ minute }}</option>
                    </select>
                    <select 
                      v-model="selectedTime.period"
                      class="bg-gray-800 text-lime-400 p-3 rounded-lg border-2 border-lime-400 focus:outline-none focus:ring-2 focus:ring-lime-500 text-lg font-bold"
                    >
                      <option value="AM">AM</option>
                      <option value="PM">PM</option>
                    </select>
                  </div>
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
            <div v-show="currentStep === 5">
              <div class="glossy-section mb-4">
                <label for="address" class="block text-2xl font-extrabold text-green-400 mb-2">Enter The Location</label>
                <p class="text-xl text-white font-semibold mb-2">Enter the address for the work order location.</p>
                <input v-model="form.address" id="address" name="address" type="text"
                  class="glossy-content text-lime-400 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-white focus:ring-white text-2xl sm:text-2xl font-bold p-4"
                  required />
                <!-- Mapbox Static Map Preview -->
                <div v-if="form.address && mapboxImageUrl" class="mt-4">
                  <a :href="mapboxMapsLink" target="_blank" rel="noopener" title="Open in Map App">
                    <img :src="mapboxImageUrl" alt="Map snapshot"
                      class="rounded-lg shadow-md border border-gray-700 hover:opacity-90 transition-opacity cursor-pointer"
                      style="width: 100%; max-width: 600px; min-height: 120px; background: #222;" />
                  </a>
                  <div v-if="mapboxLoading" class="text-lg text-gray-400 mt-1">Loading map...</div>
                  <div v-if="mapboxError" class="text-lg text-red-400 mt-1">{{ mapboxError }}</div>
                </div>
                <!-- Address Summary for Review -->
                <div v-if="form.address" class="mt-6 p-6 rounded-lg bg-gray-900/80 border-2 border-lime-400">
                  <div class="text-2xl font-extrabold text-lime-400 mb-2">Address Review</div>
                  <div class="text-2xl text-white font-bold tracking-wide">{{ form.address }}</div>
                  <div class="text-lg text-gray-300 mt-2">Please review the address above and ensure it is correct
                    before proceeding.</div>
                </div>
              </div>
              <!-- Employee Summary for Step 5 -->
              <!-- <div class="mt-6 p-6 rounded-lg bg-gray-800/70 border-2 border-lime-400">
                <div class="text-2xl font-extrabold text-lime-400 mb-2">Summary for Employees</div>
                <div class="text-xl text-white font-semibold">
                  Enter the full address where the work will be performed. Double-check for typos. The map preview below
                  will help confirm the location is correct.
                </div>
              </div> -->
            </div>

            <!-- Step 6: Hours -->
            <div v-show="currentStep === 6" class="glossy-section mb-4">
              <label for="hours" class="block text-sm font-medium text-green-400">Approved Hours</label>
              <p class="text-2xl text-white mb-4">Enter the amount of hours approved by the customer, if we need more
                time onsite then we will call at that time to request the estimated hours to complete the WO. This is
                usually about 2-4 hours for our first trip.</p>

              <!-- Quick selection tiles -->
              <div class="grid grid-cols-4 gap-4 mb-6">
                <button v-for="hours in [4, 8, 12, 16]" :key="hours" type="button" @click="form.hours = hours" :class="{
                    'bg-lime-600': form.hours === hours,
                    'hover:bg-lime-700': form.hours === hours,
                    'bg-gray-800 hover:bg-gray-700': form.hours !== hours
                  }"
                  class="glossy-content p-4 rounded-lg text-lime-400 font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-opacity-50">
                  {{ hours }}h
                </button>
              </div>

              <!-- Slider -->
              <div class="mt-6">
                <div class="flex justify-between text-lime-400 text-2xl mb-2">
                  <span>2h</span>
                  <span>16h</span>
                </div>
                <input type="range" v-model="form.hours" id="hours" name="hours" min="2" max="16" step="2"
                  class="slider w-full" required>
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

            <!-- Step 7: Rate & Travel -->
            <div v-show="currentStep === 7" class="glossy-section mb-4">
              <label for="hourlyRate" class="block text-sm font-medium text-green-400">Hourly Rate</label>
              <p class="text-sm text-white mb-4">Select the hourly rate. The total price will be calculated based on the
                approved hours.</p>

              <!-- Quick selection tiles -->
              <div class="grid grid-cols-4 gap-4 mb-6">
                <button v-for="rate in [95, 120, 130, 180]" :key="rate" type="button" @click="form.hourlyRate = rate"
                  :class="{
                    'bg-purple-600': form.hourlyRate === rate,
                    'hover:bg-purple-700': form.hourlyRate === rate,
                    'bg-gray-800 hover:bg-gray-700': form.hourlyRate !== rate
                  }"
                  class="glossy-content p-4 rounded-lg text-purple-400 font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50">
                  ${{ rate }}/hr
                </button>
              </div>

              <!-- Slider with purple theme -->
              <div class="mt-6">
                <div class="flex justify-between text-purple-400 text-sm mb-2">
                  <span>$55/hr</span>
                  <span>$200/hr</span>
                </div>
                <input type="range" v-model="form.hourlyRate" id="hourlyRate" name="hourlyRate" min="55" max="200"
                  step="5" class="slider-purple w-full" required>
                <div class="flex justify-between text-xs text-gray-400 mt-1 overflow-x-auto">
                  <template v-for="value in rateValues" :key="value">
                    <span class="relative text-center whitespace-nowrap" style="min-width: 20px">|</span>
                  </template>
                </div>
              </div>

              <!-- Travel expense section -->
              <div class="mt-8 p-4 rounded-lg bg-gray-800/30 border border-gray-700">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" v-model="form.includeTravel" class="checkbox checkbox-success"
                    id="includeTravel" name="includeTravel" />
                  <span class="text-white">Include Travel Expense</span>
                </label>

                <!-- Travel expense configuration (shows when checkbox is checked) -->
                <div v-if="form.includeTravel" class="mt-4">
                  <!-- Mileage rate quick selection tiles -->
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-purple-400 mb-2">Rate per Mile</label>
                    <div class="grid grid-cols-5 gap-2">
                      <button v-for="rate in [1.00, 0.75, 0.65, 0.50, 0.45]" :key="rate" type="button"
                        @click="form.mileageRate = rate" :class="{
                          'bg-purple-600': form.mileageRate === rate,
                          'hover:bg-purple-700': form.mileageRate === rate,
                          'bg-gray-800 hover:bg-gray-700': form.mileageRate !== rate
                        }"
                        class="glossy-content p-2 rounded-lg text-purple-400 font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 text-sm">
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
                    <input type="range" v-model="form.travelMiles" min="45" max="400" step="5"
                      class="slider-purple w-full" id="travelMiles" name="travelMiles" />
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
              <!-- Selection Summary -->
              <div class="mt-4 p-4 rounded-lg bg-gray-800/50 border border-gray-700">
                <div class="text-center">
                  <div class="text-2xl text-gray-400">Selected Options:</div>
                  <div class="text-lime-400 font-bold text-base">
                    <div>Labor: ${{ laborCost }}</div>
                    <div v-if="form.includeTravel">Travel: ${{ travelCost }}</div>
                    <div>Total: ${{ totalPrice }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 8: Status -->
            <div v-show="currentStep === 8" class="glossy-section mb-4">
              <label class="block text-sm font-medium text-green-400 mb-4">Status</label>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <button v-for="status in ['Scheduled', 'In Progress', 'Part Needed', 'Complete', 'Cancelled']"
                  :key="status" type="button" @click="form.status = status"
                  class="status-badge flex items-center justify-center gap-2 p-3 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-opacity-50"
                  :class="{
                    'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500': status === 'Scheduled' && form.status === status,
                    'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500': status === 'In Progress' && form.status === status,
                    'bg-orange-600 hover:bg-orange-700 focus:ring-orange-500': status === 'Part Needed' && form.status === status,
                    'bg-green-600 hover:bg-green-700 focus:ring-green-500': status === 'Complete' && form.status === status,
                    'bg-red-600 hover:bg-red-700 focus:ring-red-500': status === 'Cancelled' && form.status === status,
                    'bg-gray-800 hover:bg-gray-700': form.status !== status,
                    'glossy-content': true
                  }">
                  <span class="text-white font-medium">{{ status }}</span>
                  <svg v-if="form.status === status" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white"
                    viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                      clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
              <!-- Selection Summary -->
              <div class="mt-4 p-4 rounded-lg bg-gray-800/50 border border-gray-700">
                <div class="text-center">
                  <div class="text-2xl text-gray-400">Selected Status:</div>
                  <div class="text-lime-400 font-bold text-lg">{{ form.status || 'No status selected' }}</div>
                </div>
              </div>
            </div>

            <!-- Step 9: Attachments -->
            <div v-show="currentStep === 9" class="glossy-section mb-4">
              <label class="block text-sm font-medium text-green-400 mb-2">Attachments</label>
              <p class="text-2xl text-white mb-4">Upload any relevant files or documents for this work order.</p>

              <div class="form-control">
                <label
                  class="label cursor-pointer flex items-center justify-center p-8 border-2 border-dashed border-gray-600 rounded-lg hover:border-lime-400 transition-colors duration-200">
                  <input type="file" @change="handleFileUpload" id="file_attachments" name="file_attachments"
                    class="file-input file-input-bordered file-input-success w-full max-w-lg bg-gray-800/50 text-lime-400"
                    multiple />
                </label>

                <!-- File list preview -->
                <div v-if="form.file_attachments && form.file_attachments.length > 0" class="mt-4">
                  <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div v-for="(file, index) in form.file_attachments" :key="index" class="relative group">
                      <div class="aspect-square rounded-lg overflow-hidden bg-gray-800/50 w-24 h-24 mx-auto flex items-center justify-center">
                        <!-- PDF Preview -->
                        <PdfThumbnail v-if="file.type === 'application/pdf'" :pdf-url="getFileObjectURL(file)"
                          :filename="file.name" class="w-full h-full object-cover" />
                        <!-- Image Preview -->
                        <img v-else-if="file.type.startsWith('image/')" :src="getFileObjectURL(file)" :alt="file.name"
                          class="w-full h-full object-cover" />
                        <!-- Default File Icon -->
                        <div v-else class="w-full h-full flex items-center justify-center">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-lime-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                          </svg>
                        </div>
                      </div>
                      <!-- File Info Overlay -->
                      <div
                        class="absolute inset-0 bg-gray-900 bg-opacity-0 group-hover:bg-opacity-70 transition-all duration-200 flex flex-col justify-between p-2 rounded-lg">
                        <div
                          class="text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200 text-xs truncate">
                          {{ file.name }}
                        </div>
                        <button @click="removeFile(index)"
                          class="btn btn-circle btn-xs btn-error opacity-0 group-hover:opacity-100 transition-opacity duration-200 self-end"
                          type="button">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 12 12"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
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

            <input type="hidden" v-model="form.user_id" name="user_id" />
            <progress v-if="form.progress" :value="form.progress.percentage" min="0" max="100"
              class="w-full rounded-md">
              {{ form.progress.percentage }}%
            </progress>
          </div>
        </form>

        <!-- Fixed footer with navigation buttons -->
        <div class="glossy-footer px-4 py-2 border-t border-gray-700 mt-auto sticky bottom-0 bg-gray-900/95 z-20 min-h-0">
          <div class="flex justify-between items-center min-h-0">
            <button v-if="currentStep > 1" type="button" @click="prevStep"
              class="btn glass-button flex items-center space-x-1 px-5 py-2 bg-gray-700/50 text-white hover:bg-gray-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              <span>Back</span>
            </button>
            <div v-else></div>

            <div>
              <button v-if="currentStep < totalSteps" type="button" @click="nextStep"
                class="btn glass-button flex items-center space-x-1 px-6 py-2 bg-blue-600/60 text-white hover:bg-blue-700">
                <span>Next</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
              <button v-if="currentStep === totalSteps" type="button" @click="submitForm"
                :disabled="!validateCurrentStep() || form.processing || duplicateWorkOrderFound"
                class="btn glass-button flex items-center space-x-2 px-6 py-2 bg-lime-600/60 text-white hover:bg-lime-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <span>Submit</span>
                <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                  fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import NetworkStatusIndicator from '@/Components/NetworkStatusIndicator.vue';
import PdfThumbnail from '@/Components/PdfThumbnail.vue';
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue';
import { format, parseISO, isToday, isValid, addDays, setHours, setMinutes } from 'date-fns';
import axios from 'axios';
import { useToast } from '@/Composables/useToast';

// Props
const props = defineProps({
  customerId: {
    type: [String, Number],
    default: null
  },
  customerName: {
    type: String,
    default: null
  }
});

// Data
const isLoading = ref(false);
const isLoadingCustomers = ref(false);
const isLoadingTechnicians = ref(false);  // Added missing ref
const customers = ref([]);
const customersArray = ref([]);
const loadError = ref(false);
const showModal = ref(false);
const currentStep = ref(1);
const totalSteps = 9;
const workType = ref('');
const workOrderNumber = ref('');
const location = ref('');
const dateSelectionType = ref('single');
const selectedDates = ref([{ date: new Date().toISOString().slice(0, 16) }]);
const isSubmitting = ref(false);
const checkingWorkOrder = ref(false);
const duplicateWorkOrderFound = ref(false);
const debounceTimer = ref(null);
const selectedDate = ref(new Date());
const currentMonth = ref(new Date());
const calendarDays = ref([]);
const technicians = ref([]);
const rateValues = ref([55, 60, 65, 70, 75, 80, 85, 90, 95, 100, 105, 110, 115, 120, 125, 130, 135, 140, 145, 150, 155, 160, 165, 170, 175, 180, 185, 190, 195, 200]);

const selectedTime = ref({
  hour: 12,
  minute: '00',
  period: 'AM'
});

const form = useForm({
  customer_id: '',
  title: '',
  description: '',
  date_time: '',
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
  return (parseFloat(laborCost.value) + parseFloat(travelCost.value)).toFixed(2);
});

const currentMonthName = computed(() => {
  return format(currentMonth.value, 'MMMM yyyy');
});

const selectedDateFormatted = computed(() => {
  return isValid(selectedDate.value) ? format(selectedDate.value, 'EEEE, MMMM d, yyyy') : '';
});

const formattedDateTime = computed(() => {
  if (!isValid(selectedDate.value)) return 'Please select a date';
  
  const hour = parseInt(selectedTime.value.hour);
  let formattedHour = hour;
  
  // Convert to 24-hour format if PM
  if (selectedTime.value.period === 'PM' && hour !== 12) {
    formattedHour += 12;
  }
  // Handle 12 AM case
  if (selectedTime.value.period === 'AM' && hour === 12) {
    formattedHour = 0;
  }
  
  // Format date with time
  const dateWithTime = new Date(selectedDate.value);
  dateWithTime.setHours(formattedHour);
  dateWithTime.setMinutes(parseInt(selectedTime.value.minute));
  
  return format(dateWithTime, 'MMMM d, yyyy h:mm a');
});

const descriptionSummary = computed(() => {
  return form.description && form.description.trim() !== '' ? form.description : 'No description entered';
});

// Methods
const handleShowModal = () => {
  console.log('AddWorkOrder: handleShowModal called');
  resetForm();
  showModal.value = true;
  
  // Add a slight delay to ensure modal is fully shown before loading data
  setTimeout(() => {
    Promise.all([
      loadCustomers().catch(err => console.error('Error loading customers:', err)),
      loadTechnicians().catch(err => console.error('Error loading technicians:', err))
    ]).then(() => {
      console.log('AddWorkOrder: All data loaded', {
        customersCount: customers.value.length,
        techniciansCount: technicians.value.length
      });
    });
  }, 100);
};

const handleHideModal = () => {
  showModal.value = false;
  resetForm();
};

const resetForm = () => {
  console.log('Resetting form');
  currentStep.value = 1;
  // Reset form fields
  form.customer_id = props.customerId || '';
  form.title = '';
  form.description = '';
  form.date_time = '';
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
  selectedTime.value = {
    hour: 12,
    minute: '00',
    period: 'AM'
  };
};

const validateCurrentStep = () => {
  switch(currentStep.value) {
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
      return !!form.status;
    case 9:
      return true; // Files are optional
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

const handleFileUpload = (event) => {
  const files = event.target.files;
  if (!files || files.length === 0) return;
  
  // Add the files to the form.file_attachments array
  form.file_attachments = [...form.file_attachments, ...Array.from(files)];
  
  // Clear the input to allow selecting the same file again if needed
  event.target.value = null;
};

const removeFile = (index) => {
  // Remove a file from the attachments array by index
  form.file_attachments = form.file_attachments.filter((_, i) => i !== index);
};

const checkExistingWorkOrder = () => {
  // Implement validation for duplicate work orders if needed
  // For now, just a placeholder to prevent errors
  duplicateWorkOrderFound.value = false;
};

const { success: toastSuccess, error: toastError } = useToast();

const submitForm = () => {
  if (currentStep.value === totalSteps && validateCurrentStep()) {
    try {
      // Set the title from formattedTitle
      form.title = formattedTitle.value;
      
      // Set the user_id from auth if available using usePage() instead of this.$page
      const page = usePage();
      // Add proper null checking to avoid undefined errors
      if (!form.user_id && page.props.value && page.props.value.auth && page.props.value.auth.user) {
        form.user_id = page.props.value.auth.user.id;
        form.users_name = page.props.value.auth.user.name;
      } else {
        // Fallback to set user_id if not available from auth
        // This might happen when the component is in a modal or iframe context
        console.log('Auth user not available, using fallback values');
        if (!form.user_id) {
          // You might want to get these values from props or a store if available
          form.user_id = 1; // Default to admin or system user
          form.users_name = form.users_name || 'System User';
        }
      }
      
      // Format the form data
      const formData = new FormData();
      
      // Add all required fields
      formData.append('customer_id', form.customer_id);
      formData.append('title', form.title);
      formData.append('description', form.description);
      formData.append('date_time', form.date_time);
      formData.append('address', form.address);
      formData.append('hours', parseFloat(form.hours));
      formData.append('price', parseFloat(totalPrice.value));
      formData.append('status', form.status);
      formData.append('user_id', form.user_id);
      if (form.technician_id) {
        formData.append('technician_id', form.technician_id);
      }
      formData.append('hourly_rate', form.hourlyRate);
      // Add optional fields if they exist
      if (form.end_date) {
        formData.append('end_date', form.end_date);
      }
      
      // Add file attachments if any
      if (form.file_attachments.length > 0) {
        form.file_attachments.forEach((file, index) => {
          // Fix: use file_attachments instead of attachments to match backend
          formData.append(`file_attachments[${index}]`, file);
        });
      }

      // Log the form data being sent
      const formDataObj = {};
      formData.forEach((value, key) => {
        formDataObj[key] = value;
      });
      console.log('Submitting form data:', formDataObj);

      // Submit the form
      isSubmitting.value = true;
      form.post('/work-orders', {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
          console.log('Success response:', response);
          showModal.value = false;
          isSubmitting.value = false;
          // Show personalized toast
          const page = usePage();
          const userName = page.props.value?.auth?.user?.name || '';
          const firstName = userName.split(' ')[0] || 'User';
          toastSuccess(`Work order created successfully! Welcome, ${firstName}!`);
          resetForm();
        },
        onError: (errors) => {
          console.error('Form submission errors:', {
            errors,
            formData: formDataObj,
            validationState: validateCurrentStep()
          });
          
          // Show more specific error messages
          let errorMessage = 'An error occurred while creating the work order.\n\n';
          if (typeof errors === 'string') {
            errorMessage += errors;
          } else if (errors.error) {
            errorMessage += errors.error;
          } else if (errors.message) {
            errorMessage += errors.message;
          } else {
            // Create a formatted error message from all validation errors
            for (const [field, messages] of Object.entries(errors)) {
              errorMessage += `${field}: ${messages.join(', ')}\n`;
            }
          }
          
          // Add response status if available
          if (errors.response && errors.response.status) {
            errorMessage += `\nResponse status: ${errors.response.status}`;
            
            // For 500 errors, add more info
            if (errors.response.status === 500) {
              errorMessage += "\n\nThis is a server error. Please check the server logs for more details.";
              
              // Try to extract more error information if available
              if (errors.response.data && errors.response.data.debug) {
                console.log('Server error debug info:', errors.response.data.debug);
              }
              
              if (errors.response.data && errors.response.data.message) {
                errorMessage += `\nError message: ${errors.response.data.message}`;
              }
            }
          }
          
          // Replace alert(errorMessage.trim());
          toastError(errorMessage.trim());
          isSubmitting.value = false;
        },
        onFinish: () => {
          isSubmitting.value = false;
        }
      });
    } catch (error) {
      console.error('Error in form submission:', error);
      // Add more detailed debugging information
      const page = usePage();
      console.log('Page props available:', page?.props?.value ? 'Yes' : 'No');
      console.log('Auth props available:', page?.props?.value?.auth ? 'Yes' : 'No');
      
      // Replace alert('An unexpected error occurred. Please try again. ' + error.message);
      toastError('An unexpected error occurred. Please try again. ' + error.message);
      isSubmitting.value = false;
    }
  } else {
    console.warn('Form validation failed:', validateCurrentStep());
  }
};

// Date picker methods
const generateCalendar = () => {
  const year = currentMonth.value.getFullYear();
  const month = currentMonth.value.getMonth();
  
  // Get first day of the month
  const firstDay = new Date(year, month, 1);
  // Get last day of the month
  const lastDay = new Date(year, month + 1, 0);
  
  // Start from the week that contains the first day
  const startingDayOfWeek = firstDay.getDay(); // 0 = Sunday, 6 = Saturday
  
  // Create array with empty slots for days before the first of the month
  const calendarArray = Array(startingDayOfWeek).fill(null);
  
  // Add all days of the month
  for (let day = 1; day <= lastDay.getDate(); day++) {
    calendarArray.push(new Date(year, month, day));
  }
  
  calendarDays.value = calendarArray;
};

const nextMonth = () => {
  currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1, 1);
  generateCalendar();
};

const prevMonth = () => {
  currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1, 1);
  generateCalendar();
};

const selectDate = (date) => {
  selectedDate.value = date;
};

const isSameDate = (date1, date2) => {
  if (!date1 || !date2) return false;
  return date1.getFullYear() === date2.getFullYear() &&
         date1.getMonth() === date2.getMonth() &&
         date1.getDate() === date2.getDate();
};

// Add this method to fix the isToday error
const checkIsToday = (date) => {
  return isToday(date);
};

const quickSelectDate = (type) => {
  const today = new Date();
  switch (type) {
    case 'today':
      selectedDate.value = today;
      break;
    case 'tomorrow':
      selectedDate.value = addDays(today, 1);
      break;
    case 'nextWeek':
      selectedDate.value = addDays(today, 7);
      break;
  }
  
  // Update current month to show the selected date
  currentMonth.value = new Date(selectedDate.value.getFullYear(), selectedDate.value.getMonth(), 1);
  generateCalendar();
};

// Add getFileObjectURL method for file/image preview
const getFileObjectURL = (file) => {
  if (!file) return '';
  if (file.previewUrl) return file.previewUrl;
  // Create a new object URL and cache it on the file object
  try {
    const url = URL.createObjectURL(file);
    file.previewUrl = url;
    return url;
  } catch (error) {
    console.error('Error creating object URL for file:', error);
    return '';
  }
};

const updateFormDateTime = () => {
  // Skip if no valid selected date
  if (!isValid(selectedDate.value)) return;
  
  // Convert hour to 24-hour format if needed
  let hour = parseInt(selectedTime.value.hour);
  if (selectedTime.value.period === 'PM' && hour < 12) {
    hour += 12;
  } else if (selectedTime.value.period === 'AM' && hour === 12) {
    hour = 0;
  }
  
  // Create new date with selected date and time
  const dateWithTime = new Date(selectedDate.value);
  dateWithTime.setHours(hour);
  dateWithTime.setMinutes(parseInt(selectedTime.value.minute) || 0);
  
  // Update form.date_time with ISO string format
  form.date_time = format(dateWithTime, "yyyy-MM-dd'T'HH:mm:ss");
  
  // Log to verify the date is being set correctly
  console.log('Date updated:', form.date_time);
};

onMounted(() => {
  console.log('Component mounted');
  
  // Set initial form.date_time based on selected date and time
  const initialDate = new Date();
  
  // Convert hour to 24-hour format if needed
  let hour = parseInt(selectedTime.value.hour);
  if (selectedTime.value.period === 'PM' && hour < 12) {
    hour += 12;
  } else if (selectedTime.value.period === 'AM' && hour === 12) {
    hour = 0;
  }
  
  initialDate.setHours(hour);
  initialDate.setMinutes(parseInt(selectedTime.value.minute));
  
  form.date_time = format(initialDate, "yyyy-MM-dd'T'HH:mm:ss");
  
  // Apply props if provided
  if (props.customerName) {
    location.value = props.customerName;
  }
  
  if (props.customerId) {
    form.customer_id = props.customerId;
  }
  
  // Initialize calendar on next tick to ensure DOM is ready
  nextTick(() => {
    try {
      generateCalendar();
    } catch (error) {
      console.error('Calendar generation error:', error);
    }
  });
});

// Watchers
watch(currentStep, async (newStep) => {
  // Scroll to top of form when step changes
  await nextTick(() => {
    const formElement = document.querySelector('.overflow-y-auto');
    if (formElement) {
      formElement.scrollTop = 0;
    }
  });
  
  // Regenerate calendar when entering Step 4
  if (newStep === 4) {
    await nextTick(() => {
      generateCalendar();
    });
  }
}, { flush: 'post' });

watch([selectedDate, () => selectedTime.value.hour, () => selectedTime.value.minute, () => selectedTime.value.period], () => {
  updateFormDateTime();
});

watch(() => form.customer_id, async (newCustomerId) => {
  if (newCustomerId) {
    try {
      const response = await axios.get(`/customers/${newCustomerId}`);
      if (response.data && response.data.pay_rate) {
        // Update the hourly rate with the customer's pay rate
        form.hourlyRate = parseFloat(response.data.pay_rate);
      }
    } catch (error) {
      console.error('Error fetching customer data:', error);
      // Keep the default rate if there's an error
    }
  }
});

// Watcher for showModal
watch(showModal, (newValue) => {
  console.log('Modal visibility changed:', newValue);
  
  if (newValue === true) {
    try {
      // If the modal is now open, initialize step 1 data
      if (props.customerId) {
        form.customer_id = props.customerId;
      }
      if (props.customerName) {
        location.value = props.customerName;
      }
    } catch (error) {
      console.error('Error in showModal watcher:', error);
    }
  } else {
    // If modal is closed, perform cleanup to avoid memory leaks
    try {
      // Reset potentially problematic state
      calendarDays.value = [];
      
      // Other cleanup as needed
      // ...
    } catch (error) {
      console.error('Error during modal cleanup:', error);
    }
  }
});

// Helper function to get selected customer name
const getSelectedCustomerName = () => {
  if (!form.customer_id || !customersArray.value || !Array.isArray(customersArray.value)) {
    return null;
  }
  const selectedCustomer = customersArray.value.find(c => c.id === form.customer_id);
  return selectedCustomer ? selectedCustomer.business_name : null;
};

// Methods for API calls
const loadCustomers = async () => {
  console.log('AddWorkOrder: loadCustomers started');
  isLoadingCustomers.value = true;
  loadError.value = false;

  try {
    // Direct API call to backend only
    const response = await axios.get('/api/customers', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    if (typeof response.data === 'string') {
      if (response.data.trim().startsWith('<!DOCTYPE html') || response.data.trim().startsWith('<html')) {
        throw new Error('Server returned HTML instead of JSON.');
      }
      try {
        const parsed = JSON.parse(response.data);
        if (Array.isArray(parsed)) {
          customers.value = parsed;
          customersArray.value = parsed;
          loadError.value = false;
          return parsed;
        } else if (parsed && typeof parsed === 'object' && Array.isArray(parsed.data)) {
          customers.value = parsed.data;
          customersArray.value = parsed.data;
          loadError.value = false;
          return parsed.data;
        } else {
          throw new Error('Parsed string but result is not a valid array format');
        }
      } catch (parseError) {
        throw new Error('Response is a string that could not be parsed as JSON');
      }
    }

    if (response.data && Array.isArray(response.data)) {
      customers.value = response.data;
      customersArray.value = response.data;
      loadError.value = false;
      return response.data;
    } else if (response.data && typeof response.data === 'object' && Array.isArray(response.data.data)) {
      customers.value = response.data.data;
      customersArray.value = response.data.data;
      loadError.value = false;
      return response.data.data;
    } else {
      throw new Error('Invalid response format: Expected array but got ' + typeof response.data);
    }
  } catch (error) {
    console.error('AddWorkOrder: Failed to load customers:', error);
    customers.value = [];
    customersArray.value = [];
    loadError.value = true;
    // If customer ID is set from props, create a minimal customer array with just that customer
    if (props.customerId && props.customerName) {
      const singleCustomer = {
        id: props.customerId,
        business_name: props.customerName
      };
      customersArray.value = [singleCustomer];
      console.log('AddWorkOrder: Using single customer from props:', singleCustomer);
    }
    return [];
  } finally {
    isLoadingCustomers.value = false;
  }
};

const loadTechnicians = async () => {
  console.log('AddWorkOrder: loadTechnicians started');
  isLoadingTechnicians.value = true;
  try {
    const response = await axios.get('/api/technicians/active');
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

// --- Mapbox Location Preview Logic ---

const mapboxAccessToken = 'pk.eyJ1Ijoibm10ZWNoIiwiYSI6ImNtYndzNG0yZTB2MTQycm9yMmxrZTJiOXYifQ.teJIWClLiWUJvvacQC3EFQ'; // Replace with your real token
const mapboxCoords = ref({ lat: null, lon: null });
const mapboxImageUrl = computed(() => {
  const { lat, lon } = mapboxCoords.value;
  if (!lat || !lon) return '';
  return `https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/pin-s+ff0000(${lon},${lat})/${lon},${lat},16/600x200?access_token=${mapboxAccessToken}`;
});
const mapboxMapsLink = computed(() => {
  const { lat, lon } = mapboxCoords.value;
  if (!lat || !lon) return '#';
  return `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lon}#map=16/${lat}/${lon}`;
});
const mapboxLoading = ref(false);
const mapboxError = ref(null);
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
watch(() => form.address, (newAddress) => {
  geocodeAddress(newAddress);
}, { immediate: true });

const getSelectedTechnicianName = () => {
  if (!form.technician_id) return '';
  const tech = technicians.value.find(t => String(t.id) === String(form.technician_id));
  return tech ? tech.name : '';
};

// Keep hourly_rate in sync with hourlyRate
watch(() => form.hourlyRate, (val) => {
  form.hourly_rate = val;
});

// Fetch customer hourly rate on initial mount if customer is pre-selected
onMounted(() => {
  console.log('Component mounted');
  
  // Set initial form.date_time based on selected date and time
  const initialDate = new Date();
  
  // Convert hour to 24-hour format if needed
  let hour = parseInt(selectedTime.value.hour);
  if (selectedTime.value.period === 'PM' && hour < 12) {
    hour += 12;
  } else if (selectedTime.value.period === 'AM' && hour === 12) {
    hour = 0;
  }
  
  initialDate.setHours(hour);
  initialDate.setMinutes(parseInt(selectedTime.value.minute));
  
  form.date_time = format(initialDate, "yyyy-MM-dd'T'HH:mm:ss");
  
  // Apply props if provided
  if (props.customerName) {
    location.value = props.customerName;
  }
  
  if (props.customerId) {
    form.customer_id = props.customerId;
  }
  
  // Initialize calendar on next tick to ensure DOM is ready
  nextTick(() => {
    try {
      generateCalendar();
    } catch (error) {
      console.error('Calendar generation error:', error);
    }
  });
  
  // If a customer is pre-selected, fetch their hourly rate immediately
  if (form.customer_id) {
    fetchCustomerHourlyRate(form.customer_id);
  }
});

// Fetch customer hourly rate
const fetchCustomerHourlyRate = async (customerId) => {
  if (!customerId) return;
  try {
    const response = await axios.get(`/customers/${customerId}`);
    const payRate = response.data?.pay_rate;
    if (payRate !== undefined && payRate !== null) {
      form.hourlyRate = payRate;
      form.hourly_rate = payRate;
    }
  } catch (error) {
    // Optionally handle error (e.g., show notification)
    form.hourlyRate = '';
    form.hourly_rate = '';
  }
};

// Watch for customer_id changes
watch(() => form.customer_id, (newVal) => {
  fetchCustomerHourlyRate(newVal);
}, { immediate: true });
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
  height: 85vh;
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
  box-shadow:  0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
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
  display: flex;
  flex-direction: column;
  background: rgba(15, 23, 42, 0.85);
   max-width: 90vw;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.glossy-header {
  background: linear-gradient(to right, rgba(17, 24, 39, 0.9), rgba(31, 41, 55, 0.85));
}

.glossy-footer {
  background: linear-gradient(to right, rgba(20, 30, 48, 0.9), rgba(30, 41, 59, 0.85));
  box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06);
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
  z-index: 10;
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
  max-height: 85vh; /* Allow more space in the modal */
  scroll-behavior: smooth;
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
  max-width: 90vw;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.glossy-header {
  background: linear-gradient(to right, rgba(17, 24, 39, 0.9), rgba(31, 41, 55, 0.85));
}

.glossy-footer {
  background: linear-gradient(to right, rgba(20, 30, 48, 0.9), rgba(30, 41, 59, 0.85));
  box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06);
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
  z-index: 10;
}

/* Glass button styling */
.glass-button {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(255,  255, 255, 0.2);
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

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  /* Dark mode glass button styles are now handled in the enhanced styling section */
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
  box-shadow:  0 0 0 4px rgba(147, 51, 234, 0.1);
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


.status-badge {
  backdrop-filter: blur(10px);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
  border: 1px solid rgba(255, 255, 255,  0.18);
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

/* Loading spinner styles */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }

  100% { transform: rotate(360deg); }
}

/* Enhanced date picker styling */
.calendar-picker {
  background: rgba(31, 41, 55, 0.95);
  border-radius: 0.5rem;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
  overflow: hidden;
}

.calendar-day-button {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  border-radius: 0.375rem;
  transition: all 0.2s ease;
}

.calendar-day-button:hover {
  background-color: rgba(132, 204, 22, 0.2);
}

.calendar-day-selected {
  background-color: rgba(132, 204, 22, 0.8) !important;
  color: white !important;
  font-weight: 600;
}

.calendar-day-today {
  border: 1px solid rgba(132, 204, 22, 0.5);
  font-weight: 500;
  background-color: rgba(132, 204, 22, 0.1);
}

/* Form content scrolling improvements */
.overflow-y-auto {
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: rgba(75, 85, 99, 0.5) rgba(31, 41, 55, 0.6);
  scroll-behavior: smooth;
}

/* Footer button focus and active states */
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
  margin-bottom: 1.5rem;
}
</style>