<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteTeamForm from '@/Pages/Teams/Partials/DeleteTeamForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TeamMemberManager from '@/Pages/Teams/Partials/TeamMemberManager.vue';
import UpdateTeamNameForm from '@/Pages/Teams/Partials/UpdateTeamNameForm.vue';
import TeamDocumentManager from '@/Pages/Teams/Partials/TeamDocumentManager.vue';

defineProps({
    team: Object,
    availableRoles: Array,
    permissions: Object,
});
</script>

<template>
    <AppLayout title="Team Settings">
        <template #header>
            <h2 class="font-semibold text-xl text-lime-500 leading-tight">
                Team Settings
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <UpdateTeamNameForm :team="team" :permissions="permissions" />

                <SectionBorder />

                <TeamMemberManager
                    class="mt-10 sm:mt-0"
                    :team="team"
                    :available-roles="availableRoles"
                    :user-permissions="permissions"
                />

                <SectionBorder />

                <TeamDocumentManager
                    class="mt-10 sm:mt-0"
                    :team="team"
                    :permissions="permissions"
                >
                    <template #documents>
                        <div v-if="team.attachments && team.attachments.length > 0" class="mt-6">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div v-for="attachment in team.attachments" :key="attachment.id" 
                                     class="relative p-4 bg-white rounded-lg shadow">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ attachment.name }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ attachment.created_at }}
                                            </p>
                                        </div>
                                        <a :href="attachment.url" 
                                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-lime-600 hover:text-lime-700"
                                           target="_blank">
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-gray-500 mt-4">No attachments available.</p>
                    </template>
                </TeamDocumentManager>

                <template v-if="permissions.canDeleteTeam && ! team.personal_team">
                    <SectionBorder />

                    <DeleteTeamForm class="mt-10 sm:mt-0" :team="team" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
