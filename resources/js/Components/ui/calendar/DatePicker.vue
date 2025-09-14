<template>
  <div class="date-picker-container">
    <Calendar
      v-model="selectedDate"
      :month="selectedDate || new Date()"
      :min-date="minDate"
      :max-date="maxDate"
      :disabled="disabled"
      :disabled-dates="disabledDates"
    />
    <div v-if="withTime" class="time-picker mt-4">
      <div class="flex items-center justify-between">
        <label class="text-sm font-medium text-lime-400">Time</label>
      </div>
      <div class="flex items-center gap-2 mt-2">
        <select
          v-model="selectedHour"
          class="glossy-content text-white rounded-md border border-lime-400/50 px-2 py-1"
          :disabled="disabled"
        >
          <option v-for="hour in hours" :key="`hour-${hour}`" :value="hour">
            {{ formatHour(hour) }}
          </option>
        </select>
        <span class="text-lime-400 font-bold">:</span>
        <select
          v-model="selectedMinute"
          class="glossy-content text-white rounded-md border border-lime-400/50 px-2 py-1"
          :disabled="disabled"
        >
          <option v-for="minute in minutes" :key="`minute-${minute}`" :value="minute">
            {{ padZero(minute) }}
          </option>
        </select>
        <select
          v-if="use12Hours"
          v-model="selectedAmPm"
          class="glossy-content text-white rounded-md border border-lime-400/50 px-2 py-1"
          :disabled="disabled"
        >
          <option value="AM">AM</option>
          <option value="PM">PM</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import Calendar from './Calendar.vue';

const props = defineProps({
  modelValue: {
    type: Date,
    required: false,
    default: null,
  },
  withTime: {
    type: Boolean,
    default: true,
  },
  use12Hours: {
    type: Boolean,
    default: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  minDate: {
    type: Date,
    required: false,
    default: null,
  },
  maxDate: {
    type: Date,
    required: false,
    default: null,
  },
  disabledDates: {
    type: Array,
    required: false,
    default: () => [],
  },
});

const emit = defineEmits(['update:modelValue']);

const selectedDate = ref(props.modelValue ? new Date(props.modelValue) : null);
const selectedHour = ref(props.modelValue ? (props.use12Hours ? convert24To12(props.modelValue.getHours()).hour : props.modelValue.getHours()) : 9);
const selectedMinute = ref(props.modelValue ? props.modelValue.getMinutes() : 0);
const selectedAmPm = ref(props.modelValue ? (props.modelValue.getHours() >= 12 ? 'PM' : 'AM') : 'AM');

// Generate hours options
const hours = computed(() => {
  if (props.use12Hours) {
    return Array.from({ length: 12 }, (_, i) => i === 0 ? 12 : i);
  } else {
    return Array.from({ length: 24 }, (_, i) => i);
  }
});

// Generate minutes options (0, 5, 10, 15, etc.)
const minutes = computed(() => {
  return Array.from({ length: 12 }, (_, i) => i * 5);
});

// Format hour with leading zero if needed
const formatHour = (hour) => {
  return props.use12Hours ? hour.toString() : padZero(hour);
};

// Add leading zero to single digit numbers
const padZero = (num) => {
  return num < 10 ? `0${num}` : num.toString();
};

// Convert 24-hour format to 12-hour format
function convert24To12(hour24) {
  const hour12 = hour24 % 12 || 12;
  const amPm = hour24 >= 12 ? 'PM' : 'AM';
  return { hour: hour12, amPm };
}

// Convert 12-hour format to 24-hour format
function convert12To24(hour12, amPm) {
  if (amPm === 'PM' && hour12 < 12) {
    return hour12 + 12;
  } else if (amPm === 'AM' && hour12 === 12) {
    return 0;
  } else {
    return hour12;
  }
}

// Update date and time when individual parts change
watch([selectedDate, selectedHour, selectedMinute, selectedAmPm], () => {
  if (!selectedDate.value) return;
  
  const newDate = new Date(selectedDate.value);
  
  if (props.withTime) {
    let hours = selectedHour.value;
    if (props.use12Hours) {
      hours = convert12To24(selectedHour.value, selectedAmPm.value);
    }
    newDate.setHours(hours, selectedMinute.value, 0, 0);
  } else {
    newDate.setHours(0, 0, 0, 0);
  }
  
  emit('update:modelValue', newDate);
}, { deep: true });

// Update local values when modelValue prop changes
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    selectedDate.value = new Date(newValue);
    if (props.withTime) {
      if (props.use12Hours) {
        const { hour, amPm } = convert24To12(newValue.getHours());
        selectedHour.value = hour;
        selectedAmPm.value = amPm;
      } else {
        selectedHour.value = newValue.getHours();
      }
      selectedMinute.value = Math.floor(newValue.getMinutes() / 5) * 5;
    }
  } else {
    selectedDate.value = null;
  }
}, { deep: true });
</script>

<style scoped>
.date-picker-container {
  @apply flex flex-col;
}

.time-picker select {
  @apply bg-transparent text-center;
}
</style>
