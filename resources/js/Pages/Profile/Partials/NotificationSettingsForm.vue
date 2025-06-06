<template>
  <div>
    <h3 class="text-lg font-medium text-gray-900">Notification Settings</h3>
    <p class="mt-1 text-sm text-gray-600">Manage your notification preferences.</p>

    <form @submit.prevent="updateSettings" class="mt-4 space-y-4">
      <div>
        <label class="flex items-center">
          <input type="checkbox" v-model="settings.workOrderUpdates" class="form-checkbox">
          <span class="ml-2 text-sm text-gray-600">Work Order Updates</span>
        </label>
      </div>

      <div>
        <label class="flex items-center">
          <input type="checkbox" v-model="settings.customerAdditions" class="form-checkbox">
          <span class="ml-2 text-sm text-gray-600">Customer Additions</span>
        </label>
      </div>

      <div>
        <label class="flex items-center">
          <input type="checkbox" v-model="settings.technicianAdditions" class="form-checkbox">
          <span class="ml-2 text-sm text-gray-600">Technician Additions</span>
        </label>
      </div>

      <div class="flex justify-end">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const settings = ref({
  workOrderUpdates: true,
  customerAdditions: true,
  technicianAdditions: true,
});

function updateSettings() {
  // Logic to save notification settings (e.g., API call)
  console.log('Notification settings updated:', settings.value);
}
</script>

<style scoped>
/* Add any custom styles here */
</style>
