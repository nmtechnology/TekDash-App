<template>
    <select
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value)"
        ref="select"
    >
        <slot></slot>
    </select>
</template>

<script>
export default {
    props: ['modelValue'],
    
    emits: ['update:modelValue'],

    methods: {
        focus() {
            this.$refs.select.focus()
        }
    }
}
</script>