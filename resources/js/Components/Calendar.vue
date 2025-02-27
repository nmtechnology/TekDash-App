<template>
  <div>
    <button @click="toggleWeekends" class="mb-4 px-4 py-2 outline text-lime-400 rounded-md hover:bg-lime-500 dark:hover:bg-lime-400 hover:text-gray-900 transition">
      Toggle Weekends
    </button>
    <div class="bg-gray-900 outline p-4 rounded-lg shadow opacity-75">
      <FullCalendar :options="calendarOptions">
        <!-- Custom event rendering as cards -->
        <template v-slot:eventContent="arg">
          <div class="w-full flex rounded-md shadow-sm event-card overflow-hidden">
            <!-- Left colored section with status indicator -->
            <div 
  :style="{ 
    backgroundColor: getStatusColor(arg.event.extendedProps.status),
    color: getTextColorForStatus(arg.event.extendedProps.status)
  }" 
  class="flex w-8 shrink-0 items-center justify-center rounded-l-md text-xs font-bold border-r border-white/20"
  :title="arg.event.extendedProps.status"
>
  {{ getStatusText(arg.event.extendedProps.status) }}
</div>
            
            <!-- Right content section -->
            <div class="flex flex-1 items-center justify-between truncate rounded-r-md bg-white/10 dark:bg-gray-700/40 backdrop-blur-sm">
              <div class="flex-1 truncate px-2 py-1 text-xs">
                <p class="font-medium text-gray-800 dark:text-white truncate">{{ arg.event.title }}</p>
                <p class="text-gray-600 dark:text-gray-300 truncate text-xs">{{ formatEventTime(arg.event) }}</p>
              </div>
            </div>
          </div>
        </template>
      </FullCalendar>
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
import { usePage } from '@inertiajs/inertia-vue3';

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
    timeString += ' - ' + end.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
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
      // Format work orders as events
      const events = data.map((workOrder) => ({
        id: workOrder.id,
        title: workOrder.title,
        start: workOrder.start, // This comes from date_time in the backend
        description: workOrder.description || '',
        status: workOrder.status,
        user_id: workOrder.user_id,
        customer_id: workOrder.customer_id,
        backgroundColor: getStatusColor(workOrder.status), // Add color based on status
        borderColor: getStatusColor(workOrder.status),
        extendedProps: {
          description: workOrder.description || '',
          status: workOrder.status,
          customer_id: workOrder.customer_id
        }
      }));

      // Update the calendar options with the fetched events
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
      return '#4ade80'; // green
    case 'scheduled':
      return '#3b82f6'; // blue
    case 'in progress':
      return '#f59e0b'; // amber
    case 'cancelled':
      return '#ef4444'; // red
    case 'part/return':
      return '#8b5cf6'; // purple
    default:
      return '#64748b'; // slate
  }
}

// Define the event handlers
function handleDateClick(arg: any) {
  const clickedDate = arg.dateStr;
  // You can implement date click handling here
}

// Update the handleEventClick function to emit event with the work order ID
function handleEventClick(arg: any) {
  // Get the work order ID from the clicked event
  const workOrderId = arg.event.id;
  
  // Emit an event to the parent component with the work order ID
  emit('workOrderSelected', workOrderId);
  
  // For debugging
  console.log('Event clicked, opening work order:', workOrderId);
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
/* Your existing styles */
.fc-event {
  cursor: pointer;
  padding: 0 !important;
  border: none !important;
  background: transparent !important;
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
  background-color: rgba(59, 130, 246, 0.2) !important; /* Darker blue highlight */
}

.dark .fc .fc-day-other {
  background-color: #141b2a; /* Even darker for "other" days in dark mode */
}

/* Optional: customize the header row background */
.fc .fc-col-header-cell {
  background-color: #141b2a; /* Light header background */
}

.dark .fc .fc-col-header-cell {
  background-color: #141b2a; /* Dark header background */
}

/* Your other existing styles... */
</style>