<template>
  <div>
    <button @click="toggleWeekends">Toggle Weekends</button>
    <FullCalendar :options="calendarOptions">
      <template v-slot:eventContent="arg">
        <b>{{ arg.event.title }}</b>
        <b>{{ arg.event.extendedProps.description }}</b>
      </template>
    </FullCalendar>
  </div>
</template>

<script setup lang="ts">
import { reactive, onMounted } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import resourceTimelinePlugin from '@fullcalendar/resource-timeline';
import axios from 'axios';

// Setup axios to include CSRF token
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = document.head.querySelector('meta[name="csrf-token"]') as HTMLMetaElement;
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
  console.error('CSRF token not found');
}

// Rest of your code remains the same...

// Fetch events from the backend
async function fetchEvents() {
  try {
    // Changed the API endpoint to include the full URL
    const response = await axios.get('/api/work-orders', {
      headers: {
        'Accept': 'application/json'  // Explicitly request JSON
      }
    });
    
    console.log('API response:', response.data); // Debugging line

    if (response.data && Array.isArray(response.data)) {
      // Format work orders as events
      const events = response.data.map(workOrder => ({
        id: workOrder.id,
        title: workOrder.title,
        start: workOrder.start, // This already comes from date_time in the backend
        description: workOrder.description,
        status: workOrder.status,
        user_id: workOrder.user_id,
        customer_id: workOrder.customer_id
      }));

      // Update the calendar options with the fetched events
      calendarOptions.events = events;
      console.log('Formatted events:', events); // Debugging line
    } else if (response.data && response.data.workOrders && Array.isArray(response.data.workOrders)) {
      // Alternative structure
      const events = response.data.workOrders.map((workOrder: any) => ({
        id: workOrder.id,
        title: workOrder.title,
        start: workOrder.date_time || workOrder.start,
        description: workOrder.description,
        status: workOrder.status,
        user_id: workOrder.user_id,
        customer_id: workOrder.customer_id
      }));

      // Update the calendar options with the fetched events
      calendarOptions.events = events;
    } else {
      console.error('Invalid work orders data:', response.data);
    }
  } catch (error) {
    console.error('Error fetching work orders:', error);
    if (error.response) {
      console.error('Response status:', error.response.status);
      console.error('Response data:', error.response.data);
    }
  }
}

// Define the event handlers
function handleDateClick(arg: any) {
  const clickedDate = arg.dateStr;
  const workOrder = calendarOptions.events.find(event => event.start === clickedDate);
  if (workOrder) {
    alert(`Work Order: ${workOrder.title}\nDescription: ${workOrder.description}`);
  } else {
    alert('No work order on this date.');
  }
}

function handleEventClick(arg: any) {
  alert(`Event: ${arg.event.title}`);
}

function handleEventDrop(arg: any) {
  alert(`Event dropped: ${arg.event.title}`);
}

function handleEventResize(arg: any) {
  alert(`Event resized: ${arg.event.title}`);
}

function handleEventAdd(arg: any) {
  alert(`Event added: ${arg.event.title}`);
}

function handleEventChange(arg: any) {
  alert(`Event changed: ${arg.event.title}`);
}

function handleEventRemove(arg: any) {
  alert(`Event removed: ${arg.event.title}`);
}

function handleEventContent(arg: any) {
  return {
    html: `<b>${arg.event.title}</b><br><b>${arg.event.extendedProps.description}</b>`
  };
}

// Define the calendar options
const calendarOptions = reactive({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin, resourceTimelinePlugin],
  initialView: 'resourceTimelineMonth',
  editable: true,
  headerToolbar: {
    left: 'promptResource prev,next today',
    center: 'title',
    right: 'resourceMonth,dayGridMonth,timeGridWeek,timeGridDay,listMonth'
  },
  views: {
    resourceMonth: {
      type: 'resourceTimelineMonth',
      buttonText: 'personnel'
    }
  },
  customButtons: {
    promptResource: {
      text: '+ personnel',
      click: function() {
        var title = prompt('Name');
        if (title) {
          calendarOptions.resources.push({ title });
        }
      }
    }
  },
  dateClick: handleDateClick,
  events: [],
  eventClick: handleEventClick,
  eventDrop: handleEventDrop,
  eventResize: handleEventResize,
  eventAdd: handleEventAdd,
  eventChange: handleEventChange,
  eventRemove: handleEventRemove,
  eventContent: handleEventContent,
  weekends: true,
  resources: [],
  schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives'
});

// Removed duplicate fetchEvents function

// Fetch events when the component is mounted
onMounted(() => {
  fetchEvents();
});

// Toggle weekends visibility
function toggleWeekends() {
  calendarOptions.weekends = !calendarOptions.weekends;
}
</script>

<style scoped>
/* Add any styles you need here */
</style>