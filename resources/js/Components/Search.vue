<script setup>
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
  placeholder: {
    type: String,
    default: 'Search...'
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['search']);

const searchQuery = ref('');

// Debounce the search to avoid too many search events
const debouncedSearch = debounce((query) => {
  emit('search', query);
}, 300);

// Watch for changes to the search query
watch(searchQuery, (newQuery) => {
  debouncedSearch(newQuery);
});

// Clear the search
function clearSearch() {
  searchQuery.value = '';
  emit('search', '');
}
</script>

<template>
  <div class="search-container responsive-search">
    <label class="input input-bordered flex items-center gap-2 w-full">
      <div v-if="loading" class="loading loading-spinner loading-xs"></div>
      <svg v-else class="h-[1em] opacity-50 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <g
          stroke-linejoin="round"
          stroke-linecap="round"
          stroke-width="2.5"
          fill="none"
          stroke="currentColor"
        >
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.3-4.3"></path>
        </g>
      </svg>
      <input
        v-model="searchQuery"
        type="search"
        class="grow min-w-0 search-input"
        :placeholder="placeholder"
        :disabled="loading"
      />
      <div class="flex items-center gap-1 opacity-50 flex-shrink-0">
        <kbd class="kbd kbd-xs">⌘</kbd>
        <kbd class="kbd kbd-xs">K</kbd>
      </div>
    </label>
  </div>
</template>

<style scoped>
.search-container {
  width: 100%;
  position: relative;
  margin-bottom: 0.5rem;
}

.input {
  width: 100%;
  height: 2.25rem;
  min-height: 2.25rem;
  background-color: rgba(17, 24, 39, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #f3f4f6;
  transition: all 0.2s ease;
  padding: 0.5rem;
}

.input:focus-within {
  border-color: rgba(163, 230, 53, 0.5);
  box-shadow: 0 0 0 2px rgba(163, 230, 53, 0.2);
}

.search-input {
  background: transparent;
  border: none;
  outline: none;
  color: #f3f4f6;
  font-size: 0.95rem;
  text-overflow: ellipsis;
  min-width: 0;
  width: 100%;
  padding: 0.25rem 0.5rem;
}

input::placeholder,
.search-input::placeholder {
  color: rgba(243, 244, 246, 0.5);
}

.kbd {
  background-color: rgba(31, 41, 55, 0.8);
  border: 1px solid rgba(243, 244, 246, 0.2);
  color: rgba(243, 244, 246, 0.7);
  font-size: 0.65rem;
  padding: 0.1rem 0.25rem;
  height: 1.25rem;
  min-width: 1.25rem;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Responsive styles for mobile and desktop */
.responsive-search {
  max-width: 600px;
  margin: 0 auto;
}

@media (max-width: 600px) {
  .responsive-search {
    max-width: 100vw;
    padding: 0 0.5rem;
  }
  .input {
    height: 2rem;
    min-height: 2rem;
    padding: 0.25rem;
  }
  .search-input {
    font-size: 0.85rem;
    padding: 0.15rem 0.25rem;
  }
  .kbd {
    font-size: 0.55rem;
    height: 1rem;
    min-width: 1rem;
    padding: 0.05rem 0.15rem;
  }
}
</style>
