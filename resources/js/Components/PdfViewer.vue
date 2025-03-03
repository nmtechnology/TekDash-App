<template>
  <div class="pdf-viewer-outer-container">
    <div class="pdf-viewer-container">
      <!-- Header controls -->
      <div class="pdf-header">
        <h2 class="pdf-title">{{ title }}</h2>
        <div class="pdf-controls">
          <button 
            class="action-button sign-button" 
            @click="activateSignatureMode"
            v-if="!signatureMode && !loading && !error"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M6.5 8a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
              <path d="M12.354 3.646a.5.5 0 0 1 0 .708l-4.5 4.5a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 7.293l4.146-4.147a.5.5 0 0 1 .708 0z"/>
              <path d="M11.5 0h-7A1.5 1.5 0 0 0 3 1.5v13A1.5 1.5 0 0 0 4.5 16h7a1.5 1.5 0 0 0 1.5-1.5v-13A1.5 1.5 0 0 0 11.5 0zm0 1a.5.5 0 0 1 .5.5v13a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-13a.5.5 0 0 1 .5-.5h7z"/>
            </svg>
            Sign Document
          </button>
          <button class="action-button close-button" @click="closeViewer">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
            </svg>
            Close
          </button>
        </div>
      </div>
      
      <!-- Loading indicator -->
      <div v-if="loading" class="loading-indicator">
        <div class="spinner"></div>
        <p>Loading PDF...</p>
      </div>
      
      <!-- Error display -->
      <div v-if="error" class="error-message">
        <h3>Error Loading PDF</h3>
        <p>{{ errorMessage }}</p>
        <div class="error-actions">
          <a :href="pdfDisplayUrl" target="_blank" class="error-link">
            Open PDF directly in new tab
          </a>
          <button @click="setupPdfViewer" class="error-button">
            Try again
          </button>
        </div>
      </div>
      
      <!-- PDF Display using object tag (better PDF support) -->
      <div v-show="!loading && !error" class="pdf-display-container">
        <!-- Using object tag for better PDF compatibility -->
        <object
          v-if="useObjectTag"
          :data="pdfDisplayUrl"
          type="application/pdf"
          class="pdf-display"
          @load="onPdfLoad"
          @error="handleError"
        >
          <p>Your browser doesn't support PDF viewing. <a :href="pdfDisplayUrl" target="_blank">Download Instead</a></p>
        </object>
        
        <!-- Fallback to iframe if needed -->
        <iframe
          v-else
          :src="pdfDisplayUrl"
          class="pdf-display"
          frameborder="0"
          @load="onPdfLoad"
          @error="handleError"
        ></iframe>
      </div>

      <!-- Signature overlay -->
      <div v-if="signatureMode" class="signature-overlay">
        <div class="signature-box">
          <div class="signature-header">
            <h3 class="signature-title">Please sign below</h3>
            <button class="signature-close" @click="cancelSignature">×</button>
          </div>
          
          <canvas
            ref="signatureCanvas"
            class="signature-canvas bg-black"
            @mousedown="startDrawing"
            @mousemove="draw"
            @mouseup="stopDrawing"
            @mouseleave="stopDrawing"
            @touchstart.prevent="startDrawing"
            @touchmove.prevent="draw"
            @touchend.prevent="stopDrawing"
          ></canvas>
          
          <!-- NEW: Added input fields for first name and last name -->
          <div class="signature-inputs">
            <div class="input-group">
              <label for="firstName">First Name</label>
              <input 
                type="text" 
                id="firstName" 
                v-model="firstName" 
                placeholder="Enter first name" 
              />
            </div>
            <div class="input-group">
              <label for="lastName">Last Name</label>
              <input 
                type="text" 
                id="lastName" 
                v-model="lastName" 
                placeholder="Enter last name" 
              />
            </div>
          </div>
          
          <div class="signature-actions">
            <button @click="clearSignature" class="signature-btn clear">Clear</button>
            <button @click="cancelSignature" class="signature-btn cancel">Cancel</button>
            <button @click="saveSignature" class="signature-btn save">Apply Signature</button>
          </div>
        </div>
      </div>

      <!-- Download PDF after signature -->
      <div v-if="modifiedPdfUrl" class="download-bar">
        <p>Document signed successfully!</p>
        <div class="download-actions">
          <!-- Restored download button -->
          <a :href="modifiedPdfUrl" :download="signedFilename" class="action-btn download-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
              <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
            </svg>
            Download
          </a>
          <!-- New upload button -->
          <button @click="uploadSignedDocument" class="action-btn upload-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
              <path d="M7.646 4.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V14.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3z"/>
            </svg>
            Upload to Work Order
          </button>
        </div>
      </div>
      
      <!-- Debug info -->
      <div v-if="showDebugInfo" class="debug-info">
        <p>PDF URL: {{ pdfDisplayUrl }}</p>
        <p>Loading: {{ loading }}</p>
        <p>Error: {{ error }}</p>
        <p>Loaded: {{ loaded }}</p>
        <button @click="showDebugInfo = false" class="debug-close">Close Debug</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch, computed } from 'vue';
import { PDFDocument, rgb } from 'pdf-lib';

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
    },
    editable: {
      type: Boolean,
      default: false
    },
    debug: {
      type: Boolean,
      default: false
    },
    // Add a prop for the work order ID to redirect to after upload
    workOrderId: {
      type: [Number, String],
      default: null
    },
    // Add a redirectAfterUpload prop to control redirection behavior
    redirectAfterUpload: {
      type: Boolean,
      default: true
    },
    // Add an onClose prop to handle closing from parent component
    onClose: {
      type: Function,
      default: null
    }
  },
  
  setup(props, { emit }) {
    // State
    const loading = ref(true);
    const loaded = ref(false);
    const error = ref(false);
    const errorMessage = ref('Failed to load PDF');
    const pdfDisplayUrl = ref('');
    const signatureCanvas = ref(null);
    const signatureMode = ref(false);
    const isDrawing = ref(false);
    const modifiedPdfUrl = ref(null);
    const showDebugInfo = ref(props.debug);
    
    // Determine best display method based on browser
    const useObjectTag = ref(
      navigator.userAgent.includes('Chrome') || 
      navigator.userAgent.includes('Firefox')
    );
    
    // Drawing state variables
    let lastX = 0;
    let lastY = 0;

    // NEW: Add reactive variables for first and last name
    const firstName = ref('');
    const lastName = ref('');
    
    // Add a ref to store the original filename
    const originalFilename = ref('document.pdf');
    
    // Helper function to extract filename from URL
    const extractFilename = (url) => {
      if (!url) return 'document.pdf';
      
      // Remove query parameters
      const urlWithoutParams = url.split('?')[0];
      
      // Extract the filename from the URL path
      const urlParts = urlWithoutParams.split('/');
      const rawFilename = urlParts[urlParts.length - 1];
      
      // Decode URL-encoded characters
      return decodeURIComponent(rawFilename) || 'document.pdf';
    };
    
    // Compute the signed filename by appending "signed" before the extension
    const signedFilename = computed(() => {
      const filename = originalFilename.value;
      const lastDotIndex = filename.lastIndexOf('.');
      
      if (lastDotIndex === -1) {
        // No extension found
        return `${filename}-signed.pdf`;
      }
      
      const name = filename.substring(0, lastDotIndex);
      const extension = filename.substring(lastDotIndex);
      return `${name}-signed${extension}`;
    });
    
    // Methods
    const onPdfLoad = () => {
      console.log('PDF loaded successfully');
      loading.value = false;
      loaded.value = true;
    };

    const handleError = (e) => {
      console.error('PDF loading error:', e);
      loading.value = false;
      error.value = true;
      errorMessage.value = 'Failed to load PDF. Try downloading instead.';
      
      // Try alternative display method
      if (useObjectTag.value) {
        console.log('Falling back to iframe for PDF display');
        useObjectTag.value = false;
        setTimeout(() => {
          setupPdfViewer();
        }, 500);
      }
    };
    
    const closeViewer = () => {
      if (props.onClose) {
        props.onClose();
      }
    };
    
    // Simple URL processing function - avoid complexity
    const getFullPdfUrl = (url) => {
      if (!url) return '';
      
      // If it's already a full URL, return as is
      if (url.startsWith('http://') || url.startsWith('https://')) {
        return url;
      }
      
      // For local paths, simplify the logic
      let fullPath = url;
      
      // Ensure path starts with slash
      if (!fullPath.startsWith('/')) {
        fullPath = '/' + fullPath;
      }
      
      // Add storage prefix if needed and not already there
      if (!fullPath.includes('/storage/')) {
        fullPath = '/storage' + fullPath;
      }
      
      // Add domain
      if (typeof window !== 'undefined') {
        fullPath = `${window.location.origin}${fullPath}`;
      }
      
      return fullPath;
    };
    
    // Setup the PDF viewer with proper URLs
    const setupPdfViewer = () => {
      try {
        // Reset states
        loading.value = true;
        error.value = false;
        loaded.value = false;
        
        // Simplify URL processing to avoid potential issues
        const fullUrl = getFullPdfUrl(props.pdfUrl);
        console.log('Processing PDF URL:', fullUrl);
        
        // Extract and store original filename
        originalFilename.value = extractFilename(props.pdfUrl);
        console.log('Original filename:', originalFilename.value);
        
        // Set the URL for display
        pdfDisplayUrl.value = fullUrl;
        
        // Set timeout for loading state
        setTimeout(() => {
          if (!loaded.value && loading.value) {
            console.log('PDF load timeout - checking display issues');
            // Try alternative display
            if (useObjectTag.value) {
              useObjectTag.value = false;
              console.log('Switching to iframe display');
            }
            loading.value = false;
          }
        }, 5000);
      } catch (e) {
        console.error('Error setting up PDF viewer:', e);
        handleError(e);
      }
    };
    
    // Signature functionality - kept from your working code
    const activateSignatureMode = () => {
      firstName.value = '';
      lastName.value = '';
      signatureMode.value = true;
      setTimeout(() => {
        if (signatureCanvas.value) {
          initCanvas();
        }
      }, 100);
    };
    
    const initCanvas = () => {
      const canvas = signatureCanvas.value;
      if (!canvas) return;
      
      const ctx = canvas.getContext('2d');
      
      // Set canvas size
      canvas.width = canvas.offsetWidth;
      canvas.height = canvas.offsetHeight;
      
      // Set drawing style
      ctx.strokeStyle = '#000000';
      ctx.lineWidth = 3;
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      
      console.log('Canvas initialized:', canvas.width, canvas.height);
    };
    
    // Drawing functions
    const getCoordinates = (e) => {
      // ...existing code...
      if (!signatureCanvas.value) return { x: 0, y: 0 };
      
      const rect = signatureCanvas.value.getBoundingClientRect();
      let x, y;
      
      if (e.touches && e.touches[0]) {
        x = e.touches[0].clientX - rect.left;
        y = e.touches[0].clientY - rect.top;
      } else {
        x = e.clientX - rect.left;
        y = e.clientY - rect.top;
      }
      
      return { x, y };
    };
    
    const startDrawing = (e) => {
      isDrawing.value = true;
      const coords = getCoordinates(e);
      lastX = coords.x;
      lastY = coords.y;
      console.log('Started drawing at:', lastX, lastY);
    };
    
    const draw = (e) => {
      if (!isDrawing.value || !signatureCanvas.value) return;
      
      const ctx = signatureCanvas.value.getContext('2d');
      const coords = getCoordinates(e);
      
      ctx.beginPath();
      ctx.moveTo(lastX, lastY);
      ctx.lineTo(coords.x, coords.y);
      ctx.stroke();
      
      lastX = coords.x;
      lastY = coords.y;
    };
    
    const stopDrawing = () => {
      isDrawing.value = false;
    };
    
    const clearSignature = () => {
      if (!signatureCanvas.value) return;
      
      const canvas = signatureCanvas.value;
      const ctx = canvas.getContext('2d');
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    };
    
    const cancelSignature = () => {
      signatureMode.value = false;
    };
    
    const saveSignature = async () => {
      if (!signatureCanvas.value) return;
      
      const canvas = signatureCanvas.value;
      const signatureData = canvas.toDataURL('image/png');
      
      // Empty signature check - look for non-white pixels
      const ctx = canvas.getContext('2d');
      const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
      const hasDrawing = Array.from(imageData).some((pixel, index) => {
        // Check for non-transparent pixels (every 4th value is alpha channel)
        return index % 4 === 3 && pixel > 0;
      });
      
      if (!hasDrawing) {
        alert('Please sign the document before saving.');
        return;
      }
      
      // Show loading state
      loading.value = true;
      
      try {
        // Add signature to PDF
        const success = await addSignatureToPdf(signatureData);
        
        if (success) {
          // MODIFIED: Include firstName and lastName in the emitted data
          emit('signature-captured', {
            signature: signatureData,
            firstName: firstName.value,
            lastName: lastName.value
          });
          signatureMode.value = false;
        } else {
          error.value = true;
          errorMessage.value = 'Failed to add signature to PDF';
        }
      } catch (err) {
        console.error('Error saving signature:', err);
        error.value = true;
        errorMessage.value = 'Error processing signature: ' + err.message;
      } finally {
        loading.value = false;
      }
    };
    
    const addSignatureToPdf = async (signatureData) => {
      try {
        // Use the current display URL which we know is properly formatted
        console.log('Fetching PDF from URL:', pdfDisplayUrl.value);
        
        // Fetch the original PDF with appropriate cache control
        const pdfResponse = await fetch(pdfDisplayUrl.value, {
          cache: 'no-store',
          headers: {
            'Cache-Control': 'no-cache',
            'Pragma': 'no-cache',
          }
        });
        
        if (!pdfResponse.ok) {
          console.error(`HTTP error fetching PDF: ${pdfResponse.status}`);
          throw new Error(`HTTP error! Status: ${pdfResponse.status}`);
        }
        
        const pdfBuffer = await pdfResponse.arrayBuffer();
        console.log('PDF loaded, size:', pdfBuffer.byteLength);
        
        if (pdfBuffer.byteLength === 0) {
          throw new Error('Empty PDF file received');
        }
        
        // Load the PDF document
        const pdfDoc = await PDFDocument.load(pdfBuffer);
        const pages = pdfDoc.getPages();
        
        if (pages.length === 0) {
          throw new Error('PDF has no pages');
        }
        
        const lastPage = pages[pages.length - 1];
        
        console.log('Processing signature image');
        // Convert signature data URL to image (strip header)
        const base64Data = signatureData.replace(/^data:image\/(png|jpg);base64,/, '');
        const signatureImage = await pdfDoc.embedPng(base64Data);
        
        // Calculate signature position (bottom of the page)
        const { width, height } = lastPage.getSize();
        const signatureWidth = 200;
        const signatureHeight = 100;
        const signatureX = width / 2 - signatureWidth / 2;
        const signatureY = 50; // Position from bottom
        
        console.log('Adding signature to PDF page');
        // Add signature to the page
        lastPage.drawImage(signatureImage, {
          x: signatureX,
          y: signatureY,
          width: signatureWidth,
          height: signatureHeight,
        });
        
        // NEW: Add first and last name text to the PDF
        if (firstName.value || lastName.value) {
          const fullName = `${firstName.value} ${lastName.value}`.trim();
          if (fullName) {
            lastPage.drawText(fullName, {
              x: signatureX,
              y: signatureY - 20, // Position below signature
              size: 12,
              color: rgb(0, 0, 0), // Black color
            });
          }
        }
        
        console.log('Saving modified PDF');
        // Save the modified PDF
        const modifiedPdfBytes = await pdfDoc.save();
        
        // Create URL for the modified PDF
        const blob = new Blob([modifiedPdfBytes], { type: 'application/pdf' });
        const newUrl = URL.createObjectURL(blob);
        modifiedPdfUrl.value = newUrl;
        
        // Update the viewer to show the modified PDF
        pdfDisplayUrl.value = newUrl;
        console.log('Signature added successfully');
        
        return true;
      } catch (error) {
        console.error('Error adding signature to PDF:', error);
        return false;
      }
    };
    
    // NEW: Method to upload the signed document to work order
    const uploadSignedDocument = async () => {
      try {
        if (!modifiedPdfUrl.value) {
          console.error('No signed document available to upload');
          return;
        }
        
        // Show loading state
        loading.value = true;
        
        // Fetch the signed PDF blob from the URL
        const response = await fetch(modifiedPdfUrl.value);
        const blob = await response.blob();
        
        // Create form data for upload
        const formData = new FormData();
        formData.append('file', blob, signedFilename.value);
        formData.append('firstName', firstName.value);
        formData.append('lastName', lastName.value);
        
        // Add workOrderId to form data if provided
        if (props.workOrderId) {
          formData.append('workOrderId', props.workOrderId);
        }
        
        // Get CSRF token from meta tag for Laravel security
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        console.log('Uploading document', {
          filename: signedFilename.value,
          size: blob.size,
          hasToken: !!token,
          workOrderId: props.workOrderId
        });
        
        // Use the PUBLIC endpoint that doesn't require authentication
        const uploadResponse = await fetch('/api/public/documents/upload-signed', {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-TOKEN': token || '',
            'Accept': 'application/json',
          }
        });
        
        if (!uploadResponse.ok) {
          const errorData = await uploadResponse.text();
          console.error('Upload response:', errorData);
          throw new Error(`Upload failed with status: ${uploadResponse.status}`);
        }
        
        const result = await uploadResponse.json();
        console.log('Upload successful:', result);
        
        // Emit event to notify parent component of upload
        emit('document-uploaded', {
          url: result.url,
          fileName: signedFilename.value,
          firstName: firstName.value,
          lastName: lastName.value,
          workOrderId: props.workOrderId
        });
        
        // Show success message
        alert('Document successfully uploaded to work order!');
        
        // Redirect to work order view if ID is provided and redirectAfterUpload is true
        if (props.workOrderId && props.redirectAfterUpload) {
          // Small delay to ensure the alert is seen
          setTimeout(() => {
            // Use router if available, otherwise do a page redirect
            if (window.Inertia) {
              // For Inertia.js applications
              window.Inertia.visit(`/work-orders/${props.workOrderId}`);
            } else {
              // Regular redirect
              window.location.href = `/work-orders/${props.workOrderId}`;
            }
          }, 500);
        }
        
      } catch (error) {
        console.error('Error uploading signed document:', error);
        alert(`Failed to upload signed document: ${error.message}`);
      } finally {
        loading.value = false;
      }
    };
    
    // Watch for URL changes
    watch(() => props.pdfUrl, (newUrl, oldUrl) => {
      if (newUrl && newUrl !== oldUrl) {
        console.log('PDF URL changed, reloading viewer');
        setupPdfViewer();
      }
    });
    
    // Initialize on mount
    onMounted(() => {
      console.log('PDF viewer component mounted');
      setupPdfViewer();
    });
    
    return {
      loading,
      loaded,
      error,
      errorMessage,
      pdfDisplayUrl,
      useObjectTag,
      signatureCanvas,
      signatureMode,
      modifiedPdfUrl,
      showDebugInfo,
      onPdfLoad,
      handleError,
      closeViewer,
      setupPdfViewer,
      activateSignatureMode,
      startDrawing,
      draw,
      stopDrawing,
      clearSignature,
      cancelSignature,
      saveSignature,
      firstName,
      lastName,
      uploadSignedDocument,
      signedFilename
    };
  }
};
</script>

<style scoped>
.pdf-viewer-outer-container {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.75);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  padding: 20px;
}

.pdf-viewer-container {
  width: 90%;
  height: 90%;
  background-color: white;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
}

.pdf-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  background-color: #000000;
  border-bottom: 1px solid #e9ecef;
}

.pdf-title {
  font-size: 18px;
  font-weight: 500;
  color: #374151;
  margin: 0;
}

.pdf-controls {
  display: flex;
  gap: 8px;
}

.action-button {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  font-size: 14px;
  border-radius: 4px;
  cursor: pointer;
  border: none;
  font-weight: 500;
}

.sign-button {
  background-color: #10B981;
  color: white;
}

.sign-button:hover {
  background-color: #059669;
}

.close-button {
  background-color: #eb0000;
  color: white;
  outline:#059669;
}

.close-button:hover {
  background-color: #DC2626;
}

.pdf-display-container {
  flex: 1;
  position: relative;
  display: flex;
  overflow: hidden;
  background-color: #03122c; /* Light background to see if container is visible */
}

.pdf-display {
  width: 100%;
  height: 100%;
  border: none;
  display: block;
}

/* Debug info panel */
.debug-info {
  position: absolute;
  bottom: 0;
  left: 0;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 8px;
  font-family: monospace;
  font-size: 12px;
  max-width: 400px;
  z-index: 1002;
}

.debug-close {
  background: #333;
  border: none;
  color: white;
  padding: 2px 6px;
  font-size: 10px;
  margin-top: 4px;
  cursor: pointer;
}

/* Rest of your existing styles */
.loading-indicator {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  color: #6B7280;
}

.spinner {
  border: 4px solid rgba(156, 163, 175, 0.3);
  border-radius: 50%;
  border-top: 4px solid #10B981;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  background-color: #FEF2F2;
  border: 1px solid #FECACA;
  border-radius: 8px;
  padding: 24px;
  max-width: 400px;
  color: #EF4444;
}

.error-message h3 {
  font-size: 18px;
  margin-top: 0;
  margin-bottom: 12px;
}

.error-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 16px;
}

.error-link {
  color: #10B981;
  text-decoration: underline;
}

.error-button {
  padding: 8px 16px;
  background-color: #10B981;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.error-button:hover {
  background-color: #059669;
}

.signature-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.75);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10000;
  padding: 20px;
}

.signature-box {
  background-color: rgb(36, 37, 44);
  border-radius: 8px;
  width: 100%;
  max-width: 600px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.signature-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.signature-title {
  font-size: 18px;
  font-weight: 500;
  margin: 0;
  color: #50fd00;
}

.signature-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #d20202;
}

.signature-canvas {
  width: 100%;
  height: 200px;
  border: 2px solid #545454;
  border-radius: 6px;
  touch-action: none;
  background-color: #707070;
}

/* NEW: Styles for the signature input fields */
.signature-inputs {
  display: flex;
  gap: 15px;
  margin: 15px 0;
  background-color: #21252d;
}

.input-group {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.input-group label {
  font-size: 14px;
  font-weight: 500;
  color: #5fdb00;
}

.input-group input {
  padding: 8px 12px;
  border: 1px solid #D1D5DB;
  border-radius: 4px;
  font-size: 14px;
}

.signature-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.signature-btn {
  padding: 10px 16px;
  border-radius: 6px;
  font-weight: 500;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.signature-btn.clear {
  background-color: #F3F4F6;
  border: 1px solid #D1D5DB;
  color: #374151;
}

.signature-btn.clear:hover {
  background-color: #E5E7EB;
}

.signature-btn.cancel {
  background-color: #F3F4F6;
  border: 1px solid #D1D5DB;
  color: #374151;
}

.signature-btn.cancel:hover {
  background-color: #E5E7EB;
}

.signature-btn.save {
  background-color: #5fdb00;
  color: black;
  border: none;
}

.signature-btn.save:hover {
  background-color: #059669;
}

.download-bar {
  padding: 16px 20px;
  background-color: #ECFDF5;
  border-top: 1px solid #A7F3D0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.download-bar p {
  margin: 0;
  color: #047857;
  font-weight: 500;
}

.download-actions {
  display: flex;
  gap: 10px;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 4px;
  text-decoration: none;
  font-weight: 500;
  font-size: 14px;
  cursor: pointer;
}

.download-btn {
  background-color: #10B981;
  color: white;
  border: none;
}

.download-btn:hover {
  background-color: #059669;
}

.upload-btn {
  background-color: #5fdb00;
  color: black;
  border: none;
}

.upload-btn:hover {
  background-color: #5fdb00;
}
</style>