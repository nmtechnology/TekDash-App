<template>
  <div class="pdf-viewer-container">
    <div v-if="loading" class="loading-indicator">
      <div class="animate-spin rounded-full h-12 w-12 border-4 border-lime-400 border-t-transparent"></div>
      <p class="mt-3 text-white text-sm">Loading PDF...</p>
    </div>
    
    <div v-if="error" class="error-message text-red-400 text-center p-4">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      <p>{{ errorMessage }}</p>
      <div class="mt-4">
        <a :href="`/storage/${pdfUrl}`" download class="text-lime-400 hover:text-lime-300 underline">
          Download PDF instead
        </a>
      </div>
    </div>

    <iframe
      v-if="!loading && !error"
      :src="viewerUrl"
      class="pdf-frame"
      frameborder="0"
      width="100%"
      height="100%"
      :title="title"
      @load="loaded = true"
      @error="handleError"
    ></iframe>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'PdfViewer',
  
  props: {
    pdfUrl: {
      type: String,
      required: true
    },
    title: {
      type: String,
      default: 'PDF Document'
    }
  },
  
  setup(props) {
    const loading = ref(true);
    const loaded = ref(false);
    const error = ref(false);
    const errorMessage = ref('Failed to load PDF');
    
    // Build the PDF viewer URL using browser built-in PDF viewer
    const viewerUrl = ref('');
    
    // Handle loading error
    const handleError = () => {
      loading.value = false;
      error.value = true;
      errorMessage.value = 'Failed to load PDF. Try downloading instead.';
    };
    
    // Setup the PDF viewer
    onMounted(() => {
      try {
        // Check if we need a full URL
        let pdfFullUrl = props.pdfUrl;
        
        // If URL doesn't start with http/https, assume it's a relative path
        if (!pdfFullUrl.startsWith('http://') && !pdfFullUrl.startsWith('https://')) {
          // Ensure it starts with a slash
          if (!pdfFullUrl.startsWith('/')) {
            pdfFullUrl = '/' + pdfFullUrl;
          }
          
          // Add full domain if in browser context
          if (typeof window !== 'undefined') {
            pdfFullUrl = `${window.location.origin}/storage${pdfFullUrl}`;
          }
        }
        
        // Use the browser's built-in PDF viewer if available
        // Otherwise fall back to Google Docs viewer as a reliable option
        if (navigator.userAgent.includes('Chrome') || 
            navigator.userAgent.includes('Firefox') || 
            navigator.userAgent.includes('Safari')) {
          viewerUrl.value = pdfFullUrl;
        } else {
          viewerUrl.value = `https://docs.google.com/viewer?url=${encodeURIComponent(pdfFullUrl)}&embedded=true`;
        }
        
        // Remove loading state after a short delay to ensure UI is ready
        setTimeout(() => {
          loading.value = false;
        }, 1500);
      } catch (e) {
        console.error('Error setting up PDF viewer:', e);
        handleError();
      }
    });
    
    return {
      loading,
      loaded,
      error,
      errorMessage,
      viewerUrl,
      handleError
    };
  }
};
</script>

<style scoped>
.pdf-viewer-container {
  width: 100%;
  height: 80vh;
  position: relative;
  background-color: rgba(20, 20, 20, 0.9);
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pdf-frame {
  width: 100%;
  height: 100%;
  border-radius: 0.375rem;
}

.loading-indicator, .error-message {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.error-message {
  max-width: 300px;
}
</style>
