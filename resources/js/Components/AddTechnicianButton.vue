<template>
  <div class="tech-button-container">
    <button
      @click="showModal = true"
      class="btn flex items-center gap-2 px-4 py-2 font-bold text-sm text-green-400 transition-all duration-300"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Technician
    </button>
  </div>
  
  <!-- Teleport the modal to body to fix positioning issues -->
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="showModal" class="modal-backdrop fixed inset-0 z-[9999] flex items-center justify-center p-4" @click="closeModal">
        <div class="glossy-card rounded-lg overflow-hidden shadow-xl transform flex flex-col" @click.stop>
          <div class="glossy-header px-6 pt-5 pb-4">
            <div class="flex justify-between items-center">
              <h3 class="text-lime-400 text-2xl leading-6 font-medium" id="modal-title">
                Add New Technician
              </h3>
              <button
                @click="closeModal"
                class="btn btn-circle btn-outline ml-4 text-gray-400 hover:text-lime-400 transition-colors duration-200 focus:outline-none"
                aria-label="Close modal"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <div class="glossy-section flex-grow overflow-y-auto px-6 py-4">
            <form @submit.prevent="addTechnician">
              <div class="mb-4">
                <label for="first_name" class="block text-sm font-medium text-green-400">First Name</label>
                <input
                  v-model="form.first_name"
                  type="text"
                  id="first_name"
                  class="glossy-content mt-1 block w-full rounded-lg border border-gray-700 bg-gray-800/50 text-white shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-500/50"
                  required
                />
              </div>

              <div class="mb-4">
                <label for="last_name" class="block text-sm font-medium text-green-400">Last Name</label>
                <input
                  v-model="form.last_name"
                  type="text"
                  id="last_name"
                  class="glossy-content mt-1 block w-full rounded-lg border border-gray-700 bg-gray-800/50 text-white shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-500/50"
                  required
                />
              </div>

              <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-green-400">Email</label>
                <input
                  v-model="form.email"
                  type="email"
                  id="email"
                  class="glossy-content mt-1 block w-full rounded-lg border border-gray-700 bg-gray-800/50 text-white shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-500/50"
                  required
                />
              </div>

              <div class="mb-4">
                <label for="phone_number" class="block text-sm font-medium text-green-400">Phone Number</label>
                <input
                  v-model="form.phone_number"
                  type="text"
                  id="phone_number"
                  class="glossy-content mt-1 block w-full rounded-lg border border-gray-700 bg-gray-800/50 text-white shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-500/50"
                  required
                />
              </div>

              <div class="mb-4">
                <label for="pay_rate" class="block text-sm font-medium text-green-400">Pay Rate</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">$</span>
                  <input
                    v-model="form.pay_rate"
                    type="number"
                    step="0.01"
                    id="pay_rate"
                    class="glossy-content mt-1 block w-full rounded-lg border border-gray-700 bg-gray-800/50 text-white shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-500/50 pl-8"
                    required
                  />
                </div>
              </div>
            </form>
          </div>

          <div class="glossy-footer px-6 py-4 border-t border-gray-700">
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeModal"
                class="glass-button px-4 py-2 bg-gray-700/50 text-white hover:bg-gray-700"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="glass-button px-4 py-2 bg-lime-600/60 text-white hover:bg-lime-700"
                @click="addTechnician"
              >
                Add Technician
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from '@/Composables/useToast';
import { defineEmits } from 'vue';
// Teleport is a built-in component in Vue 3 - no need to import it explicitly

const showModal = ref(false);
const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone_number: '',
  pay_rate: '',
});

const emit = defineEmits(['technician-added']);
const toast = useToast ? useToast() : null;

function closeModal() {
  showModal.value = false;
}

async function addTechnician() {
  try {
    // Optionally, get CSRF cookie for Sanctum
    await axios.get('/sanctum/csrf-cookie');
    const response = await axios.post('/api/technicians', form.value);
    if (toast) toast.success('Technician added successfully!');
    emit('technician-added', response.data);
    closeModal();
    form.value = {
      first_name: '',
      last_name: '',
      email: '',
      phone_number: '',
      pay_rate: '',
    };
  } catch (error) {
    if (toast) toast.error('Failed to add technician.');
    // Optionally handle error details
  }
}
</script>

<style scoped>
/* Glass morphism styles */
.glossy-card {
  display: flex;
  flex-direction: column;
  background: rgba(15, 23, 42, 0.95);
  width: 100%;
  max-width: 500px;
  max-height: 80vh;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  z-index: 10000;
  position: relative;
  margin: 0 auto;
  transform: translateY(-5vh); /* This gives the appearance of being centered */
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.glossy-header {
  background: linear-gradient(to right, rgba(17, 24, 39, 0.9), rgba(31, 41, 55, 0.85));
}

.glossy-footer {
  background: linear-gradient(to right, rgba(20, 30, 48, 0.9), rgba(30, 41, 59, 0.85));
  box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06);
}

.glossy-section {
  background: linear-gradient(145deg, rgba(17, 24, 39, 0.5), rgba(31, 41, 55, 0.3));
  border-radius: 8px;
  padding: 10px;
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
  background: rgba(132, 204, 22, 0.3);
  border-color: rgba(132, 204, 22, 0.5);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(132, 204, 22, 0.2);
  color: rgb(255, 255, 255);
}

.glass-button:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(132, 204, 22, 0.6);
}

.glass-button:active {
  transform: translateY(0);
  background: rgba(132, 204, 22, 0.4);
}

/* Custom scrollbar */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: rgba(75, 85, 99, 0.5) rgba(17, 24, 39, 0.3);
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

/* Center modal container */
.tech-button-container {
  position: relative;
}

/* Modal animation */
.transform {
  transition: all 0.3s ease-out;
}

@media (max-height: 700px) {
  .glossy-card {
    max-height: 90vh;
    transform: translateY(0);
  }
}

@media (max-width: 640px) {
  .glossy-card {
    max-width: 95vw;
  }
}

/* Modal fade transition */
.modal-fade-enter-active, .modal-fade-leave-active {
  transition: all 0.3s ease;
}
.modal-fade-enter-from {
  opacity: 0;
  transform: translateY(-5vh) scale(0.95);
}
.modal-fade-leave-to {
  opacity: 0;
  transform: translateY(-5vh) scale(0.95);
}
.modal-fade-enter-to, .modal-fade-leave-from {
  opacity: 1;
  transform: translateY(-5vh) scale(1);
}

/* Modal fade transition */
.modal-fade-enter-active, .modal-fade-leave-active {
  transition: opacity 0.5s;
}
.modal-fade-enter, .modal-fade-leave-to /* .modal-fade-leave-active in <2.1.8 */ {
  opacity: 0;
}
</style>
