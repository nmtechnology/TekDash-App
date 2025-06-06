<template>
  <div>
    <button
      @click="showModal = true"
      class="btn flex items-center gap-2 px-4 py-2 font-bold text-sm text-purple-400 transition-all duration-300"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Add Customer
    </button>

    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full">
        <h3 class="text-xl font-bold mb-4">Add New Customer</h3>

        <form @submit.prevent="addCustomer">
          <div class="mb-4">
            <label for="business_name" class="block text-sm font-medium text-gray-700">Business Name</label>
            <input
              v-model="form.business_name"
              type="text"
              id="business_name"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
              required
            />
          </div>

          <div class="mb-4">
            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
            <input
              v-model="form.address"
              type="text"
              id="address"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
              required
            />
          </div>

          <div class="mb-4">
            <label for="poc_name" class="block text-sm font-medium text-gray-700">POC Name</label>
            <input
              v-model="form.poc_name"
              type="text"
              id="poc_name"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
              required
            />
          </div>

          <div class="mb-4">
            <label for="poc_email" class="block text-sm font-medium text-gray-700">POC Email</label>
            <input
              v-model="form.poc_email"
              type="email"
              id="poc_email"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
              required
            />
          </div>

          <div class="mb-4">
            <label for="fax" class="block text-sm font-medium text-gray-700">Fax</label>
            <input
              v-model="form.fax"
              type="text"
              id="fax"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
          </div>

          <div class="mb-4">
            <label for="net_terms" class="block text-sm font-medium text-gray-700">Net Terms</label>
            <select
              v-model="form.net_terms"
              id="net_terms"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
              required
            >
              <option value="Net 7">Net 7</option>
              <option value="Net 15">Net 15</option>
              <option value="Net 30">Net 30</option>
              <option value="Net 60">Net 60</option>
            </select>
          </div>

          <div class="mb-4">
            <label for="attachable_files" class="block text-sm font-medium text-gray-700">Attachable Files</label>
            <input
              type="file"
              id="attachable_files"
              multiple
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
              @change="handleFileChange"
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
              Add Customer
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
  business_name: '',
  address: '',
  poc_name: '',
  poc_email: '',
  fax: '',
  net_terms: 'Net 30',
  attachable_files: null,
});

function addCustomer() {
  // Logic to add customer (e.g., API call)
  console.log('Customer added:', form.value);
  showModal.value = false;
  form.value = {
    business_name: '',
    address: '',
    poc_name: '',
    poc_email: '',
    fax: '',
    net_terms: 'Net 30',
    attachable_files: null,
  };
}

function handleFileChange(event) {
  form.value.attachable_files = Array.from(event.target.files);
}
</script>

<style scoped>
/* Add any custom styles here */
</style>
