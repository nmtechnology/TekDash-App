<script setup>
import { ref } from 'vue';
import SectionTitle from '@/Components/SectionTitle.vue';
import ActionSection from '@/Components/ActionSection.vue';

const props = defineProps({
    team: Object,
    permissions: Object,
});

const documents = ref(props.team.documents || []);
</script>

<template>
    <ActionSection>
        <template #title>
            Team Documents & Images
        </template>

        <template #description>
            View and manage team documents and images.
        </template>

        <template #content>
            <div class="space-y-6">
                <div v-if="documents.length === 0" class="text-gray-500">
                    No documents or images uploaded yet.
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="doc in documents" :key="doc.id" class="p-4 border rounded">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <h4 class="font-medium">{{ doc.name }}</h4>
                                <p class="text-sm text-gray-500">{{ doc.created_at }}</p>
                            </div>
                            <a :href="doc.url" target="_blank" 
                               class="inline-flex items-center px-4 py-2 bg-lime-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-lime-600">
                                View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </ActionSection>
</template>
