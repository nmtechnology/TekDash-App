<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { DocumentIcon, PhotoIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';
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

</script>

<template>
    <AppLayout title="Work Order Details">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Main Content -->
                <div class="bg-gray-900 overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <!-- Header Section -->
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-300">
                        <h3 class="text-lg leading-6 font-medium text-lime-400">
                            Work Order #{{ workOrder.title }}
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Details and attachments for this work order
                        </p>
                    </div>

                    <div class="px-4 py-5 sm:p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Work Order Details -->
                            <div>
                                <div class="bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 rounded-lg">
                                    <dt class="text-sm font-medium text-purple-500">Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full"
                                              :class="{
                                                  'bg-green-100 text-green-800': workOrder.status === 'completed',
                                                  'bg-yellow-100 text-yellow-800': workOrder.status === 'in_progress',
                                                  'bg-gray-100 text-gray-800': workOrder.status === 'pending'
                                              }">
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

                            <!-- Attachments Section -->
                            <div class="bg-gray-800 p-4 rounded-lg">
                                <h4 class="text-lg font-medium text-blue-400 mb-4">Attachments</h4>
                                
                                <!-- Images -->
                                <div v-if="workOrder.images?.length" class="mb-6">
                                    <h5 class="text-sm font-medium text-limepurple mb-3">Images</h5>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                        <div v-for="image in workOrder.images" :key="image.id" 
                                             class="relative group">
                                            <img :src="image.url" 
                                                 class="h-24 w-24 object-cover rounded-lg shadow-sm hover:opacity-75 transition"
                                                 @click="openImage(image.url)" />
                                            <PhotoIcon class="h-6 w-6 absolute top-2 right-2 text-white opacity-75" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Documents -->
                                <div v-if="workOrder.documents?.length">
                                    <h5 class="text-sm font-medium text-gray-700 mb-3">Documents</h5>
                                    <div class="space-y-2">
                                        <a v-for="doc in workOrder.documents" :key="doc.id"
                                           :href="doc.url"
                                           target="_blank"
                                           class="flex items-center p-3 bg-white rounded-lg hover:bg-gray-50 transition">
                                            <component 
                                                :is="getFileIcon(getFileType(doc.name))" 
                                                class="h-6 w-6 mr-3"
                                                :class="{'text-red-400': getFileType(doc.name) === 'pdf', 'text-gray-400': getFileType(doc.name) !== 'pdf'}" 
                                            />
                                            <div class="flex flex-col">
                                                <span class="text-sm text-gray-900">{{ doc.name }}</span>
                                                <span v-if="getFileType(doc.name) === 'pdf'" class="text-xs text-gray-500">
                                                    PDF Document
                                                </span>
                                            </div>
                                        </a>
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
                            :work-order-id="workOrder.id"
                            :initial-notes="workOrder.notes"
                            :user-id="$page.props.auth.user.id"
                            :user-avatar="$page.props.auth.user.profile_photo_url"
                            class="bg-gray-800 rounded-lg p-4"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
