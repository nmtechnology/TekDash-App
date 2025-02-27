<template>
  <div class="digital-clock mb-10">
    <p class="time">{{ formattedDateTime }}</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const currentTime = ref(new Date());
let timer = null;

const formattedDateTime = computed(() => {
  return new Intl.DateTimeFormat('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: 'numeric',
    second: 'numeric',
    hour12: true
  }).format(currentTime.value);
});

const updateTime = () => {
  currentTime.value = new Date();
};

onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 1000);
});

onBeforeUnmount(() => {
  if (timer) clearInterval(timer);
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');

.digital-clock {
  font-family: "Press Start 2P", system-ui;
  color: #ffc902;
}

.time {
  font-size: 2rem;
  margin-top: 2rem;
  pointer-events: none; /* Disable pointer events */
}

@keyframes blink {
  50% {
    opacity: 0;
  }
}

/* Responsive font sizes */
@media (max-width: 1200px) {
  .time {
    font-size: 1.75rem;
  }
}

@media (max-width: 992px) {
  .time {
    font-size: 1.5rem;
  }
}

@media (max-width: 768px) {
  .time {
    font-size: 1.25rem;
  }
}

@media (max-width: 576px) {
  .time {
    font-size: 1rem;
  }
}
</style>