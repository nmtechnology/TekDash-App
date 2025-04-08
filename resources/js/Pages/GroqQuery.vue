<template>
  <div class="groq-container glass-container p-5 mb-6 mt-12">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
        TekDash Chatbot
        <NetworkStatusIndicator class="inline-block ml-2" />
      </h2>
    </div>

    <p class="text-gray-400 italic mb-4">
      You can ask me about pretty much anything about alarms, fire alarms, wiring devices, testing devices or troubleshooting a system or devices on a system.
    </p>

    <div class="glass-card p-4 rounded-lg mb-4">
      <div class="flex items-center gap-4">
        <input 
          v-model="query" 
          type="text" 
          placeholder="Ask a question" 
          @keyup.enter="submitQuery" 
          class="flex-1 glass-input px-4 py-2 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-purple-400"
        />
        <button 
          @click="submitQuery" 
          class="glass-button px-6 py-2 rounded-md"
          :disabled="isLoading || !query"
        >
          <span v-if="isLoading" class="inline-block w-4 h-4 border-2 border-t-transparent border-white rounded-full animate-spin mr-2"></span>
          {{ isLoading ? 'Thinking...' : 'Ask' }}
        </button>
      </div>
    </div>

    <!-- Responses Container -->
    <div class="responses-container h-[500px] overflow-y-auto pr-2">
      <div v-for="(resp, index) in responses" :key="index" class="glass-card p-4 rounded-lg mb-4">
        <h3 class="font-bold mb-2 text-purple-400">TekDash's Response {{ responses.length - index }}:</h3>
        <p class="text-gray-200" v-html="formatResponse(resp.text)"></p>
        <p class="text-gray-500 text-xs mt-2">{{ resp.timestamp }}</p>
      </div>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="glass-error p-4 rounded-lg mt-4">
      <h3 class="font-bold mb-2 text-red-400">Error:</h3>
      <p class="text-red-300">{{ error }}</p>
      <div v-if="errorDetails" class="mt-2">
        <p class="text-amber-400 font-semibold text-sm">Error Details:</p>
        <pre class="bg-gray-900 bg-opacity-50 p-2 rounded text-red-300 text-xs mt-1 overflow-x-auto">{{ errorDetails }}</pre>
      </div>
      <button 
        @click="retryQuery" 
        class="glass-error-button mt-3 px-4 py-1 rounded-md"
        v-if="lastQuery"
      >
        Retry Request
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import NetworkStatusIndicator from '@/Components/NetworkStatusIndicator.vue';

const query = ref('');
const lastQuery = ref('');
const responses = ref([]);
const error = ref(null);
const errorDetails = ref(null);
const isLoading = ref(false);

const submitQuery = async () => {
  if (!query.value || isLoading.value) return;
  
  lastQuery.value = query.value;
  isLoading.value = true;
  error.value = null;
  errorDetails.value = null;

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
    // Extract and format error details
    const errorInfo = {
      message: err.message,
      status: err.response?.status,
      statusText: err.response?.statusText,
      data: err.response?.data,
      url: err.config?.url,
      method: err.config?.method
    };
    
    console.error('Groq API Error Details:', errorInfo);
    
    // Set appropriate error messages
    const statusMessage = err.response?.status ? ` (Status ${err.response.status}: ${err.response.statusText})` : '';
    error.value = `Request failed${statusMessage}: ${err.message}`;
    
    // Format detailed error information
    errorDetails.value = JSON.stringify(errorInfo, null, 2);
  } finally {
    isLoading.value = false;
  }
};

const retryQuery = () => {
  if (lastQuery.value) {
    query.value = lastQuery.value;
    submitQuery();
  }
};

const formatResponse = (text) => {
  if (!text) return '';
  // Replace newlines with <br> tags for HTML rendering
  return text.replace(/\n/g, '<br>');
};
</script>

<style scoped>
/* Glass Morphism Styles */
.groq-container {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 16px;
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
  transition: all 0.3s ease;
}

.glass-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.18);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
  transition: all 0.3s ease;
}

.glass-card:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
  box-shadow: 0 12px 32px 0 rgba(31, 38, 135, 0.2);
}

.glass-button {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.8);
  transition: all 0.3s ease;
}

.glass-button:hover:not(:disabled) {
  background: rgba(139, 92, 246, 0.3);
  border-color: rgba(139, 92, 246, 0.5);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
}

.glass-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.glass-input {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.glass-input:focus {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(139, 92, 246, 0.5);
}

.glass-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.glass-error {
  background: rgba(220, 38, 38, 0.2);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(220, 38, 38, 0.3);
}

.glass-error-button {
  background: rgba(220, 38, 38, 0.3);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(220, 38, 38, 0.4);
  color: white;
  transition: all 0.3s ease;
}

.glass-error-button:hover {
  background: rgba(220, 38, 38, 0.5);
}

/* Scrollbar Styling */
.responses-container {
  scrollbar-width: thin;
  scrollbar-color: rgba(139, 92, 246, 0.5) rgba(255, 255, 255, 0.1);
}

.responses-container::-webkit-scrollbar {
  width: 6px;
}

.responses-container::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
}

.responses-container::-webkit-scrollbar-thumb {
  background-color: rgba(139, 92, 246, 0.5);
  border-radius: 3px;
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  .groq-container {
    background: rgba(30, 30, 30, 0.3);
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.25);
  }
  
  .glass-card {
    background: rgba(30, 30, 30, 0.3);
    border-color: rgba(255, 255, 255, 0.08);
  }
  
  .glass-card:hover {
    background: rgba(40, 40, 40, 0.4);
  }
  
  .glass-input {
    background: rgba(30, 30, 30, 0.4);
    border-color: rgba(255, 255, 255, 0.1);
  }
  
  .glass-input:focus {
    background: rgba(30, 30, 30, 0.5);
  }
}
</style>