<template>
  <div class="glass-container p-5 mb-6">
    <div class="flex justify-between items-center mb-4">
      <div class="flex items-center gap-2">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
          Work Order Calendar
        </h2>
      </div>
      <div class="flex gap-2">
        <button @click="toggleWeekends" class="glass-button px-3 py-1 rounded text-white flex items-center gap-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Toggle Weekends
        </button>
        <AddWorkOrder class="px-3 py-1 rounded" />
      </div>
    </div>
    
    <div class="glass-card p-4 rounded-lg">
      <FullCalendar 
        ref="calendarRef"
        :options="calendarOptions"
      />
    </div>
  </div>

  <!-- Work Order Modal -->
  <div v-if="showWorkOrderModal && selectedWorkOrder" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-800 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
              <!-- Header with close button -->
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                  Work Order #{{ selectedWorkOrder.id }}
                </h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-200">
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <!-- Work order details -->
                <div class="text-gray-300 space-y-3">
                <div v-if="selectedWorkOrder.title" class="mb-2">
                  <h4 class="text-lg font-medium text-white">{{ selectedWorkOrder.title }}</h4>
                </div>
                
                <div v-if="selectedWorkOrder.status" class="flex justify-between">
                  <div>
                  <p class="text-sm text-gray-400">Status:</p>
                  <span class="inline-flex px-2 py-1 text-xs rounded" 
                      :class="{
                      'bg-green-800 text-green-100': selectedWorkOrder.status.toLowerCase().includes('complete'),
                      'bg-blue-800 text-blue-100': selectedWorkOrder.status.toLowerCase().includes('scheduled'),
                      'bg-yellow-800 text-yellow-100': selectedWorkOrder.status.toLowerCase().includes('progress'),
                      'bg-red-800 text-red-100': selectedWorkOrder.status.toLowerCase().includes('cancel'),
                      'bg-purple-800 text-purple-100': selectedWorkOrder.status.toLowerCase().includes('part') || 
                                      selectedWorkOrder.status.toLowerCase().includes('return')
                      }">
                    {{ selectedWorkOrder.status }}
                  </span>
                  </div>
                  
                  <div v-if="selectedWorkOrder.price">
                  <p class="text-sm text-gray-400">Price:</p>
                  <span>${{ selectedWorkOrder.price }}</span>
                  </div>
                </div>
                
                <div class="mt-4">
                  <p class="text-sm text-gray-400">Customer:</p>
                  <p class="text-white">{{ selectedWorkOrder.customer?.business_name || 'No Customer' }}</p>
                </div>
                
                <div v-if="selectedWorkOrder.created_at || selectedWorkOrder.date">
                  <p class="text-sm text-gray-400">Date:</p>
                  <p>{{ new Date(selectedWorkOrder.created_at || selectedWorkOrder.date).toLocaleString() }}</p>
                </div>
                
                <div v-if="selectedWorkOrder.description" class="mt-4">
                  <p class="text-sm text-gray-400">Description:</p>
                  <p class="whitespace-pre-line mt-1 text-sm bg-gray-800 p-3 rounded-md">
                  {{ selectedWorkOrder.description }}
                  </p>
                </div>
                
                <div v-if="selectedWorkOrder.address" class="mt-4">
                  <p class="text-sm text-gray-400">Address:</p>
                  <p class="whitespace-pre-line mt-1 text-sm bg-gray-800 p-3 rounded-md">
                  {{ selectedWorkOrder.address }}
                  </p>
                </div>
                
                <div class="mt-4">
                  <p class="text-sm text-gray-400">Technician:</p>
                  <p class="text-white">{{ selectedWorkOrder.technician?.name || 'No Technician Assigned' }}</p>
                </div>
                </div>
            </div>
          </div>
        </div>
        
        <!-- Modal footer -->
        <div class="bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <a :href="`/work-orders/${selectedWorkOrder.id}`" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
            View Full Details
          </a>
          <button @click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-700 shadow-sm px-4 py-2 bg-gray-700 text-base font-medium text-gray-300 hover:bg-gray-600 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            Close
          </button>
        </div>
        </div></div>
      </div>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import resourceTimelinePlugin from '@fullcalendar/resource-timeline';
import { CalendarOptions } from '@fullcalendar/core';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import AddWorkOrder from '@/Pages/WorkOrders/AddWorkOrder.vue';
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';
import 'tippy.js/animations/shift-away.css';

// Define the events emitted by this component
const emit = defineEmits(['workOrderSelected']);

const calendarRef = ref<InstanceType<typeof FullCalendar> | null>(null);
const isLoading = ref(true);
const showWorkOrderModal = ref(false);
const selectedWorkOrder = ref<any>(null);

function getStatusColor(status: string): string {
  switch (status?.toLowerCase()) {
    case 'complete':
      return '#279c54';
    case 'scheduled':
      return '#223694';
    case 'in progress':
      return '#b59023';
    case 'cancelled':
      return '#ef4444';
    case 'part/return':
      return '#844ac4';
    default:
      return '#844ac4';
  }
}

const calendarOptions: CalendarOptions = {
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin, resourceTimelinePlugin],
  initialView: 'dayGridMonth',
  editable: true,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
  },
  events: [],
  eventContent: (info) => {
    const eventEl = document.createElement('div');
    eventEl.classList.add('glass-event-pill');
    
    // Add status color indicator
    const statusBar = document.createElement('div');
    statusBar.classList.add('status-indicator');
    statusBar.style.backgroundColor = info.event.backgroundColor;
    
    // Create content wrapper
    const contentWrapper = document.createElement('div');
    contentWrapper.classList.add('event-content');
    
    // Add title
    const titleEl = document.createElement('div');
    titleEl.classList.add('event-title');
    titleEl.textContent = info.event.title;
    
    // Add time if available
    if (info.event.start) {
      const timeEl = document.createElement('div');
      timeEl.classList.add('event-time');
      timeEl.textContent = new Date(info.event.start).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
      });
      contentWrapper.appendChild(timeEl);
    }

    contentWrapper.appendChild(titleEl);
    eventEl.appendChild(statusBar);
    eventEl.appendChild(contentWrapper);
    
    return { domNodes: [eventEl] };
  },
  eventClick: async (info) => {
    const workOrderId = info.event.id;
    // Emit the event to the parent component
    emit('workOrderSelected', workOrderId);
    
    // Also handle it internally if needed
    try {
      // Use the detailed endpoint that includes relationships
      const response = await axios.get(`/work-orders/${workOrderId}/details`);
      console.log('Fetched work order details:', response.data);
      selectedWorkOrder.value = response.data;
      showWorkOrderModal.value = true;
    } catch (error) {
      console.error('Error loading work order:', error);
      alert('Failed to load work order details');
    }
  },
  eventDidMount: (info) => {
    // Enhanced tooltips with more work order information
    const tooltip = document.createElement('div');
    tooltip.classList.add('calendar-tooltip');
    
    // Get customer name and business name from extended props
    const customerName = info.event.extendedProps?.customer_name || '';
    const businessName = info.event.extendedProps?.customer_business_name || '';
      
    // Get technician name if available
    const technicianName = info.event.extendedProps?.technician_name || '';
    
    tooltip.innerHTML = `
      <div class="tooltip-header">
        <div class="tooltip-title">${info.event.title}</div>
        <div class="tooltip-status-pill" style="background-color: ${info.event.backgroundColor}">
          ${info.event.extendedProps?.status || 'Unknown'}
        </div>
      </div>
      <div class="tooltip-body">
        ${businessName ? `<div class="tooltip-item tooltip-customer-highlight">
          <span class="tooltip-label">Customer:</span>
          <span class="tooltip-value">${businessName}</span>
        </div>` : ''}
        ${technicianName ? `<div class="tooltip-item">
          <span class="tooltip-label">Technician:</span>
          <span class="tooltip-value">${technicianName}</span>
        </div>` : ''}
        ${info.event.extendedProps?.description ? `
        <div class="tooltip-description-wrapper">
          <span class="tooltip-label">Description:</span>
          <div class="tooltip-description">${info.event.extendedProps.description.substring(0, 100)}${info.event.extendedProps.description.length > 100 ? '...' : ''}</div>
        </div>` : ''}
        <div class="tooltip-item">
          <span class="tooltip-label">Date:</span>
          <span class="tooltip-value">${info.event.start ? new Date(info.event.start).toLocaleDateString() : ''}</span>
        </div>
        <div class="tooltip-item">
          <span class="tooltip-label">Time:</span>
          <span class="tooltip-value">${info.event.start ? new Date(info.event.start).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
          }) : ''}</span>
        </div>
      </div>
    `;
    
    // Add tooltip to the event element
    tippy(info.el, {
      content: tooltip,
      allowHTML: true,
      theme: 'tekdash-glass',
      placement: 'top',
      arrow: true,
      animation: 'shift-away',
      duration: [200, 150],
      interactive: true,
      maxWidth: 350,
      appendTo: () => document.body,
      popperOptions: {
        modifiers: [{
          name: 'preventOverflow',
          options: {
            padding: 10
          }
        }]
      }
    });
  },
  weekends: true,
  height: 'auto',
  eventTimeFormat: {
    hour: 'numeric',
    minute: '2-digit',
    meridiem: 'short' as 'short' | 'narrow' | 'lowercase'
  }
};

async function fetchEvents() {
  try {
    console.log('Fetching calendar events...');
    const response = await fetch('/api/work-orders-calendar', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    });

    if (!response.ok) {
      console.error(`Calendar API error: ${response.status}`, await response.text());
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const rawResponse = await response.text();
    let parsedData;
    
    try {
      // Try to parse the response as JSON
      parsedData = JSON.parse(rawResponse);
    } catch (parseError) {
      console.error('Failed to parse calendar response as JSON:', parseError);
      throw new Error(`Invalid JSON response: ${rawResponse.substring(0, 100)}...`);
    }
    
    // Handle different response formats
    let calendarData;
    
    if (Array.isArray(parsedData)) {
      // Direct array response
      calendarData = parsedData;
      console.log('Calendar data is a direct array:', calendarData.length);
    } else if (parsedData && typeof parsedData === 'object') {
      // Object response that might contain data property
      if (Array.isArray(parsedData.data)) {
        calendarData = parsedData.data;
        console.log('Calendar data found in data property:', calendarData.length);
      } else if (parsedData.events && Array.isArray(parsedData.events)) {
        calendarData = parsedData.events;
        console.log('Calendar data found in events property:', calendarData.length);
      } else {
        console.error('No array found in response object:', parsedData);
        throw new Error('Response contains an object but no usable array data');
      }
    } else {
      console.error('Unhandled response format:', typeof parsedData);
      throw new Error(`Invalid data format: Expected array or object but got ${typeof parsedData}`);
    }

    // Debug: log the processed calendar data
    console.log('Processed calendar data:', calendarData);
    
    const events = data.flatMap(workOrder => {
      // Debug: log each work order's date fields to check format
      console.log(`Work Order #${workOrder.id}:`, {
        title: workOrder.title,
        date_time: workOrder.date_time,
        end_date: workOrder.end_date,
        visit_dates: workOrder.visit_dates
      });
      
      const baseEvent = {
        id: workOrder.id,
        title: workOrder.title, // Just use the work order title without customer name
        description: workOrder.description || '',
        status: workOrder.status,
        backgroundColor: getStatusColor(workOrder.status),
        borderColor: getStatusColor(workOrder.status),
        customer_name: workOrder.customer_name || '',
        technician_name: workOrder.technician_name || '',
        customer_business_name: workOrder.customer_business_name || '',
      };

      // Always use workOrder.date_time as the main event date if present
      if (workOrder.date_time) {
        return [{
          ...baseEvent,
          start: workOrder.date_time,
          end: workOrder.end_date || undefined,
          extendedProps: {
            ...baseEvent,
            isMultiDayEvent: Boolean(workOrder.end_date),
            visit_dates: workOrder.visit_dates || [],
            customer_name: workOrder.customer_name || '',
            customer_business_name: workOrder.customer_business_name || '',
            technician_name: workOrder.technician_name || '',
            description: workOrder.description || '',
            status: workOrder.status || 'Unknown'
          }
        }];
      }

      // Fallback: if visit_dates exist but no date_time, show all visit_dates
      if (workOrder.visit_dates?.length) {
        return workOrder.visit_dates.map((visitDate, index) => ({
          ...baseEvent,
          start: visitDate,
          extendedProps: {
            ...baseEvent,
            isMultiDayEvent: true,
            visitNumber: index + 1,
            totalVisits: workOrder.visit_dates.length,
            customer_name: workOrder.customer_name || '',
            customer_business_name: workOrder.customer_business_name || '',
            technician_name: workOrder.technician_name || '',
            description: workOrder.description || '',
            status: workOrder.status || 'Unknown'
          }
        }));
      }

      // If no date info, skip event
      return [];
    });

    // Debug: log the processed events
    console.log('Processed events:', events);

    if (calendarRef.value) {
      const calendar = calendarRef.value.getApi();
      calendar.removeAllEvents();
      calendar.addEventSource(events);
    }
  } catch (error) {
    console.error('Error fetching calendar events:', error);
  } finally {
    isLoading.value = false;
  }
}

function toggleWeekends() {
  if (calendarRef.value) {
    const calendar = calendarRef.value.getApi();
    calendar.setOption('weekends', !calendar.getOption('weekends'));
  }
}

function closeModal() {
  showWorkOrderModal.value = false;
  selectedWorkOrder.value = null;
}

onMounted(fetchEvents);
</script>

<style scoped>
/* Additional header glow effect */
.glossy-header::after {
  content: '';
  position: absolute;
  top: -10px;
  left: 0;
  right: 0;
  height: 10px;
  background: linear-gradient(180deg, rgba(163, 230, 53, 0.1), transparent);
  pointer-events: none;
}

.glossy-footer {
  background: linear-gradient(0deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.85) 100%);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  position: relative;
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
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

/* Make sure navbar sticks to the top even when scrolling */
body {
  padding-top: 0 !important; /* Ensure no default padding interferes */
  scroll-padding-top: 64px; /* Height of your navbar */
}

/* Add vertical spotlight effect for dropdown items */
.dropdown-content .dropdown-item {
  position: relative;
  overflow: hidden;
}

.dropdown-content .dropdown-item::before {
  content: '';
  position: absolute;
  left: 10px;
  top: 0;
  height: 100%;
  width: 3px;
  background: linear-gradient(to bottom, transparent, rgba(163, 230, 53, 0.3), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.dropdown-content .dropdown-item:hover::before {
  opacity: 1;
}

/* Button styling to match the glossy theme */
.glossy-btn {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.05));
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.glossy-btn:hover {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Custom scrollbar for webkit browsers */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: rgba(17, 24, 39, 0.3);
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgba(163, 230, 53, 0.3);
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(163, 230, 53, 0.5);
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
  background: linear-gradient(180deg, rgba(31, 41, 55, 0.95) 0%, rgba(17, 24, 39, 0.9) 100%);
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
  flex-shrink: 0;
}

/* Scrollable content area */
.overflow-y-auto {
  flex-grow: 1;
  overflow-y: auto;
  scrollbar-color: rgba(163, 230, 53, 0.3) rgba(17, 24, 39, 0.3);
  scrollbar-width: thin;
}

/* Fixed footer styling */
.glossy-footer {
  background: linear-gradient(0deg, rgba(31, 41, 55, 0.95) 0%, rgba(17, 24, 39, 0.9) 100%);
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
  flex-shrink: 0;
}

/* Additional spotlight effect for active page */
.active .nav-link-spotlight::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, transparent, rgba(163, 230, 53, 0.7), transparent);
  opacity: 1;
}

/* Glass effect for cards and content areas */
.glossy-panel {
  background: linear-gradient(145deg, 
    rgba(31, 41, 55, 0.7), 
    rgba(17, 24, 39, 0.6)
  );
  border-radius: 0.5rem;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.05);
  overflow: hidden;
}
/* Your existing styles */
.fc-event {
  cursor: pointer;
  padding: 0 !important;
  border: none !important;
  background: transparent !important;
  font-size: 0.85em; /* Smaller font size for events */
}

/* Make the event cards smaller overall */
.event-card {
  max-height: 2.5rem; /* Reduce the height */
}

.event-card .flex-1 {
  padding: 1px 2px !important; /* Reduce padding */
}

.event-card .w-8 {
  width: 1.5rem !important; /* Make the colored status bar smaller */
}

.event-card .text-xs {
  font-size: 0.7rem; /* Make text smaller */
}

/* List view glassmorphism styling */
.fc .fc-list-event {
  transition: all 0.2s ease;
}

.fc .fc-list-event:hover td {
  background: rgba(83, 65, 92, 0.4) !important; /* Semi-transparent hover color */
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

.dark .fc .fc-list-event:hover td {
  background: rgba(2, 6, 23, 0.7) !important; /* Even darker hover color for dark mode */
}

/* Add a subtle border to list items */
.fc .fc-list-event td {
  border-color: rgba(255, 255, 255, 0.05) !important;
}

/* Style the list event titles with the same styling as pill events */
.fc .fc-list-event-title a {
  color: #f8fafc !important; /* Light text color */
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
  font-weight: 500;
}

.dark .fc .fc-list-event-title a {
  color: #f1f5f9 !important; /* Light text color for dark mode */
}

/* Style the day headers with glassmorphism */
.fc .fc-list-day-cushion {
  background: linear-gradient(90deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.8) 100%) !important;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.05);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.dark .fc .fc-list-day-cushion {
  background: linear-gradient(90deg, rgba(15, 23, 42, 0.9) 0%, rgba(9, 15, 25, 0.8) 100%) !important;
}

/* Add color indicator to list events */
.fc .fc-list-event-dot {
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
}

/* Glassmorphism day grid styling */
/* Day grid background colors with glassmorphism effect */
.fc .fc-daygrid-day {
  background: rgba(20, 27, 42, 0.4); /* Semi-transparent background */
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  transition: background 0.2s ease;
}

/* Current day highlighting with glow effect */
.fc .fc-day-today {
  background: rgba(80, 200, 246, 0.08) !important; 
  box-shadow: inset 0 0 20px rgba(80, 200, 246, 0.1);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

/* Days from other months */
.fc .fc-day-other {
  background: rgba(20, 27, 42, 0.25); /* More transparent for "other" days */
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
}

/* Dark mode overrides with enhanced glassmorphism */
.dark .fc .fc-daygrid-day {
  background: rgba(30, 41, 59, 0.4); /* Dark semi-transparent background */
}

.dark .fc .fc-day-today {
  background: rgba(0, 145, 255, 0.1) !important; /* Darker blue highlight */
  box-shadow: inset 0 0 30px rgba(0, 145, 255, 0.15);
}

.dark .fc .fc-day-other {
  background: rgba(15, 21, 35, 0.3); /* More transparent for "other" days in dark mode */
}

/* Header row with gradient glassmorphism */
.fc .fc-col-header-cell {
  background: linear-gradient(180deg, rgba(20, 27, 42, 0.9), rgba(20, 27, 42, 0.7));
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.dark .fc .fc-col-header-cell {
  background: linear-gradient(180deg, rgba(29, 56, 114, 0.9), rgba(29, 56, 114, 0.7));
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

/* Subtle hover effect for day cells */
.fc .fc-daygrid-day:hover {
  background: rgba(255, 255, 255, 0.03);
}

/* Your other existing styles... */

/* Glass Morphism Styles */
.glass-container {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 16px;
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
  transition: all 0.3s ease;
}

.glass-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.18);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
  transition: all 0.3s ease;
}

.glass-button {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.8);
  transition: all 0.3s ease;
}

.glass-button:hover {
  background: rgba(139, 92, 246, 0.3);
  border-color: rgba(139, 92, 246, 0.5);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  .glass-container {
    background: rgba(30, 30, 30, 0.3);
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.25);
  }
  
  .glass-card {
    background: rgba(30, 30, 30, 0.3);
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.25);
  }
  
  .glass-button {
    background: rgba(30, 30, 30, 0.4);
    border-color: rgba(255, 255, 255, 0.1);
  }
  
  .glass-button:hover {
    background: rgba(139, 92, 246, 0.25);
    border-color: rgba(139, 92, 246, 0.4);
  }
}

/* FullCalendar custom styles to match glass theme */
.fc {
  --fc-page-bg-color: transparent;
  --fc-border-color: rgba(255, 255, 255, 0.1);
  --fc-neutral-bg-color: rgba(255, 255, 255, 0.05);
  --fc-list-event-hover-bg-color: rgba(139, 92, 246, 0.2);
}

.fc .fc-toolbar-title {
  color: rgba(255, 255, 255, 0.9);
}

.fc .fc-button {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.8);
  transition: all 0.3s ease;
}

.fc .fc-button:hover {
  background: rgba(139, 92, 246, 0.3);
  border-color: rgba(139, 92, 246, 0.5);
}

.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active {
  background: rgba(139, 92, 246, 0.4);
  border-color: rgba(139, 92, 246, 0.6);
}

.fc-theme-standard td,
.fc-theme-standard th {
  border-color: rgba(255, 255, 255, 0.1);
}

/* Glassmorphism Tooltip Styles */
.tippy-box[data-theme~='tekdash-glass'] {
  background: rgba(17, 24, 39, 0.85);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5),
              inset 0 0 15px rgba(255, 255, 255, 0.05);
  color: #fff;
  padding: 0;
  overflow: hidden;
}

/* Arrow styling for different placements */
.tippy-box[data-theme~='tekdash-glass'][data-placement^='top'] > .tippy-arrow::before {
  border-top-color: rgba(17, 24, 39, 0.85);
}

.tippy-box[data-theme~='tekdash-glass'][data-placement^='bottom'] > .tippy-arrow::before {
  border-bottom-color: rgba(17, 24, 39, 0.85);
}

.tippy-box[data-theme~='tekdash-glass'][data-placement^='left'] > .tippy-arrow::before {
  border-left-color: rgba(17, 24, 39, 0.85);
}

.tippy-box[data-theme~='tekdash-glass'][data-placement^='right'] > .tippy-arrow::before {
  border-right-color: rgba(17, 24, 39, 0.85);
}

/* Enhanced calendar tooltip structure */
.calendar-tooltip {
  overflow: hidden;
}

.calendar-tooltip .tooltip-header {
  background: linear-gradient(90deg, rgba(55, 65, 81, 0.8) 0%, rgba(31, 41, 55, 0.9) 100%);
  padding: 10px 15px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  position: relative;
}

.calendar-tooltip .tooltip-header:before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
}

.calendar-tooltip .tooltip-title {
  font-size: 16px;
  font-weight: 600;
  color: white;
  margin-bottom: 6px;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.calendar-tooltip .tooltip-status-pill {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 500;
  color: white;
  text-transform: uppercase;
  box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.2),
              0 1px 2px rgba(0, 0, 0, 0.3);
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
  margin-top: 3px;
}

.calendar-tooltip .tooltip-body {
  padding: 12px 15px;
  background: rgba(17, 24, 39, 0.5);
}

.calendar-tooltip .tooltip-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 6px;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.9);
}

.calendar-tooltip .tooltip-label {
  color: rgba(255, 255, 255, 0.6);
  font-weight: 500;
  margin-right: 8px;
}

.calendar-tooltip .tooltip-value {
  color: rgba(255, 255, 255, 0.9);
}

.calendar-tooltip .tooltip-description-wrapper {
  margin: 10px 0;
}

.calendar-tooltip .tooltip-description {
  font-size: 12px;
  margin-top: 5px;
  color: rgba(255, 255, 255, 0.8);
  background: rgba(0, 0, 0, 0.2);
  border-radius: 6px;
  padding: 8px;
  max-height: 100px;
  overflow-y: auto;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

/* Highlight customer information in tooltip */
.calendar-tooltip .tooltip-customer-highlight {
  background: rgba(139, 92, 246, 0.15);
  border-radius: 6px;
  padding: 5px 8px;
  margin: -5px -8px 6px;
  border-left: 2px solid rgba(139, 92, 246, 0.4);
} 
.fc-theme-standard th {
  border-color: rgba(255, 255, 255, 0.1);
}

.fc .fc-daygrid-day-number,
.fc .fc-col-header-cell-cushion {
  color: rgba(255, 255, 255, 0.9);
}

/* Glassmorphism Pill Event Styles */
.glass-event-pill {
  display: flex;
  align-items: center;
  height: 100%;
  width: 100%;
  min-height: 22px;
  border-radius: 20px;
  overflow: hidden;
  background: rgba(30, 41, 59, 0.8);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15),
              inset 0 1px 1px rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.05);
  transition: all 0.2s ease;
}

.glass-event-pill:hover {
  transform: translateY(-1px) scale(1.01);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2),
              inset 0 1px 2px rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.1);
}

.status-indicator {
  width: 6px;
  height: 100%;
  margin-right: 3px;
  box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
}

.event-content {
  flex: 1;
  padding: 2px 6px 2px 4px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  overflow: hidden;
}

.event-title {
  font-size: 11px;
  font-weight: 500;
  color: white;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.event-time {
  font-size: 10px;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 1px;
}

/* If in day or week view, make events taller */
.fc-timeGridDay-view .glass-event-pill,
.fc-timeGridWeek-view .glass-event-pill {
  border-radius: 10px;
  padding: 2px 0;
}

.fc-timeGridDay-view .event-title,
.fc-timeGridWeek-view .event-title {
  font-size: 12px;
}

.fc-timeGridDay-view .event-time,
.fc-timeGridWeek-view .event-time {
  font-size: 11px;
}
</style>