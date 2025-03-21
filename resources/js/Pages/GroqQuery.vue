<template>
  <div>
    <h1 class="mt-10 text-green-400 text-2xl">TekDash Chabot</h1>
    <p class="text-white italic mb-4">You can ask me about pretty much anything about alarms, fire alarms, wiring devices, testing devices or troublshooting a system or devices on a system.</p>
    <div class="p-4 border border-blue-400 rounded-md">
      <div class="flex items-center gap-4">
        <input 
          v-model="query" 
          type="text" 
          placeholder="Ask a question" 
          @keyup.enter="submitQuery" 
          class="flex-1 bg-transparent border border-accent rounded-md px-4 py-2 text-lime-400 focus:outline-none focus:ring-2 focus:ring-accent"
        />
        <button 
          @click="submitQuery" 
          class="border border-green-500 text-green-500 px-6 py-2 rounded-md hover:bg-green-500 hover:text-white transition-colors duration-200"
          :disabled="isLoading || !query"
        >
          {{ isLoading ? 'Thinking...' : 'Ask' }}
        </button>
      </div>

      <!-- Add a scrollable container for responses -->
      <div class="responses-container h-[500px] overflow-y-auto mt-4 pr-2">
        <div v-for="(resp, index) in responses" :key="index" class="mb-4 border border-accent rounded-md p-4">
          <h2 class="font-bold mb-2 text-blue-400">TekDash's Response {{ responses.length - index }}:</h2>
          <p class="text-green-400" v-html="formatResponse(resp.text)"></p>
          <p class="text-gray-500 text-sm">{{ resp.timestamp }}</p>
        </div>
      </div>

      <div v-if="error" class="mt-4 border border-red-500 rounded-md p-4">
        <h2 class="font-bold mb-2 text-red-500">Error:</h2>
        <p class="text-red-500">{{ error }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import axios from 'axios';

export default {
  setup() {
    const query = ref('');
    const responses = ref([]);
    const error = ref(null);
    const isLoading = ref(false);

    const submitQuery = async () => {
      if (!query.value || isLoading.value) return;

      isLoading.value = true;
      error.value = null;

      try {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const response = await axios.post('/api/groq/query', {
          query: query.value
        }, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
          },
          timeout: 30000 // 30 second timeout
        });
        
        if (response.data.error) {
          throw new Error(response.data.error);
        }
        
        const responseText = response.data.response;
        
        if (!responseText) {
          throw new Error('Received empty response from the server');
        }
        
        responses.value.unshift({
          text: responseText,
          timestamp: new Date().toLocaleString()
        });
        
        if (responses.value.length > 5) {
          responses.value.pop();
        }
        
        query.value = '';
      } catch (err) {
        console.error('Groq API Error Details:', {
          message: err.message,
          status: err.response?.status,
          statusText: err.response?.statusText,
          data: err.response?.data,
          headers: err.response?.headers,
          config: {
            url: err.config?.url,
            method: err.config?.method,
            headers: err.config?.headers
          }
        });
        
        error.value = err.response?.data?.message || err.message || 'An error occurred while processing your request';
      } finally {
        isLoading.value = false;
      }
    };

    const formatResponse = (text) => {
      if (!text) return '';
      // Replace newlines with <br> tags for HTML rendering
      return text.replace(/\n/g, '<br>');
    };

    return {
      query,
      responses,
      error,
      isLoading,
      submitQuery,
      formatResponse,
    };
  },
};
</script>

<style scoped lang="postcss">
input::placeholder {
  @apply text-green-400 opacity-50;
}

button:disabled {
  @apply opacity-50 cursor-not-allowed;
}

.responses-container {
  scrollbar-width: thin;
  scrollbar-color: theme('colors.green.500') theme('colors.gray.800');
}

.responses-container::-webkit-scrollbar {
  width: 8px;
}

.responses-container::-webkit-scrollbar-track {
  background: theme('colors.gray.800');
  border-radius: 4px;
}

.responses-container::-webkit-scrollbar-thumb {
  background-color: theme('colors.green.500');
  border-radius: 4px;
}
</style>