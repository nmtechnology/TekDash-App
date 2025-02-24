<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
</script>

<template>
    <AppLayout class="" title="Dashboard">
        <div class="pt-16"> <!-- Adjusted padding to account for the fixed nav -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="relative bg-transparent opacity-75 overflow-hidden shadow-xl sm:rounded-lg bg-cover bg-center bg-no-repeat" style="background-image: url('https://images.unsplash.com/photo-1576400883215-7083980b6193?q=80&w=4113&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');">
                    <div class="absolute inset-0 bg-black opacity-50"></div> <!-- Overlay -->
                    <div class="relative z-10">
                        <Welcome />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Additional styles if needed */
</style>