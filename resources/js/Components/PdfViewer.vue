<template>
  <div class="pdf-viewer-outer-container">
    <!-- Add floating close button -->
    <button class="floating-close-btn" @click.stop="closeViewer" title="Close PDF Viewer">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
      </svg>
    </button>
    
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
              <path d="M11.5 0h-7A1.5 1.5 0 0 0 3 1.5v13A1.5 1.5 0 0 0 4.5 16h7a1.5.5 0 0 1 11.5 0zm0 1a.5.5 0 0 1 .5.5v13a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-13a.5.5 0 0 1 .5-.5h7z"/>
            </svg>
            Sign Document
          </button>
          <button class="action-button close-button" @click.stop="closeViewer">
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
      <div 
        v-if="signatureMode" 
        class="signature-overlay" 
        @click.self="cancelSignature"
      >
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
            @touchstart.passive="startDrawing"
            @touchmove.passive="draw"
            @touchend="stopDrawing"
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
          <button 
            @click="finishAndClose" 
            class="action-btn save-btn"
          >
            Save & Close
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
import { ref, onMounted, watch, computed, onUnmounted } from 'vue';
import { PDFDocument, rgb } from 'pdf-lib';
import { useToast } from '@/Composables/useToast';

export default {
  name: 'PdfViewer',
  
  emits: ['close', 'update:modelValue', 'document-uploaded'],
  
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
    },
    // Add workOrderTitle prop for naming the signed document
    workOrderTitle: {
      type: String,
      default: ''
    },
    modelValue: {
      type: Boolean,
      default: false
    },
    showThumbnail: {
      type: Boolean,
      default: true
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
    
    // Helper function to sanitize filename (remove invalid characters)
    const sanitizeFilename = (filename) => {
      // Replace invalid file name characters with underscores
      return filename
        .replace(/[<>:"/\\|?*\x00-\x1F]/g, '_') // Replace invalid characters
        .replace(/\s+/g, '_')                    // Replace spaces with underscores
        .replace(/__+/g, '_')                    // Replace multiple underscores with single
        .trim();
    };
    
    // Compute the signed filename using work order title if available
    const signedFilename = computed(() => {
      if (props.workOrderTitle) {
        // Use work order title as the filename base
        const sanitizedTitle = sanitizeFilename(props.workOrderTitle);
        return `${sanitizedTitle}_signed.pdf`;
      } else {
        // Fall back to original filename if available
        const baseName = originalFilename.value.replace(/\.pdf$/i, '');
        return `${baseName}_signed.pdf`;
      }
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
          try {
            setupPdfViewer();
          } catch (fallbackError) {
            console.error('Error in fallback PDF display:', fallbackError);
            showError('Unable to display PDF document');
          }
        }, 500);
      }
    };
    
    const closeViewer = () => {
      if (modifiedPdfUrl.value) {
        URL.revokeObjectURL(modifiedPdfUrl.value);
        modifiedPdfUrl.value = null;
      }
      
      loading.value = false;
      error.value = false;
      signatureMode.value = false;
      
      // Emit close event
      emit('close');
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
    
    const isSignatureValid = () => {
      if (!signatureCanvas.value) return false;
      
      const canvas = signatureCanvas.value;
      const ctx = canvas.getContext('2d');
      const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
      
      // Check if the signature has enough pixels drawn
      let pixelCount = 0;
      for (let i = 0; i < imageData.length; i += 4) {
        // If alpha channel is non-zero, it's a drawn pixel
        if (imageData[i + 3] > 0) {
          pixelCount++;
        }
      }
      
      // Ensure signature has a minimum number of pixels (adjust threshold as needed)
      const minPixels = 100; 
      if (pixelCount < minPixels) {
        showError('Your signature is too small. Please sign with a more visible mark.');
        return false;
      }
      
      // Ensure name fields are filled
      if (!firstName.value.trim()) {
        warning('Please enter your first name');
        return false;
      }
      
      if (!lastName.value.trim()) {
        warning('Please enter your last name');
        return false;
      }
      
      return true;
    };
    
    const saveSignature = async () => {
      if (!signatureCanvas.value || !props.workOrderId) return;
      
      // Validate signature using our helper method
      if (!isSignatureValid()) return;
      
      loading.value = true;
      info('Processing signature...');
      
      try {
        const canvas = signatureCanvas.value;
        const signatureData = canvas.toDataURL('image/png');
        
        // Validate signature
        const ctx = canvas.getContext('2d');
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
        const hasDrawing = Array.from(imageData).some((pixel, index) => {
          return index % 4 === 3 && pixel > 0;
        });
        
        if (!hasDrawing) {
          showError('Please sign the document before saving.');
          loading.value = false;
          return;
        }
        
        // Validate name fields
        if (!firstName.value.trim() || !lastName.value.trim()) {
          warning('Please enter both first and last name.');
          loading.value = false;
          return;
        }
        
        // Add signature to PDF
        const success = await addSignatureToPdf(signatureData);
          
        if (success) {
          try {
            // Create form data for upload
            const formData = new FormData();
            
            // Convert the modified PDF to a Blob
            const response = await fetch(modifiedPdfUrl.value);
            const pdfBlob = await response.blob();
            
            // Verify blob size and type
            if (pdfBlob.size === 0) {
              throw new Error('Generated PDF is empty');
            }
            
            // Generate timestamp for the signature
            const now = new Date();
            const timestamp = now.toISOString();
            const formattedTimestamp = now.toLocaleString('en-US', {
              weekday: 'short',
              month: 'short', 
              day: 'numeric',
              year: 'numeric',
              hour: 'numeric',
              minute: 'numeric',
              hour12: true
            });
            
            // Add the PDF file and metadata to form data
            formData.append('file', new File([pdfBlob], signedFilename.value, { type: 'application/pdf' }));
            formData.append('work_order_id', props.workOrderId);
            formData.append('metadata', JSON.stringify({
              firstName: firstName.value,
              lastName: lastName.value,
              timestamp: timestamp,
              formattedTimestamp: formattedTimestamp,
              hasSignature: true,
              originalFilename: originalFilename.value,
              workOrderId: props.workOrderId,
              workOrderTitle: props.workOrderTitle
            }));
            
            // Add CSRF token if needed
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Determine the API endpoint based on workOrderId
            const uploadEndpoint = props.workOrderId 
              ? `/work-orders/${props.workOrderId}/attachments` 
              : '/api/documents/upload';
            
            console.log(`Uploading signed document to ${uploadEndpoint}`);
            
            // Upload the signed document
            const uploadResponse = await fetch(uploadEndpoint, {
              method: 'POST',
              body: formData,
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                ...(token ? { 'X-CSRF-TOKEN': token } : {})
              },
              credentials: 'same-origin'
            });
            
            if (!uploadResponse.ok) {
              const errorData = await uploadResponse.json().catch(() => ({}));
              throw new Error(errorData.message || `Upload failed with status: ${uploadResponse.status}`);
            }
            
            const result = await uploadResponse.json();
            
            // Emit success event with upload result
            emit('document-uploaded', {
              path: result.path || (result.attachments && result.attachments[0]?.url) || '',
              success: true,
              fileName: signedFilename.value,
              signed: true,
              signature: {
                firstName: firstName.value,
                lastName: lastName.value,
                timestamp: timestamp,
                formattedTimestamp: formattedTimestamp
              }
            });
            
            signatureMode.value = false;
            loading.value = false;
            
            // Add download bar when the PDF is ready
            modifiedPdfUrl.value = URL.createObjectURL(pdfBlob);
            
            // Show success toast notification
            success(`Document signed successfully by ${firstName.value} ${lastName.value}`);
            
            if (props.redirectAfterUpload && props.workOrderId) {
              setTimeout(() => {
                window.location.href = `/work-orders/${props.workOrderId}`;
              }, 2000);
            }
          } catch (uploadError) {
            console.error('Upload error:', uploadError);
            showError(`Failed to upload signed document: ${uploadError.message}`);
            loading.value = false;
          }
        } else {
          showError('Failed to add signature to PDF');
          loading.value = false;
        }
      } catch (err) {
        console.error('Error saving signature:', err);
        error.value = true;
        errorMessage.value = err.message || 'Error saving signed document';
        loading.value = false;
        showError(errorMessage.value);
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
            'If-None-Match': '',
            'If-Modified-Since': ''
          }
        });
        
        // Check if the fetch was successful
        if (!pdfResponse.ok) {
          throw new Error(`Failed to fetch PDF: ${pdfResponse.status} ${pdfResponse.statusText}`);
        }
        
        // Get the PDF as an ArrayBuffer
        const pdfBuffer = await pdfResponse.arrayBuffer();
        
        // Load the PDF document using pdf-lib
        const pdfDoc = await PDFDocument.load(pdfBuffer);
        
        // Embed the signature image
        const signatureImage = await pdfDoc.embedPng(signatureData);
        
        // Get the dimensions of the signature image
        const { width, height } = signatureImage.scale(0.5);
        
        // Get the first page of the document
        const pages = pdfDoc.getPages();
        if (pages.length === 0) {
          throw new Error('PDF document has no pages');
        }
        
        // Generate current timestamp
        const timestamp = new Date().toLocaleString();
        
        // Draw signature on the last page (where signatures usually go)
        const lastPage = pages[pages.length - 1];
        const { width: pageWidth, height: pageHeight } = lastPage.getSize();
        
        // Calculate signature position (centered horizontally, near the bottom)
        const signatureX = (pageWidth - width) / 2;
        const signatureY = 100; // Position from the bottom of the page
        
        // Draw the signature image
        lastPage.drawImage(signatureImage, {
          x: signatureX,
          y: signatureY,
          width,
          height
        });
        
        // Add the name text below the signature
        const fullName = `${firstName.value} ${lastName.value}`;
        lastPage.drawText(fullName, {
          x: signatureX + (width / 2) - 50,
          y: signatureY - 20,
          size: 12,
          color: rgb(0, 0, 0)
        });
        
        // Add the timestamp below the name
        lastPage.drawText(`Signed: ${timestamp}`, {
          x: signatureX + (width / 2) - 60,
          y: signatureY - 40,
          size: 10,
          color: rgb(0.5, 0.5, 0.5)
        });
        
        // Check for form fields in the document and fill them if present
        const form = pdfDoc.getForm();
        const fields = form.getFields();
        
        // Log fields for debugging
        console.log('PDF form fields:', fields.map(field => field.getName()));
        
        // Try to fill common signature fields if they exist
        fields.forEach(field => {
          const fieldName = field.getName().toLowerCase();
          
          try {
            // Handle different types of fields
            if (field instanceof PDFTextField) {
              if (fieldName.includes('signature') || fieldName.includes('sign')) {
                field.setText(fullName);
              } else if (fieldName.includes('name') && fieldName.includes('first')) {
                field.setText(firstName.value);
              } else if (fieldName.includes('name') && fieldName.includes('last')) {
                field.setText(lastName.value);
              } else if (fieldName.includes('date') || fieldName.includes('time')) {
                field.setText(timestamp);
              }
            }
          } catch (fieldError) {
            console.warn(`Could not set field ${fieldName}:`, fieldError);
          }
        });
        
        // Save the form if changes were made
        if (fields.length > 0) {
          form.flatten(); // Optional: make form fields non-editable
          info('PDF form fields filled with signature information');
        }
        
        // Serialize the PDF to bytes
        const modifiedPdfBytes = await pdfDoc.save();
        
        // Create a blob URL for the modified PDF
        const blob = new Blob([modifiedPdfBytes], { type: 'application/pdf' });
        modifiedPdfUrl.value = URL.createObjectURL(blob);
        
        // Return success
        return true;
      } catch (error) {
        console.error('Error adding signature to PDF:', error);
        errorMessage.value = `Failed to add signature to PDF: ${error.message}`;
        error.value = true;
        return false;
      }
    };
    
    const finishAndClose = () => {
      emit('document-uploaded');
      closeViewer();
      
      if (props.redirectAfterUpload && props.workOrderId) {
        window.location.href = `/work-orders/${props.workOrderId}`;
      }
    };

    const handleThumbnailClick = () => {
      setupPdfViewer();
      emit('update:modelValue', true);
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
      
      // Add keyboard event listener
      window.addEventListener('keydown', handleKeyDown);
    });
    
    // Clean up on unmount
    onUnmounted(() => {
      window.removeEventListener('keydown', handleKeyDown);
    });
    
    // Add keyboard support for closing with Escape key
    const handleKeyDown = (e) => {
      if (e.key === 'Escape') {
        closeViewer();
      }
    };

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
      signedFilename,
      sanitizeFilename,
      finishAndClose,
      handleThumbnailClick
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

/* Add floating close button styles */
.floating-close-btn {
  position: fixed;
  top: 15px;
  right: 15px;
  z-index: 10001; /* Above everything else */
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: rgba(0, 0, 0, 0.7);
  color: white;
  border: 2px solid white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
  transition: all 0.2s ease;
}

.floating-close-btn:hover {
  background-color: #ff0000;
  transform: scale(1.1);
}

.floating-close-btn:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(255, 0, 0, 0.5);
}

.save-btn {
  background-color: #5fdb00;
  color: black;
  border: none;
}

.save-btn:hover {
  background-color: #4caf00;
}
</style>