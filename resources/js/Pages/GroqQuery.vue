<template>
    <div>
      <h1 class="mt-10 text-blue-500 font-bold text-2xl">TekDash AI Assistant</h1>
      <p class="p-10 text-blue-600">You can ask me about pretty much anything about anything</p>
      <div class="p-4 border border-accent rounded-md">
        <div class="flex items-center gap-4">
          <input 
            v-model="query" 
            type="text" 
            placeholder="Ask a question" 
            @keyup.enter="submitQuery" 
            class="flex-1 bg-transparent border border-accent rounded-md px-4 py-2 text-blue-400 focus:outline-none focus:ring-2 focus:ring-accent"
          />
          <button 
            @click="submitQuery" 
            class="border border-green-500 text-green-500 px-6 py-2 rounded-md hover:bg-green-500 hover:text-white transition-colors duration-200"
            :disabled="!query"
          >
            Ask
          </button>
        </div>
  
        <div v-for="(resp, index) in responses" :key="index" class="mt-4 border border-accent rounded-md p-4">
          <h2 class="font-bold mb-2 text-blue-400">TekDash's Response {{ responses.length - index }}:</h2>
          <p class="text-lime-400" v-html="formatResponse(resp.text)"></p>
          <p class="text-gray-300 text-sm">{{ resp.timestamp }}</p>
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
  
      const submitQuery = async () => {
        if (!query.value) return;
  
        try {
          const { data } = await axios.post('/groq/query', {
            query: query.value
          });
          
          responses.value.unshift({
            text: data.response,
            timestamp: new Date().toLocaleString()
          });
          if (responses.value.length > 5) {
            responses.value.pop(); // Remove the oldest response
          }
          error.value = null;
          query.value = ''; // Reset the input field
        } catch (err) {
          console.error('Groq API Error:', err);
          error.value = err.response?.data?.error || 'An error occurred while processing your request';
          responses.value = [];
        }
      };
  
      const formatResponse = (text) => {
        if (!text) return ''; // Check if text is undefined or null
        // Replace newlines with <br> tags for HTML rendering
        return text.replace(/\n/g, '<br>');
      };
  
      return {
        query,
        responses,
        error,
        submitQuery,
        formatResponse,
      };
    },
  };
  </script>
  
  <style scoped>
  input::placeholder {
    @apply text-lime-400 opacity-50;
  }
  
  button:disabled {
    @apply opacity-50 cursor-not-allowed;
  }
  </style>