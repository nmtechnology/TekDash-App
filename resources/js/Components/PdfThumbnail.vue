<template>
  <div class="pdf-thumbnail-wrapper">
    <div class="pdf-thumbnail" @click="handleClick">
      <canvas 
        ref="canvas" 
        class="thumbnail-canvas"
        :style="{ display: thumbnailGenerated ? 'block' : 'none' }"
      ></canvas>
      <div class="pdf-icon" v-if="!thumbnailGenerated">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
          <path d="M320 464c8.8 0 16-7.2 16-16V160H256c-17.7 0-32-14.3-32-32V48H64c-8.8 0-16 7.2-16 16V448c0 8.8 7.2 16 16 16H320zM0 64C0 28.7 28.7 0 64 0H229.5c17 0 33.3 6.7 45.3 18.7l90.5 90.5c12 12 18.7 28.3 18.7 45.3V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V64z"/>
        </svg>
      </div>
      <div class="filename">{{ truncatedFilename }}</div>
      <button class="delete-button" @click.stop="handleDelete">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
          <path d="M3 6h18M9 6v12m3-12v12m3-12v12m3-12v12M3 6h18M3 6l3 15h12l3-15M9 6h6"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import * as pdfjsLib from 'pdfjs-dist/build/pdf';
import pdfjsWorker from 'pdfjs-dist/build/pdf.worker?url';

// Set the worker source to the local worker file via Vite
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

    const truncatedFilename = computed(() => {
      const maxLength = 20;
      if (props.filename.length <= maxLength) return props.filename;
      return props.filename.substring(0, maxLength - 3) + '...';
    });

    const generateThumbnail = async () => {
      try {
        const loadingTask = pdfjsLib.getDocument({
          url: props.pdfUrl,
        });
        const pdf = await loadingTask.promise;
        const page = await pdf.getPage(1);
        
        const viewport = page.getViewport({ scale: 0.3 });
        const context = canvas.value.getContext('2d');
        
        canvas.value.width = viewport.width;
        canvas.value.height = viewport.height;

        await page.render({
          canvasContext: context,
          viewport: viewport
        }).promise;

        thumbnailGenerated.value = true;
      } catch (error) {
        console.error('Error generating PDF thumbnail:', error);
        thumbnailGenerated.value = false;
      }
    };

    const handleClick = () => {
      emit('click', { url: props.pdfUrl, filename: props.filename });
    };

    const handleDelete = () => {
      emit('delete', props.pdfUrl);
    };

    onMounted(() => {
      generateThumbnail();
    });

    return {
      canvas,
      thumbnailGenerated,
      truncatedFilename,
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
}

.pdf-icon svg {
  width: 80px;
  height: 60px;
  fill: #9dff00e9;
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

.delete-button {
  width: 24px;
  height: 24px;
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
