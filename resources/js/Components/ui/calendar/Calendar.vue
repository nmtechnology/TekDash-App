<template>
  <div ref="root" class="calendar">
    <div class="calendar-header">
      <div class="flex items-center justify-between">
        <div
          role="button"
          class="calendar-nav-button"
          @click="onPrevMonthClick"
          :disabled="prevMonthButtonIsDisabled"
          :class="{ 'cursor-not-allowed opacity-50': prevMonthButtonIsDisabled }"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M15.75 19.5L8.25 12l7.5-7.5"
            />
          </svg>
        </div>
        <div class="calendar-title">
          {{ currentMonthName }} {{ currentYear }}
        </div>
        <div
          role="button"
          class="calendar-nav-button"
          @click="onNextMonthClick"
          :disabled="nextMonthButtonIsDisabled"
          :class="{ 'cursor-not-allowed opacity-50': nextMonthButtonIsDisabled }"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M8.25 4.5l7.5 7.5-7.5 7.5"
            />
          </svg>
        </div>
      </div>
    </div>
    <div
      role="grid"
      class="mt-2 grid grid-cols-7 gap-1 text-center text-sm"
    >
      <div
        v-for="day in daysOfWeek"
        :key="day"
        role="columnheader"
        class="calendar-weekday"
      >
        {{ day }}
      </div>
    </div>
    <div
      role="grid"
      class="mt-1 grid grid-cols-7 gap-1 text-center text-sm"
    >
      <template v-for="(date, idx) in calendarDays" :key="idx">
        <div
          role="gridcell"
          class="calendar-cell"
          :class="getCellClasses(date)"
        >
          <div
            role="button"
            class="calendar-day"
            :class="getDayClasses(date)"
            @click="handleDateSelect(date)"
            v-if="date"
          >
            {{ date.getDate() }}
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
  value: {
    type: Date,
    required: false,
    default: null,
  },
  month: {
    type: Date,
    required: false,
    default: () => new Date(),
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
  fromDate: {
    type: Date,
    required: false,
    default: null,
  },
  toDate: {
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

const emit = defineEmits(['update:value']);
const root = ref(null);

const currentMonth = ref(new Date(props.month.getFullYear(), props.month.getMonth(), 1));

const currentMonthName = computed(() => {
  return currentMonth.value.toLocaleString('default', { month: 'long' });
});

const currentYear = computed(() => {
  return currentMonth.value.getFullYear();
});

const daysOfWeek = computed(() => {
  const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  return weekdays;
});

const calendarDays = computed(() => {
  const year = currentMonth.value.getFullYear();
  const month = currentMonth.value.getMonth();
  const firstDayOfMonth = new Date(year, month, 1);
  const lastDayOfMonth = new Date(year, month + 1, 0);
  const daysInMonth = lastDayOfMonth.getDate();
  const startDay = firstDayOfMonth.getDay();
  
  const days = Array(42).fill(null);
  
  // Fill in days from previous month
  const prevMonthLastDay = new Date(year, month, 0).getDate();
  for (let i = 0; i < startDay; i++) {
    days[i] = new Date(year, month - 1, prevMonthLastDay - startDay + i + 1);
  }
  
  // Fill in days of current month
  for (let i = 0; i < daysInMonth; i++) {
    days[startDay + i] = new Date(year, month, i + 1);
  }
  
  // Fill in days from next month
  let nextMonthDay = 1;
  for (let i = startDay + daysInMonth; i < days.length; i++) {
    days[i] = new Date(year, month + 1, nextMonthDay++);
  }
  
  return days;
});

const isDateDisabled = (date) => {
  if (!date) return true;
  
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  if (props.minDate && date < props.minDate) return true;
  if (props.maxDate && date > props.maxDate) return true;
  
  if (props.disabledDates.length > 0) {
    return props.disabledDates.some((disabledDate) => {
      return isSameDay(date, disabledDate);
    });
  }
  
  return false;
};

const isSameDay = (date1, date2) => {
  if (!date1 || !date2) return false;
  
  return (
    date1.getFullYear() === date2.getFullYear() &&
    date1.getMonth() === date2.getMonth() &&
    date1.getDate() === date2.getDate()
  );
};

const isCurrentMonth = (date) => {
  return date && date.getMonth() === currentMonth.value.getMonth();
};

const isToday = (date) => {
  if (!date) return false;
  const today = new Date();
  return (
    date.getDate() === today.getDate() &&
    date.getMonth() === today.getMonth() &&
    date.getFullYear() === today.getFullYear()
  );
};

const isSelected = (date) => {
  if (!date || !props.value) return false;
  return isSameDay(date, props.value);
};

const isInRange = (date) => {
  if (!date || !props.fromDate || !props.toDate) return false;
  
  return date > props.fromDate && date < props.toDate;
};

const isRangeStart = (date) => {
  if (!date || !props.fromDate) return false;
  return isSameDay(date, props.fromDate);
};

const isRangeEnd = (date) => {
  if (!date || !props.toDate) return false;
  return isSameDay(date, props.toDate);
};

const getCellClasses = (date) => {
  return {
    'opacity-50': !isCurrentMonth(date),
  };
};

const getDayClasses = (date) => {
  return {
    'calendar-day-selected': isSelected(date),
    'calendar-day-today': isToday(date),
    'calendar-day-disabled': isDateDisabled(date),
    'calendar-day-range-start': isRangeStart(date),
    'calendar-day-range-end': isRangeEnd(date),
    'calendar-day-in-range': isInRange(date),
  };
};

const handleDateSelect = (date) => {
  if (isDateDisabled(date) || props.disabled) return;
  emit('update:value', date);
};

const onPrevMonthClick = () => {
  if (prevMonthButtonIsDisabled.value) return;
  currentMonth.value = new Date(
    currentMonth.value.getFullYear(),
    currentMonth.value.getMonth() - 1,
    1
  );
};

const onNextMonthClick = () => {
  if (nextMonthButtonIsDisabled.value) return;
  currentMonth.value = new Date(
    currentMonth.value.getFullYear(),
    currentMonth.value.getMonth() + 1,
    1
  );
};

const prevMonthButtonIsDisabled = computed(() => {
  if (!props.minDate) return false;
  
  const prevMonth = new Date(
    currentMonth.value.getFullYear(),
    currentMonth.value.getMonth() - 1,
    1
  );
  return prevMonth < new Date(props.minDate.getFullYear(), props.minDate.getMonth(), 1);
});

const nextMonthButtonIsDisabled = computed(() => {
  if (!props.maxDate) return false;
  
  const nextMonth = new Date(
    currentMonth.value.getFullYear(),
    currentMonth.value.getMonth() + 1,
    1
  );
  return nextMonth > new Date(props.maxDate.getFullYear(), props.maxDate.getMonth(), 1);
});

// Watch for prop changes
watch(() => props.month, (newMonth) => {
  currentMonth.value = new Date(newMonth.getFullYear(), newMonth.getMonth(), 1);
}, { deep: true });

onMounted(() => {
  // If there's a selected date, ensure the calendar shows its month
  if (props.value) {
    currentMonth.value = new Date(props.value.getFullYear(), props.value.getMonth(), 1);
  }
});
</script>

<style scoped>
.calendar {
  @apply p-3 bg-gray-800/70 rounded-lg border border-lime-500/30;
}

.calendar-header {
  @apply flex items-center justify-between mb-2;
}

.calendar-nav-button {
  @apply p-1 rounded-md text-lime-400 hover:text-lime-300 hover:bg-gray-700;
}

.calendar-title {
  @apply font-medium text-lime-400;
}

.calendar-weekday {
  @apply text-xs font-medium text-lime-400/70 p-1;
}

.calendar-cell {
  @apply p-1;
}

.calendar-day {
  @apply flex h-8 w-8 items-center justify-center rounded-md text-center text-sm text-white p-0 hover:bg-lime-600/40 hover:text-lime-100;
}

.calendar-day-today {
  @apply bg-lime-800/50 text-lime-300 font-bold;
}

.calendar-day-selected {
  @apply bg-lime-600 text-white font-bold;
}

.calendar-day-disabled {
  @apply opacity-50 cursor-not-allowed hover:bg-transparent hover:text-white;
}

.calendar-day-in-range {
  @apply bg-lime-600/30 rounded-none text-lime-100;
}

.calendar-day-range-start {
  @apply bg-lime-600 text-white rounded-l-md rounded-r-none;
}

.calendar-day-range-end {
  @apply bg-lime-600 text-white rounded-r-md rounded-l-none;
}
</style>
