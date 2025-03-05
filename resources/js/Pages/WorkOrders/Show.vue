<script setup>
import { ref } from 'vue';
import QRCode from 'qrcode.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { DocumentIcon, PhotoIcon, DocumentTextIcon, QrCodeIcon } from '@heroicons/vue/24/outline';
import Messenger from '@/Components/Messenger.vue';

/**
 * @typedef {Object} WorkOrder
 * @property {number} id
 * @property {string} order_number
 * @property {'pending'|'in_progress'|'completed'} status
 * @property {string} description
 * @property {Array<{id: number, url: string}>} [images]
 * @property {Array<{id: number, url: string, name: string}>} [documents]
 */
const props = defineProps({
    workOrder: Object,
    users: {
        type: Array,
        default: () => []
    }
});

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

const getFileType = (filename) => {
    if (!filename) return 'unknown';
    const ext = filename.split('.').pop().toLowerCase();
    return ext === 'pdf' ? 'pdf' : 'document';
};

const getFileIcon = (type) => {
    return type === 'pdf' ? DocumentTextIcon : DocumentIcon;
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
};

const openImage = (url) => {
    window.open(url, '_blank');
};

// User-related functions
const getUserName = (userId) => {
    return userId; // Since we're already passing the name directly
};

const getUserAvatar = (userId) => {
    // Return a default avatar or find user's avatar from users prop
    return '/default-avatar.png';
};

 // Enhanced getStatusClasses function to match the color scheme in the example
 const getStatusClasses = (status) => {
      if (!status) return 'bg-gray-800 text-gray-300 ring-gray-700';
      
      const statusLower = status.toLowerCase();
      
      if (statusLower.includes('complete')) {
        return 'bg-green-800 text-green-100 ring-green-700';
      } else if (statusLower.includes('scheduled')) {
        return 'bg-blue-800 text-blue-100 ring-blue-700';
      } else if (statusLower.includes('progress')) {
        return 'bg-yellow-800 text-yellow-100 ring-yellow-700';
      } else if (statusLower.includes('cancel')) {
        return 'bg-red-800 text-red-100 ring-red-700';
      } else if (statusLower.includes('part') || statusLower.includes('return')) {
        return 'bg-purple-800 text-purple-100 ring-purple-700';
      }
      
      return 'bg-gray-800 text-gray-300 ring-gray-700';
    };

// Add this computed value for the QR code URL
const qrCodeUrl = window.location.origin + route('work-orders.show', props.workOrder.id);

const qrSize = ref(100); // Size of QR code in pixels
// style the qr code
const qrStyle = ref({
    width: '100%',
    height: '100%',
    background: '#161b28',
    color: '#57fd00'
});

// Add these functions for attachment handling
const getFilePreview = (doc) => {
    const ext = doc.name?.split('.').pop().toLowerCase() || '';
    if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
        return doc.url;
    } else if (ext === 'pdf') {
        return '/images/pdf-preview.png'; // Add a PDF preview image to your public folder
    }
    return '/images/document-preview.png'; // Add a default document preview image
};

const isImage = (doc) => {
    const ext = doc.name?.split('.').pop().toLowerCase() || '';
    return ['jpg', 'jpeg', 'png', 'gif'].includes(ext);
};
</script>

<template>
    <AppLayout title="Work Order Details">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Main Content -->
                <div class="bg-gray-900 overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <!-- Header Section with QR Code -->
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-300">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-4xl leading-6 font-medium text-lime-400">
                                    {{ workOrder.title }}
                                </h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                    Details and attachments for this work order
                                </p>
                            </div>
                            <!-- QR Code Section -->
                            <div class="flex flex-col items-center">
                                <div class="bg-white p-2 rounded-lg">
                                    <QRCode
                                        :value="qrCodeUrl"
                                        :style="qrStyle"
                                        :size="qrSize"
                                        level="H"
                                        render-as="svg"
                                    />
                                </div>
                                <span class="text-xs text-gray-400 mt-2">Scan to view on mobile</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-5 sm:p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Work Order Details -->
                            <div>
                                <div class="bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-lg">
                                    <dt class="text-sm font-medium text-purple-500">Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <span 
                                            class="ml-3 inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                            :class="getStatusClasses(workOrder.status)"
                                             >
                                                {{ workOrder.status }}
                                        </span>
                                    </dd>
                                </div>

                                <div class="mt-4 bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-lg">
                                    <dt class="text-sm font-medium text-purple-500">Title</dt>
                                    <dd class="mt-1 text-sm text-blue-400 sm:mt-0 sm:col-span-2">
                                        {{ workOrder.title }}
                                    </dd>
                                </div>
                                
                                <div class="mt-4 bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-lg">
                                    <dt class="text-sm font-medium text-purple-500">Date</dt>
                                    <dd class="mt-1 text-sm text-blue-400 sm:mt-0 sm:col-span-2">
                                        {{ workOrder.date_time }}
                                    </dd>
                                </div>
                               
                                <div class="mt-4 bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-lg">
                                    <dt class="text-sm font-medium text-purple-500">Price</dt>
                                    <dd class="mt-1 text-sm text-blue-400 sm:mt-0 sm:col-span-2">
                                        {{ formatPrice(workOrder.price) }}
                                    </dd>
                                </div>

                                <div class="mt-4 bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-lg">
                                    <dt class="text-sm font-medium text-purple-500">Description</dt>
                                    <dd class="mt-1 text-sm text-blue-400 sm:mt-0 sm:col-span-2">
                                        {{ workOrder.description }}
                                    </dd>
                                </div>
                            </div>

                            <!-- Updated Attachments Section -->
                            <div class="bg-gray-800 p-4 rounded-lg">
                                <h4 class="text-lg font-medium text-blue-400 mb-4">Attachments</h4>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <template v-for="doc in workOrder.documents" :key="doc.id">
                                        <!-- DaisyUI Card Component -->
                                        <div class="card bg-base-100 shadow-xl">
                                            <!-- Preview Image -->
                                            <figure class="px-4 pt-4">
                                                <img 
                                                    :src="getFilePreview(doc)" 
                                                    :alt="doc.name"
                                                    class="rounded-xl h-32 w-full object-cover"
                                                    @click="isImage(doc) && openImage(doc.url)"
                                                    :class="{ 'cursor-pointer': isImage(doc) }"
                                                />
                                            </figure>
                                            
                                            <div class="card-body p-4">
                                                <h2 class="card-title text-sm text-gray-200">
                                                    {{ doc.name }}
                                                </h2>
                                                
                                                <div class="card-actions justify-end mt-2">
                                                    <a 
                                                        :href="doc.url" 
                                                        target="_blank" 
                                                        class="btn btn-primary btn-sm"
                                                    >
                                                        View
                                                    </a>
                                                    <button 
                                                        class="btn btn-ghost btn-sm"
                                                        @click="downloadFile(doc.url, doc.name)"
                                                    >
                                                        Download
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- No Attachments Message -->
                                <div v-if="!workOrder.documents?.length" class="text-center py-8">
                                    <div class="text-gray-500">
                                        <DocumentIcon class="h-12 w-12 mx-auto mb-2" />
                                        <p>No attachments available</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 flex justify-end">
                            <button 
                                @click="createInvoice" 
                                :disabled="processing"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition"
                            >
                                <span v-if="processing">Creating...</span>
                                <span v-else>Create Invoice</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages Section -->
                <div class="bg-gray-900 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-700">
                        <h3 class="text-lg leading-6 font-medium text-blue-400">
                            Messages
                        </h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <Messenger 
                        :initialNotes="workOrder.notes" 
                        :workOrderId="workOrder.id"
                        :userId="$page.props.auth.user.name"
                        :getUserName="getUserName"
                        :getUserAvatar="getUserAvatar"
                        :currentUserAvatar="$page.props.auth.user.profile_photo_url"
                            class="bg-gray-800 rounded-lg p-4"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.card {
    @apply transition-all duration-200;
}

.card:hover {
    @apply transform -translate-y-1;
}

.card figure img {
    @apply transition-opacity duration-200;
}

.card figure img:hover {
    @apply opacity-80;
}
</style>
