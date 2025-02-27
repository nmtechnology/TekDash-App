<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import Stats from '@/Components/Stats.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const statsData = ref([]);
const isLoading = ref(true);

// Fetch stats from the backend
async function fetchStats() {
  try {
    isLoading.value = true;
    const response = await axios.get('/work-order-stats');
    statsData.value = response.data;
  } catch (error) {
    console.error('Error fetching stats:', error);
    // Provide fallback data if there's an error
    statsData.value = [
      { name: 'Total Revenue', value: '$0.00', change: '0%', changeType: 'neutral' },
      { name: 'Completed Orders', value: '0', change: '0%', changeType: 'neutral' },
      { name: 'Pending Orders', value: '0', change: '0%', changeType: 'neutral' },
      { name: 'Average Price', value: '$0.00', change: '0%', changeType: 'neutral' },
    ];
  } finally {
    isLoading.value = false;
  }
}

// Load stats when component mounts
onMounted(() => {
  fetchStats();
});


</script>

<template>
    <AppLayout class="" title="Dashboard">
        <div class="pt-16"> <!-- Adjusted padding to account for the fixed nav -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Loading state -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500 dark:border-lime-400"></div>
    </div>
    
    <!-- Stats component -->
    <Stats v-else :stats="statsData" /><div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white"></h1>
      <button 
        @click="fetchStats" 
        class="flex items-center gap-2 px-3 py-1.5 mt-2 btn outline text-lime-400 rounded-md hover:bg-lime-500 dark:hover:bg-lime-500 hover:text-gray-900 transition"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Refresh
      </button>
    </div>
                <div class="relative bg-transparent opacity-75 overflow-hidden shadow-xl sm:rounded-lg bg-cover bg-center bg-no-repeat" style="background-image: url('https://images.unsplash.com/photo-1553152531-b98a2fc8d3bf?q=80&w=3731&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');">
                    <div class="absolute inset-0 bg-black opacity-50"></div> <!-- Overlay -->
                    <div class="relative z-10">
                        <Welcome />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Additional styles if needed */
</style>