<template>
  <div>
    <button
      @click="showModal = true"
      class="btn flex items-center gap-2 px-4 py-2 font-bold text-sm text-purple-400 transition-all duration-300"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Customer
    </button>

    <!-- Portal target to render modal at root level -->
    <div v-if="showModal" class="fixed inset-0 z-[9] flex items-start justify-center pt-24">
      <!-- Backdrop with higher opacity for better contrast -->
      <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" @click="showModal = false"></div>
      
      <!-- Modal content -->
      <div class="relative z-[10000] w-full max-w-3xl mx-4">
        <div class="glossy-card rounded-lg shadow-xl transform transition-all flex flex-col max-h-[80vh]">
          <div class="glossy-header px-6 pt-5 pb-4 z-[10000]">
            <div class="flex justify-between items-center">
              <h3 class="text-purple-400 text-2xl leading-6 font-medium" id="modal-title">
                Add New Customer
              </h3>
              <button 
                @click="showModal = false" 
                class="btn btn-circle btn-outline ml-4 text-gray-400 hover:text-purple-400 transition-colors duration-200 focus:outline-none"
                aria-label="Close modal"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <div class="flex-grow overflow-y-auto px-6 py-4">
            <!-- Error message area -->
            <div v-if="errorMessage" class="mb-4 bg-red-900 border border-red-500 text-red-100 px-4 py-3 rounded relative" role="alert">
              <strong class="font-bold">Error: </strong>
              <span class="block sm:inline">{{ errorMessage }}</span>
              <button @click="errorMessage = ''" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 5.652a1 1 0 10-1.414-1.414L10 7.172 7.066 4.238a1 1 0 10-1.414 1.414L8.586 8.586l-2.934 2.934a1 1 0 101.414 1.414L10 10.828l2.934 2.934a1 1 0 001.414-1.414l-2.934-2.934 2.934-2.934z"/></svg>
              </button>
            </div>
            <form @submit.prevent="addCustomer">
              <div class="mb-4">
                <label for="business_name" class="block text-sm font-medium text-purple-400">Business Name</label>
                <input
                  v-model="form.business_name"
                  type="text"
                  id="business_name"
                  class="glossy-content mt-1 block w-full rounded-lg border border-gray-700 bg-gray-800/50 text-white shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500/50"
                  required
                />
              </div>

              <div class="mb-4">
                <label for="address" class="block text-sm font-medium text-purple-400">Address</label>
                <input
                  v-model="form.address"
                  type="text"
                  id="address"
                  class="glossy-content mt-1 block w-full rounded-lg border border-gray-700 bg-gray-800/50 text-white shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500/50"
                  required
                />
              </div>

              <div class="mb-4">
                <label for="poc_name" class="block text-sm font-medium text-purple-400">POC Name</label>
                <input
                  v-model="form.poc_name"
                  type="text"
                  id="poc_name"
                  class="glossy-content mt-1 block w-full rounded-lg border border-gray-700 bg-gray-800/50 text-white shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500/50"
                  required
                />
              </div>

              <div class="mb-4">
                <label for="poc_email" class="block text-sm font-medium text-purple-400">POC Email</label>
                <input
                  v-model="form.poc_email"
                  type="email"
                  id="poc_email"
                  class="glossy-content mt-1 block w-full rounded-lg border border-gray-700 bg-gray-800/50 text-white shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500/50"
                  required
                />
              </div>

              <div class="mb-4">
                <label for="fax" class="block text-sm font-medium text-purple-400">Fax</label>
                <input
                  v-model="form.fax"
                  type="text"
                  id="fax"
                  class="glossy-content mt-1 block w-full rounded-lg border border-gray-700 bg-gray-800/50 text-white shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500/50"
                />
              </div>

              <div class="mb-4">
                <label for="pay_rate" class="block text-sm font-medium text-purple-400">Pay Rate</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">$</span>
                  <input
                    v-model="form.pay_rate"
                    type="number"
                    step="0.01"
                    id="pay_rate"
                    class="glossy-content mt-1 block w-full rounded-lg border border-gray-700 bg-gray-800/50 text-white shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500/50 pl-8"
                    required
                  />
                </div>
              </div>

              <div class="mb-4">
                <label for="net_terms" class="block text-sm font-medium text-purple-400">Net Terms</label>
                <select
                  v-model="form.net_terms"
                  id="net_terms"
                  class="glossy-content mt-1 block w-full rounded-lg border border-gray-700 bg-gray-800/50 text-white shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500/50"
                  required
                >
                  <option value="Net 7">Net 7</option>
                  <option value="Net 15">Net 15</option>
                  <option value="Net 30">Net 30</option>
                  <option value="Net 60">Net 60</option>
                </select>
              </div>

              <div class="mb-4">
                <label for="attachable_files" class="block text-sm font-medium text-purple-400">Attachable Files</label>
                <input
                  type="file"
                  id="attachable_files"
                  multiple
                  class="glossy-content mt-1 block w-full rounded-lg border border-gray-700 bg-gray-800/50 text-white file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-purple-600/60 file:text-white hover:file:bg-purple-700 file:cursor-pointer"
                  @change="handleFileChange"
                />
              </div>
            </form>
          </div>

          <div class="glossy-footer px-6 py-4 border-t border-gray-700">
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="showModal = false"
                class="glass-button px-4 py-2 bg-gray-700/50 text-white hover:bg-gray-700"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="glass-button px-4 py-2 bg-purple-600/60 text-white hover:bg-purple-700"
                @click="addCustomer"
              >
                Add Customer
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineEmits } from 'vue';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';

// Define component emits
const emit = defineEmits(['customer-added']);

// Initialize Axios configuration
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// Initialize Sanctum and CSRF protection
async function initializeSanctum() {
  try {
    // Get CSRF cookie first
    await axios.get('/sanctum/csrf-cookie');
    
    // Get CSRF token from meta tag and set up headers
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    } else {
      throw new Error('CSRF token not found');
    }
  } catch (error) {
    console.error('Error initializing Sanctum:', error);
    throw error;
  }
}

// Component setup
const showModal = ref(false);
const form = ref({
  business_name: '',
  address: '',
  poc_name: '',
  poc_email: '',
  fax: '',
  net_terms: 'Net 30',
  pay_rate: '120.00',
  attachable_files: null,
});
const errorMessage = ref('');

const toast = useToast();

async function addCustomer() {
  try {
    await initializeSanctum();
    const formData = new FormData();
    // Explicitly append all required fields, trimming whitespace
    formData.append('business_name', form.value.business_name?.trim() || '');
    formData.append('address', form.value.address?.trim() || '');
    formData.append('poc_name', form.value.poc_name?.trim() || '');
    formData.append('poc_email', form.value.poc_email?.trim() || '');
    formData.append('fax', form.value.fax?.trim() || '');
    formData.append('net_terms', form.value.net_terms?.trim() || '');
    formData.append('pay_rate', form.value.pay_rate?.toString() || '');
    // Add files if they exist
    if (form.value.attachable_files) {
      Array.from(form.value.attachable_files).forEach((file, index) => {
        formData.append(`attachable_files[${index}]`, file);
      });
    }
    // Make the API call
    const response = await axios.post('/api/customers', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      }
    });
    toast.success('Customer added successfully!');
    showModal.value = false;
    form.value = {
      business_name: '',
      address: '',
      poc_name: '',
      poc_email: '',
      fax: '',
      net_terms: 'Net 30',
      pay_rate: '120.00',
      attachable_files: null,
    };
    emit('customer-added', response.data);
  } catch (error) {
    // Log the full error response for debugging
    if (error.response) {
      console.log('AddCustomer API error:', error.response.data);
    } else {
      console.log('AddCustomer API error:', error);
    }
    if (error.response) {
      if (error.response.status === 401) {
        toast.error('Please log in to add a customer');
      } else if (error.response.status === 419) {
        toast.error('Your session has expired. Please refresh the page and try again.');
        // Try to reinitialize Sanctum
        await initializeSanctum();
      } else if (error.response.status === 422 && error.response.data.errors) {
        const messages = Object.values(error.response.data.errors).flat();
        errorMessage.value = messages.join('\n');
      } else if (error.response.status === 409) {
        errorMessage.value = 'A customer with this business name already exists. Please use a different name.';
      } else {
        toast.error(error.response.data.message || 'Failed to add customer');
      }
    } else {
      toast.error('An error occurred while adding the customer. Please try again.');
    }
  }
}

function handleFileChange(event) {
  form.value.attachable_files = Array.from(event.target.files);
}
</script>

<style scoped>
/* Glass morphism styles */
.glossy-card {
  background: rgba(15, 23, 42, 0.95);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
}

.glossy-header {
  background: linear-gradient(to right, rgba(17, 24, 39, 0.9), rgba(31, 41, 55, 0.85));
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.glossy-footer {
  background: linear-gradient(to right, rgba(20, 30, 48, 0.9), rgba(30, 41, 59, 0.85));
  box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06);
}

.glossy-section {
  background: linear-gradient(145deg, rgba(17, 24, 39, 0.5), rgba(31, 41, 55, 0.3));
  border-radius: 8px;
  position: relative;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.glossy-content {
  background: linear-gradient(145deg, rgba(31, 41, 55, 0.6), rgba(17, 24, 39, 0.4));
  border: 1px solid rgba(255, 255, 255, 0.05);
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
}

/* Glass button styling */
.glass-button {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.8);
  font-weight: 500;
  transition: all 0.2s ease;
  border-radius: 0.375rem;
}

.glass-button:hover {
  background: rgba(139, 92, 246, 0.3);
  border-color: rgba(139, 92, 246, 0.5);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
  color: rgb(255, 255, 255);
}

.glass-button:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.6);
}

.glass-button:active {
  transform: translateY(0);
  background: rgba(139, 92, 246, 0.4);
}

/* Custom scrollbar */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: rgba(75, 85, 99, 0.5) rgba(17, 24, 39, 0.3);
  max-height: calc(90vh - 200px); /* Account for header and footer */
  overflow-y: auto;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: rgba(17, 24, 39, 0.3);
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: rgba(75, 85, 99, 0.5);
  border-radius: 3px;
}
</style>
