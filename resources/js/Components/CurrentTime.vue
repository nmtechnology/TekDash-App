<template>
  <div class="digital-clock">
    <p class="time">{{ formattedDateTime }}</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const currentTime = ref(new Date());
let timer = null;

const formattedDateTime = computed(() => {
  return new Intl.DateTimeFormat('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
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
@import url('https://fonts.googleapis.com/css2?family=Digital-7+Mono&display=swap');

.digital-clock {
  font-family: 'Digital-7 Mono', monospace;
  color: #00ff00;
  text-shadow: 0 0 10px #00ff00;
}

.time {
  font-size: 2rem;
  margin-top: 2rem;
  pointer-events: none; /* Disable pointer events */
}

.time::after {
  content: '';
  display: inline-block;
  width: 0.1em;
  height: 1em;
  background: #00ff00;
  margin-left: 0.1em;
  animation: blink 1s steps(1) infinite;
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