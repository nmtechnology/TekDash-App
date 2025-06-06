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
        <AddWorkorder class="px-3 py-1 rounded" />
      </div>
    </div>
    <div class="glass-card p-4 rounded-lg">
      <FullCalendar :options="calendarOptions">
        <!-- Custom event rendering as cards -->
        <template v-slot:eventContent="arg">
          <div class="w-full flex rounded-md shadow-sm event-card overflow-hidden glossy-content">
            <!-- Left colored section with status indicator -->
            <div 
  :style="{ 
    backgroundColor: getStatusColor(arg.event.extendedProps.status),
    color: getTextColorForStatus(arg.event.extendedProps.status)
  }" 
  class="flex w-8 shrink-0 items-center justify-center rounded-l-md text-xs font-bold border-r border-white/20 glossy-btn"
  :title="arg.event.extendedProps.status"
>
  {{ getStatusText(arg.event.extendedProps.status) }}
</div>
            
            <!-- Right content section -->
            <div class="glossy-btn flex flex-1 items-center justify-between truncate rounded-r-md bg-white/10 dark:bg-gray-700/40 backdrop-blur-sm">
              <div class="flex-1 truncate px-1 py-1 text-xs">
                <p class="font-medium text-gray-800 dark:text-white truncate">
                  {{ arg.event.title }}
                  <span v-if="arg.event.extendedProps.isMultiDayEvent && arg.event.extendedProps.visitNumber" 
                        class="ml-1 text-xs text-gray-400 dark:text-gray-300">
                    ({{ arg.event.extendedProps.visitNumber }}/{{ arg.event.extendedProps.totalVisits }})
                  </span>
                </p>
                <p class="text-gray-600 dark:text-gray-300 truncate text-xs">{{ formatEventTime(arg.event) }}</p>
              </div>
            </div>
          </div>
        </template>
      </FullCalendar>
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
                
                <div v-if="selectedWorkOrder.customer_id || selectedWorkOrder.customer_name" class="mt-4">
                  <p class="text-sm text-gray-400">Customer:</p>
                  <p v-if="selectedWorkOrder.customer_name">{{ selectedWorkOrder.customer_name }}</p>
                  <p v-if="selectedWorkOrder.customer_id" class="text-sm">ID: {{ selectedWorkOrder.customer_id }}</p>
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
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted, defineEmits } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import resourceTimelinePlugin from '@fullcalendar/resource-timeline';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import AddWorkorder from '@/Pages/WorkOrders/AddWorkOrder.vue';

// Define the emits for component communication
const emit = defineEmits(['workOrderSelected']);

// Get CSRF token from Inertia page props
const page = usePage();
const csrf = (page.props as any).csrf || '';

// Setup axios to include CSRF token
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true; // Include cookies with requests

const token = document.head.querySelector('meta[name="csrf-token"]') as HTMLMetaElement;
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
  console.error('CSRF token not found');
}

// Add this function to format the time
function formatEventTime(event: any): string {
  if (!event.start) return '';
  
  const start = new Date(event.start);
  let timeString = start.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
  
  if (event.end) {
    const end = new Date(event.end);
    if (event.extendedProps.isMultiDayEvent) {
      // Format for multi-day events
      const startDate = start.toLocaleDateString([], { month: 'short', day: 'numeric' });
      const endDate = end.toLocaleDateString([], { month: 'short', day: 'numeric' });
      timeString = `${startDate} - ${endDate}`;
    } else {
      // Same day event with end time
      timeString += ' - ' + end.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    }
  }
  
  // If it's a multi-visit event without end time, show visit number
  if (event.extendedProps.isMultiDayEvent && event.extendedProps.visitNumber && !event.end) {
    const date = start.toLocaleDateString([], { month: 'short', day: 'numeric' });
    timeString = `${date} ${timeString}`;
  }
  
  return timeString;
}

// Add the CSRF token using Inertia's page props first, then try meta tag
if (csrf) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
} else {
  // Fallback to meta tag
  const token = document.head.querySelector('meta[name="csrf-token"]');
  if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = (token as HTMLMetaElement).content;
  } else {
    console.warn('CSRF token not found! This could cause CSRF protection issues.');
  }
}

// Track loading state
const isLoading = ref(true);

// Define selected work order
const selectedWorkOrder = ref(null);

// Define showWorkOrderModal
const showWorkOrderModal = ref(false);

async function openWorkOrderModal(workOrderId) {
  try {
    console.log('Opening work order with ID:', workOrderId);
    
    const response = await axios.get(`/work-orders/${workOrderId}/details`, {
      headers: {
        'Accept': 'application/json'
      }
    });
    
    selectedWorkOrder.value = response.data;
    showWorkOrderModal.value = true;
  } catch (error) {
    console.error('Error loading work order:', error);
    
    if (error.response) {
      if (error.response.status === 404) {
        alert('This work order no longer exists. It may have been deleted.');
      } else {
        alert(`Error: ${error.response.data.message || 'Something went wrong'}`);
      }
    } else {
      alert('Network error. Please check your connection and try again.');
    }
  }
}

// Use the web route instead of the API route
// Update the fetchEvents function to handle multiple dates and date ranges
async function fetchEvents() {
  try {
    isLoading.value = true;
    
    // Use the web route instead of the API route
    const response = await fetch('/calendar-data', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    console.log('Calendar events response:', data);

    if (data && Array.isArray(data)) {
      // Process work orders into calendar events
      const events = [];
      
      data.forEach((workOrder) => {
        // Check if we have visit_dates array for multiple dates
        if (workOrder.visit_dates && Array.isArray(workOrder.visit_dates) && workOrder.visit_dates.length > 0) {
          // Create separate event for each visit date
          workOrder.visit_dates.forEach((visitDate, index) => {
            events.push({
              id: workOrder.id,
              title: workOrder.title,
              start: visitDate,
              description: workOrder.description || '',
              status: workOrder.status,
              user_id: workOrder.user_id,
              customer_id: workOrder.customer_id,
              backgroundColor: getStatusColor(workOrder.status),
              borderColor: getStatusColor(workOrder.status),
              extendedProps: {
                description: workOrder.description || '',
                status: workOrder.status,
                customer_id: workOrder.customer_id,
                isMultiDayEvent: workOrder.visit_dates.length > 1,
                visitNumber: index + 1,
                totalVisits: workOrder.visit_dates.length
              }
            });
          });
        }
        // Fall back to regular start date if no visit_dates
        else {
          events.push({
            id: workOrder.id,
            title: workOrder.title,
            start: workOrder.start || workOrder.date_time,
            end: workOrder.end || workOrder.end_date,
            description: workOrder.description || '',
            status: workOrder.status,
            user_id: workOrder.user_id,
            customer_id: workOrder.customer_id,
            backgroundColor: getStatusColor(workOrder.status),
            borderColor: getStatusColor(workOrder.status),
            extendedProps: {
              description: workOrder.description || '',
              status: workOrder.status,
              customer_id: workOrder.customer_id,
              isMultiDayEvent: workOrder.end_date ? true : false
            }
          });
        }
      });

      // Update the calendar options with the processed events
      calendarOptions.events = events;
    }
  } catch (error) {
    console.error('Error fetching work orders for calendar:', error);
    calendarOptions.events = [];
  } finally {
    isLoading.value = false;
  }
}

// Function to get text color based on background color for optimal contrast
function getTextColorForStatus(status: string): string {
  // Dark backgrounds need white text, light backgrounds need dark text
  switch (status?.toLowerCase()) {
    case 'cancelled':
    case 'complete':
    case 'in progress':
      return 'white'; // For darker backgrounds
    default:
      return '#1a202c'; // Dark text for lighter backgrounds
  }
}

// Function to get status text for display
function getStatusText(status: string): string {
  if (!status) return 'N/A';
  
  // Format the status text (capitalize first letter or use abbreviations)
  switch (status.toLowerCase()) {
    case 'complete':
      return 'C';
    case 'scheduled':
      return 'S';
    case 'in progress':
      return 'IP';
    case 'cancelled':
      return 'X';
    case 'part/return':
      return 'PR';
    default:
      return status.charAt(0).toUpperCase();
  }
}

// Function to get color based on work order status
function getStatusColor(status: string): string {
  switch (status?.toLowerCase()) {
    case 'complete':
      return '#279c54'; // green
    case 'scheduled':
      return '#223694'; // blue
    case 'in progress':
      return '#b59023'; // amber
    case 'cancelled':
      return '#ef4444'; // red
    case 'part/return':
      return '#844ac4'; // purple
    default:
      return '#844ac4'; // slate
  }
}

// Define the event handlers
function handleDateClick(arg: any) {
  const clickedDate = arg.dateStr;
  // You can implement date click handling here
}

// Update the handleEventClick function to directly open the work order modal
function handleEventClick(arg: any) {
  // Get the work order ID from the clicked event
  const workOrderId = arg.event.id;
  
  // Open the work order modal directly
  console.log('Event clicked, opening work order:', workOrderId);
  openWorkOrderModal(workOrderId);
}

// Define the calendar options
const calendarOptions = reactive({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin, resourceTimelinePlugin],
  initialView: 'dayGridMonth',
  editable: true,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
  },
  dateClick: handleDateClick,
  events: [], // Will be populated by fetchEvents
  eventClick: handleEventClick,
  weekends: true,
  height: 'auto',
  themeSystem: 'standard',
  eventTimeFormat: {
    hour: 'numeric',
    minute: '2-digit',
    meridiem: 'short' as 'short'
  }
});

// Fetch events when the component is mounted
onMounted(() => {
  fetchEvents();
});

// Toggle weekends visibility
function toggleWeekends() {
  calendarOptions.weekends = !calendarOptions.weekends;
}
</script>

<style>
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

/* Add these new styles for list view hover colors */
.fc .fc-list-event:hover td {
  background-color: #53415c !important; /* Dark hover color */
}

.dark .fc .fc-list-event:hover td {
  background-color: #020617 !important; /* Even darker hover color for dark mode */
}

/* Optional: Style the list event titles and headers */
.fc .fc-list-event-title a {
  color: #f8fafc !important; /* Light text color */
}

.dark .fc .fc-list-event-title a {
  color: #f1f5f9 !important; /* Light text color for dark mode */
}

.fc .fc-list-day-cushion {
  background-color: #1e293b !important; /* Header background color */
}

.dark .fc .fc-list-day-cushion {
  background-color: #0f172a !important; /* Darker header background for dark mode */
}

/* Add these new styles for day grid customization */
/* Day grid background colors */
.fc .fc-daygrid-day {
  background-color: #141b2a; /* Light theme background */
}

/* Current day highlighting */
.fc .fc-day-today {
  background-color: rgba(80, 200, 246, 0.1) !important; /* Light blue highlight */
}

/* Days from other months */
.fc .fc-day-other {
  background-color: #141b2a; /* Slightly darker for "other" days */
}

/* Dark mode overrides */
.dark .fc .fc-daygrid-day {
  background-color: #1e293b; /* Dark theme background */
}

.dark .fc .fc-day-today {
  background-color: #0091ff6f !important; /* Darker blue highlight */
}

.dark .fc .fc-day-other {
  background-color: #0f1523; /* Even darker for "other" days in dark mode */
}

/* Optional: customize the header row background */
.fc .fc-col-header-cell {
  background-color: #141b2a; /* Light header background */
}

.dark .fc .fc-col-header-cell {
  background-color: #1d3872; /* Dark header background */
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

.fc .fc-daygrid-day-number,
.fc .fc-col-header-cell-cushion {
  color: rgba(255, 255, 255, 0.9);
}

/* Event card specific styles */
.event-card {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  transition: transform 0.2s ease;
}

.event-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
</style>