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

// Define the event handlers
function handleDateClick(arg) {
  const clickedDate = arg.dateStr;
  const workOrder = calendarOptions.events.find(event => event.start === clickedDate);
  if (workOrder) {
    alert(`Work Order: ${workOrder.title}\nDescription: ${workOrder.description}`);
  } else {
    alert('No work order on this date.');
  }
}

function handleEventClick(arg) {
  alert(`Event: ${arg.event.title}`);
}

function handleEventDrop(arg) {
  alert(`Event dropped: ${arg.event.title}`);
}

function handleEventResize(arg) {
  alert(`Event resized: ${arg.event.title}`);
}

function handleEventAdd(arg) {
  alert(`Event added: ${arg.event.title}`);
}

function handleEventChange(arg) {
  alert(`Event changed: ${arg.event.title}`);
}

function handleEventRemove(arg) {
  alert(`Event removed: ${arg.event.title}`);
}

function handleEventContent(arg) {
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

// Fetch events from the backend
async function fetchEvents() {
  try {
    const response = await axios.get('/api/work-orders'); // Use GET method to fetch work orders
    console.log('API response:', response.data); // Debugging line

    // Check if the response is HTML
    if (typeof response.data === 'string' && response.data.includes('<!DOCTYPE html>')) {
      console.error('Received HTML response instead of JSON. Please check the API endpoint.');
      return;
    }

    const workOrders = response.data.workOrders || response.data; // Adjust based on actual API response structure

    if (workOrders && Array.isArray(workOrders)) {
      // Format work orders as events
      const events = workOrders.map(workOrder => ({
        title: workOrder.title,
        start: workOrder.date_time,
        description: workOrder.description
      }));

      // Update the calendar options with the fetched events
      calendarOptions.events = events;
      console.log('Formatted events:', events); // Debugging line
    } else {
      console.error('Invalid work orders data:', workOrders);
    }
  } catch (error) {
    console.error('Error fetching work orders:', error);
    if (error.response && error.response.data) {
      console.error('Validation errors:', error.response.data.errors);
      // Handle validation errors if needed
    }
  }
}

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