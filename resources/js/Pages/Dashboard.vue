<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import Stats from '@/Components/Stats.vue';
import RevenueStats from '@/Components/RevenueStats.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import CurrentTime from '@/Components/CurrentTime.vue';

// Define reactive state
const statsData = ref([]);
const isLoading = ref(true);
const filteredData = ref([]);

// Fetch stats from the backend
async function fetchStats() {
  try {
    isLoading.value = true;
    const response = await axios.get('/work-order-stats');
    
    // Check that the data is valid
    if (response.data && Array.isArray(response.data) && response.data.length > 0) {
      statsData.value = response.data;
      filteredData.value = response.data;
      console.log('Stats data loaded successfully:', response.data);
    } else {
      console.error('Stats data format is incorrect:', response.data);
      // Use fallback data
      const fallback = getFallbackStats();
      statsData.value = fallback;
      filteredData.value = fallback;
    }
  } catch (error) {
    console.error('Error fetching stats:', error);
    // Provide fallback data if there's an error
    const fallback = getFallbackStats();
    statsData.value = fallback;
    filteredData.value = fallback;
  } finally {
    isLoading.value = false;
  }
}

// Function to provide fallback stats data
function getFallbackStats() {
  return [
    { name: 'Total Revenue', value: '$0.00', change: '0%', changeType: 'neutral' },
    { name: 'Completed Orders', value: '0', change: '0%', changeType: 'neutral' },
    { name: 'Pending Orders', value: '0', change: '0%', changeType: 'neutral' },
    { name: 'Average Price', value: '$0.00', change: '0%', changeType: 'neutral' },
  ];
}

// Load stats when component mounts
onMounted(() => {
  fetchStats();
});
</script>

<template>
  <AppLayout title="Dashboard">
    <template #header>
      <div class="fixed mt-16 top-0 left-0 right-0 z-10 backdrop-blur-md bg-white/50 dark:bg-gray-800/60 shadow">
        <div class="max-w-7xl mx-auto py-2 px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-lime-400 leading-tight">
              Dashboard <CurrentTime />
            </h2>
          </div>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white/70 dark:bg-gray-800/70 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <!-- Other dashboard content -->
          <div class="pt-16"> <!-- Adjusted padding to account for the fixed nav -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
              <!-- Loading state -->
              <div v-if="isLoading" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500 dark:border-lime-400"></div>
              </div>
              
              <!-- Stats component -->
              <div v-else>
                <Stats :stats="filteredData" />
                <RevenueStats />
              </div>
              
              <div class="flex justify-between items-start mb-3 mt-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white"></h1>
              </div>
              
              <div class="relative bg-transparent opacity-75 overflow-hidden shadow-xl sm:rounded-lg bg-cover bg-center bg-no-repeat" 
                  style="background-image: url('https://images.unsplash.com/photo-1553152531-b98a2fc8d3bf?q=80&w=3731&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');">
                <div class="absolute inset-0 bg-black opacity-50"></div> <!-- Overlay -->
                <div class="relative z-10">
                  <Welcome />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Additional styles if needed */
</style>