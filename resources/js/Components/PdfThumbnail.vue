<template>
  <div class="pdf-thumbnail-wrapper">
    <div class="pdf-thumbnail" @click="handleClick">
      <!-- Canvas for PDF rendering when successful -->
      <canvas 
        ref="canvas" 
        class="thumbnail-canvas"
        width="120" height="160"
        :style="{ opacity: thumbnailGenerated ? 1 : 0, transition: 'opacity 0.2s' }"
      ></canvas>
      
      <!-- Fallback PDF icon with loading state or error message -->
      <div class="pdf-icon" v-if="!thumbnailGenerated || thumbnailError">
        <div class="flex flex-col items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" class="pdf-svg">
            <path d="M320 464c8.8 0 16-7.2 16-16V160H256c-17.7 0-32-14.3-32-32V48H64c-8.8 0-16 7.2-16 16V448c0 8.8 7.2 16 16 16H320zM0 64C0 28.7 28.7 0 64 0H229.5c17 0 33.3 6.7 45.3 18.7l90.5 90.5c12 12 18.7 28.3 18.7 45.3V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V64z"/>
          </svg>
          <span v-if="isLoading && !thumbnailError" class="loading-text">Loading...</span>
          <span v-if="thumbnailError" class="error-text">Preview failed</span>
        </div>
      </div>
      
      <!-- Filename display -->
      <div class="filename">{{ truncatedFilename }}</div>
      
      <!-- Delete button -->
      <button class="delete-button" @click.stop="handleDelete">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
          <path d="M3 6h18M9 6v12m3-12v12m3-12v12m3-12v12M3 6h18M3 6l3 15h12l3-15M9 6h6"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, watch } from 'vue';
import * as pdfjsLib from 'pdfjs-dist/build/pdf';
import pdfjsWorker from 'pdfjs-dist/build/pdf.worker?url';

// Explicitly set the worker source and verify
console.log('PDF.js worker URL:', pdfjsWorker);
pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorker;

export default {
  name: 'PdfThumbnail',
  
  props: {
    pdfUrl: {
      type: String,
      required: true
    },
    filename: {
      type: String,
      default: 'document.pdf'
    }
  },

  setup(props, { emit }) {
    const canvas = ref(null);
    const thumbnailGenerated = ref(false);
    const thumbnailError = ref(false);
    const isLoading = ref(true);
    const retryCount = ref(0);
    const MAX_RETRIES = 2;
    
    // Log worker setup verification
    console.log('PdfThumbnail: Worker source is set to:', pdfjsLib.GlobalWorkerOptions.workerSrc);

    const truncatedFilename = computed(() => {
      // Use filename from props or extract from URL
      const filename = props.filename || extractFilenameFromUrl(props.pdfUrl) || 'document.pdf';
      const maxLength = 20;
      if (filename.length <= maxLength) return filename;
      return filename.substring(0, maxLength - 3) + '...';
    });
    
    // Helper function to extract filename from URL
    const extractFilenameFromUrl = (url) => {
      if (!url) return '';
      const parts = url.split('/');
      const rawName = parts[parts.length - 1] || '';
      // Remove query parameters
      return rawName.split('?')[0];
    };

    // Enhanced URL normalization function with better edge case handling
    const normalizeUrl = (url) => {
      if (!url) {
        console.error('PdfThumbnail: Empty URL provided');
        return '';
      }
      
      console.log('PdfThumbnail: Normalizing URL:', url);
      
      try {
        // Get the current origin
        const origin = window.location.origin;
        let normalizedUrl = url.trim();
        
        // Remove hash fragments and query strings for consistency
        normalizedUrl = normalizedUrl.split('#')[0];
        
        // Handle already absolute URLs
        if (normalizedUrl.startsWith('http://') || normalizedUrl.startsWith('https://')) {
          // Replace localhost with current origin if needed
          if (normalizedUrl.includes('localhost') && !origin.includes('localhost')) {
            normalizedUrl = normalizedUrl.replace(/http:\/\/localhost(?:\:\d+)?/, origin);
            console.log('PdfThumbnail: Replaced localhost URL:', normalizedUrl);
          } else if (normalizedUrl.includes('127.0.0.1') && !origin.includes('127.0.0.1')) {
            normalizedUrl = normalizedUrl.replace(/http:\/\/127\.0\.0\.1(?:\:\d+)?/, origin);
            console.log('PdfThumbnail: Replaced 127.0.0.1 URL:', normalizedUrl);
          }
        } 
        // Handle storage URLs, which is a common pattern in Laravel
        else if (normalizedUrl.includes('storage/') || normalizedUrl.includes('/storage/')) {
          // Fix common storage path issues
          let storagePath = normalizedUrl;
          
          // Remove any duplicate 'storage/' prefixes
          storagePath = storagePath.replace(/^storage\/storage\//, 'storage/');
          
          // Ensure it has a leading slash if it doesn't start with /storage/
          if (!storagePath.startsWith('/storage/')) {
            storagePath = `/storage/${storagePath.replace(/^storage\//, '')}`;
          }
          
          // Handle the /public/ prefix that sometimes appears in Laravel storage paths
          storagePath = storagePath.replace(/\/public\/storage\//, '/storage/');
          
          normalizedUrl = `${origin}${storagePath}`;
          console.log('PdfThumbnail: Formatted storage URL:', normalizedUrl);
        }
        // Handle other relative URLs
        else {
          // Ensure proper slash formatting
          if (!normalizedUrl.startsWith('/')) {
            normalizedUrl = '/' + normalizedUrl;
          }
          
          normalizedUrl = `${origin}${normalizedUrl}`;
          console.log('PdfThumbnail: Normalized relative URL:', normalizedUrl);
        }
        
        // Verify we have a valid URL
        new URL(normalizedUrl);
        
        return normalizedUrl;
      } catch (error) {
        console.error('PdfThumbnail: Error normalizing URL:', error);
        
        // Attempt a simple fallback if URL parsing failed
        const origin = window.location.origin;
        const fallbackUrl = `${origin}/storage/${url.replace(/^.*[\\\/]/, '')}`;
        console.log('PdfThumbnail: Using fallback URL:', fallbackUrl);
        
        return fallbackUrl;
      }
    };
    
    const generateThumbnail = async () => {
      if (!props.pdfUrl) {
        console.error('PdfThumbnail: No PDF URL provided');
        thumbnailGenerated.value = false;
        thumbnailError.value = true;
        isLoading.value = false;
        emit('error', new Error('No PDF URL provided'));
        return;
      }
      
      // Reset state on new render attempt
      thumbnailError.value = false;
      thumbnailGenerated.value = false;
      isLoading.value = true;
      
      try {
        // Normalize the URL - this is critical for PDF.js to work correctly
        const normalizedUrl = normalizeUrl(props.pdfUrl);
        console.log('PdfThumbnail: Loading PDF thumbnail from:', normalizedUrl);
        
        // Create a timeout to avoid hanging on unresponsive URLs
        const timeoutPromise = new Promise((_, reject) => 
          setTimeout(() => reject(new Error('URL access timeout')), 8000)
        );
        
        // Check if URL is accessible first
        try {
          const fetchPromise = fetch(normalizedUrl, { 
            method: 'HEAD',
            credentials: 'include', // Include cookies for authenticated requests
            headers: {
              'Accept': 'application/pdf',
              'X-Requested-With': 'XMLHttpRequest'
            }
          });
          
          // Race between fetch and timeout
          const checkResponse = await Promise.race([fetchPromise, timeoutPromise]);
          
          console.log('PdfThumbnail: URL check response:', checkResponse.status, checkResponse.ok);
          
          if (!checkResponse.ok) {
            console.error('PdfThumbnail: URL is not accessible:', checkResponse.status);
            thumbnailError.value = true;
            isLoading.value = false;
            emit('error', new Error(`URL not accessible: ${checkResponse.status}`));
            return;
          }
        } catch (fetchError) {
          console.error('PdfThumbnail: Cannot fetch URL:', fetchError);
          // Continue anyway, as the HEAD request might fail but GET could still work
        }
        
        console.log('PdfThumbnail: Creating PDF loading task');
        
        // Try multiple CDN sources for cMaps
        const cMapUrls = [
          `${window.location.origin}/node_modules/pdfjs-dist/cmaps/`,
          'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/cmaps/',
          'https://unpkg.com/pdfjs-dist@3.11.174/cmaps/'
        ];
        
        // Improved PDF loading with proper error handling and cMapUrl
        const loadingTask = pdfjsLib.getDocument({
          url: normalizedUrl,
          withCredentials: true,
          cMapUrl: cMapUrls[0],
          cMapPacked: true,
          rangeChunkSize: 65536,
          disableAutoFetch: false, // Enable auto fetching
          isEvalSupported: true
        });
        
        console.log('PdfThumbnail: Awaiting PDF document');
        const pdf = await loadingTask.promise;
        
        console.log('PdfThumbnail: PDF loaded successfully, pages:', pdf.numPages);
        if (pdf.numPages < 1) {
          throw new Error('PDF has no pages');
        }
        
        const page = await pdf.getPage(1);
        console.log('PdfThumbnail: First page retrieved');
        
        if (!canvas.value) {
          console.error('PdfThumbnail: Canvas reference is null');
          thumbnailError.value = true;
          isLoading.value = false;
          throw new Error('Canvas reference is null');
        }
        
        const viewport = page.getViewport({ scale: 0.3 });
        const context = canvas.value.getContext('2d', { alpha: false });
        
        // Set proper dimensions
        canvas.value.width = viewport.width;
        canvas.value.height = viewport.height;
        
        console.log('PdfThumbnail: Rendering page to canvas', {
          width: viewport.width,
          height: viewport.height
        });
        
        // Render the page
        const renderTask = page.render({
          canvasContext: context,
          viewport: viewport
        });
        
        await renderTask.promise;
        console.log('PdfThumbnail: Rendering complete');
        thumbnailGenerated.value = true;
        thumbnailError.value = false;
        isLoading.value = false;
        
        // Reset retry count on success
        retryCount.value = 0;
        
      } catch (error) {
        console.error('PdfThumbnail: Error generating PDF thumbnail:', error);
        console.error('PdfThumbnail: Error details:', {
          message: error.message,
          name: error.name,
          stack: error.stack,
          url: props.pdfUrl
        });
        
        thumbnailGenerated.value = false;
        isLoading.value = false;
        
        // Try with an alternative URL formation before giving up completely
        if (retryCount.value < MAX_RETRIES) {
          retryCount.value++;
          console.log(`PdfThumbnail: Retrying (${retryCount.value}/${MAX_RETRIES})...`);
          
          // Wait a moment before retry
          setTimeout(() => {
            generateThumbnail();
          }, 1000);
          return;
        }
        
        // If all retries failed, mark as error
        thumbnailError.value = true;
        emit('error', error);
      }
    };

    // Regenerate thumbnail when URL changes
    watch(() => props.pdfUrl, (newUrl, oldUrl) => {
      console.log('PdfThumbnail: URL changed from', oldUrl, 'to', newUrl);
      // Reset state for new URL
      thumbnailGenerated.value = false;
      thumbnailError.value = false;
      isLoading.value = true;
      retryCount.value = 0;
      
      // Don't try to generate with empty URLs
      if (!newUrl) {
        thumbnailError.value = true;
        isLoading.value = false;
        return;
      }
      
      // Generate with a small delay to ensure DOM is updated
      setTimeout(() => {
        generateThumbnail();
      }, 100);
    });

    const handleClick = () => {
      emit('click', { url: props.pdfUrl, filename: props.filename });
    };

    const handleDelete = () => {
      emit('delete', props.pdfUrl);
    };

    onMounted(() => {
      console.log('PdfThumbnail: Component mounted, generating thumbnail');
      generateThumbnail();
    });

    return {
      canvas,
      thumbnailGenerated,
      thumbnailError,
      truncatedFilename,
      isLoading,
      handleClick,
      handleDelete
    };
  }
}
</script>

<style scoped>
.pdf-thumbnail-wrapper {
  display: inline-block;
  cursor: pointer;
  transition: transform 0.2s;
  width: 100%;
  max-width: 180px;
  min-width: 100px;
}

.pdf-thumbnail-wrapper:hover {
  transform: scale(1.05);
}

.pdf-thumbnail {
  width: 100%;
  aspect-ratio: 3/4;
  min-width: 100px;
  max-width: 180px;
  min-height: 120px;
  max-height: 240px;
  border: 1px solid #263343;
  border-radius: 12px;
  overflow: hidden;
  background: #2a2a2a;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: relative;
}

.thumbnail-canvas {
  width: 100%;
  height: auto;
  min-height: 120px;
  max-height: 200px;
  object-fit: contain;
  background: #010101;
  display: block;
}

.pdf-icon {
  width: 100%;
  height: 60%;
  min-height: 120px;
  max-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #232323;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: 5;
}

.pdf-icon svg.pdf-svg {
  width: 80px;
  height: 60px;
  fill: #9dff00e9;
}

.loading-text {
  color: #9dff00;
  font-size: 0.75rem;
  margin-top: 8px;
  animation: pulse 1.5s infinite;
}

.error-text {
  color: #ff4d4f;
  font-size: 0.75rem;
  margin-top: 8px;
}

@keyframes pulse {
  0% { opacity: 0.6; }
  50% { opacity: 1; }
  100% { opacity: 0.6; }
}

.filename {
  padding: 8px;
  font-size: 0.95rem;
  color: #1f2937;
  text-align: center;
  word-break: break-word;
  background: #272727;
  width: 100%;
  border-top: 1px solid #e2e8f0;
}

.delete-button {
  position: absolute;
  top: 8px;
  right: 8px;
  background: transparent;
  border: none;
  cursor: pointer;
  outline: none;
}

.delete-button svg {
  width: 30px;
  height: 30px;
  fill: #ff4d4f;
}

@media (max-width: 600px) {
  .pdf-thumbnail-wrapper {
    max-width: 120px;
    min-width: 80px;
  }
  .pdf-thumbnail {
    min-width: 80px;
    max-width: 120px;
    min-height: 80px;
    max-height: 160px;
    border-radius: 8px;
  }
  .thumbnail-canvas, .pdf-icon {
    min-height: 80px;
    max-height: 120px;
  }
  .pdf-icon svg {
    width: 48px;
    height: 36px;
  }
  .filename {
    font-size: 0.8rem;
    padding: 6px;
  }
}
</style>
