<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import GroqQuery from '@/Pages/GroqQuery.vue';
import Calendar from './Calendar.vue';
import Index from '@/Pages/WorkOrders/Index.vue';
</script>

<template>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-16">
        <div class="p-6 lg:p-8 bg-opacity-75 border-b border-blue-400">
            <h1 class="mt-8 text-2xl font-medium text-white">
                Welcome to TekDash {{ $page.props.auth.user.name }}!
            </h1>
            <p class="mt-6 text-lime-400 leading-relaxed">
               <Calendar /><!-- Add any additional welcome text here -->
            </p>
        </div>

        <div class="bg-gray-800 bg-opacity-65 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8 rounded-lg top-1">
            <div>
                <div class="flex items-center">
                   <GroqQuery /> <!-- Add any additional content here -->
                   </div>
               
            </div>
        </div>
    </div>
</template>