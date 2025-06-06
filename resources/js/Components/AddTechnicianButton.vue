<template>
  <div>
    <button
      @click="showModal = true"
      class="btn flex items-center gap-2 px-4 py-2 font-bold text-sm text-green-400 transition-all duration-300"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Add Technician
    </button>

    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full">
        <h3 class="text-xl font-bold mb-4">Add New Technician</h3>

        <form @submit.prevent="addTechnician">
          <div class="mb-4">
            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
            <input
              v-model="form.first_name"
              type="text"
              id="first_name"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
              required
            />
          </div>

          <div class="mb-4">
            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
            <input
              v-model="form.last_name"
              type="text"
              id="last_name"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
              required
            />
          </div>

          <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
              v-model="form.email"
              type="email"
              id="email"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
              required
            />
          </div>

          <div class="mb-4">
            <label for="pay_rate" class="block text-sm font-medium text-gray-700">Pay Rate</label>
            <input
              v-model="form.pay_rate"
              type="number"
              step="0.01"
              id="pay_rate"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
              required
            />
          </div>

          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Add Technician
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const showModal = ref(false);
const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  pay_rate: '',
});

function addTechnician() {
  // Logic to add technician (e.g., API call)
  console.log('Technician added:', form.value);
  showModal.value = false;
  form.value = {
    first_name: '',
    last_name: '',
    email: '',
    pay_rate: '',
  };
}
</script>

<style scoped>
/* Add any custom styles here */
</style>
