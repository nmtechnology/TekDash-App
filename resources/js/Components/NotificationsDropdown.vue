<template>
  <div class="dropdown dropdown-end">
    <label tabindex="0" class="btn btn-ghost btn-circle">
      <div class="indicator">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span v-if="unreadCount > 0" class="badge badge-sm indicator-item">{{ unreadCount }}</span>
      </div>
    </label>
    <div tabindex="0" class="mt-3 card card-compact dropdown-content w-52 bg-base-100 shadow">
      <div class="card-body">
        <span class="font-bold text-lg">{{ unreadCount }} Notifications</span>
        <ul>
          <li v-for="notification in notifications" :key="notification.id" class="text-sm">
            {{ notification.data.message }}
          </li>
        </ul>
        <div v-if="notifications.length === 0" class="text-sm text-gray-500">No notifications</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const notifications = ref([
  // Example notifications
  { id: 1, data: { message: 'Work order #123 updated' }, created_at: '2025-06-05 10:00:00' },
  { id: 2, data: { message: 'New customer added: Acme Corp' }, created_at: '2025-06-05 09:30:00' },
]);
const unreadCount = ref(notifications.value.length);
</script>

<style scoped>
/* Add any custom styles here */
</style>
