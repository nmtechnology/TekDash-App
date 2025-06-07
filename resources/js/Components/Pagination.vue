<template>
    <div>
        <div v-if="links.length > 3" class="flex flex-wrap -mb-1">
            <template v-for="(link, key) in links" :key="key">
                <div v-if="link.url === null" 
                    class="mr-1 mb-1 px-4 py-2 text-sm text-gray-500 border rounded-md"
                    :class="{ 'ml-auto': link.label === 'Next &raquo;' }"
                    v-html="link.label" />

                <Link v-else
                    :href="link.url"
                    class="mr-1 mb-1 px-4 py-2 text-sm border rounded-md focus:outline-none focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    :class="{
                        'bg-indigo-600 text-white border-indigo-600': link.active,
                        'text-gray-700 hover:bg-gray-50': !link.active,
                        'ml-auto': link.label === 'Next &raquo;'
                    }"
                    v-html="link.label" />
            </template>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>
