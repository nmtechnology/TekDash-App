<template>
    <div>
      <p class="text-purple-400 text-sm mt-2">{{ formattedDateTime }}</p>
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