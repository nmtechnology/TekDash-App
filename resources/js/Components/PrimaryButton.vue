<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center px-4 py-2 outline hover:text-gray-900 border border-transparent rounded-md font-semibold text-lime-400 tracking-wide hover:bg-lime-400 active:bg-lime-500 focus:outline-none focus:ring-2 focus:ring-lime-400 focus:ring-offset-2 transition ease-in-out duration-150">
        <slot />
    </button>
</template>
