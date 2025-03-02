<script setup>
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
  placeholder: {
    type: String,
    default: 'Search2...'
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
  <div class="relative">
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
      <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
      </svg>
    </div>
    <input
      v-model="searchQuery"
      type="text"
      :placeholder="placeholder"
      class="block w-full rounded-md border-0 py-1.5 pl-10 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:placeholder:text-gray-400 dark:focus:ring-lime-500 sm:text-sm sm:leading-6"
    />
    <button 
      v-if="searchQuery" 
      @click="clearSearch"
      class="absolute inset-y-0 right-0 flex items-center pr-3"
    >
      <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
      </svg>
    </button>
  </div>
</template>
