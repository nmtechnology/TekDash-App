<script setup>
import { ref } from 'vue';
// ...existing code...

const processing = ref(false);
const createInvoice = async () => {
    processing.value = true;
    try {
        await axios.post(`/api/work-orders/${props.workOrder.id}/invoice`, {}, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        toast.success('Invoice created successfully');
        router.visit(route('work-orders.show', props.workOrder.id));
    } catch (error) {
        console.error('Error creating invoice:', error);
        toast.error('Failed to create invoice');
    } finally {
        processing.value = false;
    }
};

// ...existing code...
</script>

<template>
    // ...existing code...
    <button 
        @click="createInvoice" 
        :disabled="processing"
        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition"
    >
        <span v-if="processing">Creating...</span>
        <span v-else>Create Invoice</span>
    </button>
    // ...existing code...
</template>
