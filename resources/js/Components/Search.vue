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
  <label class="input input-success input-bordered flex items-center gap-2">
    <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
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
      class="grow"
      :placeholder="placeholder"
    />
    <kbd class="kbd kbd-sm">⌘</kbd>
    <kbd class="kbd kbd-sm">K</kbd>
  </label>
</template>
