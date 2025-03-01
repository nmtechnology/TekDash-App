<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

import Search from '@/Components/Search.vue';
import axios from 'axios';

defineProps({
    title: String,
});

// Search functionality variables
const searchQuery = ref('');
const workOrders = ref([]);
const isSearching = ref(false);
const showSearchResults = ref(false);
const selectedWorkOrder = ref(null);
const showWorkOrderModal = ref(false);
const showingNavigationDropdown = ref(false);

// Work order search functionality with better error handling
let searchTimeout;
function handleSearch(query) {
    searchQuery.value = query;
    clearTimeout(searchTimeout);
    
    if (query.length < 2) {
        workOrders.value = [];
        showSearchResults.value = false;
        return;
    }
    
    isSearching.value = true;
    showSearchResults.value = true;
    
    // Use a debounce to avoid too many API calls while typing
    searchTimeout = setTimeout(async () => {
        try {
            // Connect to your actual Laravel backend endpoint for searching work orders
            const response = await axios.get('/search-work-orders', {
                params: { query }
            });
            
            console.log('Search response:', response.data);
            
            // Update the workOrders with the data from your actual database
            workOrders.value = response.data;
            
            // If it's an error response, handle it appropriately
            if (response.data.error) {
                console.error('Search error:', response.data.error);
                workOrders.value = [];
            }
        } catch (error) {
            console.error('Error searching work orders:', error);
            workOrders.value = [];
            
            // Show a user-friendly error message
            alert('There was an error performing your search. Please try again later.');
        } finally {
            isSearching.value = false;
        }
    }, 300);
}

// View work order details with better error handling
function openWorkOrderModal(workOrderId) {
    showSearchResults.value = false;
    isSearching.value = true;
    
    // Get the complete work order details from your backend
    axios.get(`/work-orders/${workOrderId}/details`)
        .then(response => {
            console.log('Work order details response:', response.data);
            selectedWorkOrder.value = response.data;
            showWorkOrderModal.value = true;
        })
        .catch(error => {
            console.error('Error fetching work order details:', error);
            
            if (error.response && error.response.status === 404) {
                alert(`Work order #${workOrderId} could not be found.`);
            } else {
                alert('Unable to load work order details. Please try again.');
            }
        })
        .finally(() => {
            isSearching.value = false;
        });
}

function closeModal() {
    showWorkOrderModal.value = false;
    selectedWorkOrder.value = null;
}

// Close search results when clicking outside
function handleClickOutside(event) {
    if (!event.target.closest('.search-container')) {
        showSearchResults.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Existing functions
const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

// Update the logout method
function logout() {
  // Instead of using axios directly, use Inertia's router for form submission
  // This automatically handles CSRF tokens for you
  router.post('/logout', {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Handle successful logout
      console.log('Logout successful');
      // Optionally redirect
      window.location.href = '/login';
    },
    onError: (errors) => {
      console.error('Logout failed:', errors);
    },
  });
}
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-900 relative isolate overflow-hidden bg-opacity-95 bg-fixed">
            <nav class="bg-transparent backdrop-blur-md border-b border-gray-600 fixed inset-x-0 top-0 z-20">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')" class="flex items-center">
                                    <ApplicationMark class="block h-9 w-auto" />
                                    <p class="italic font-extrabold text-white ms-2 -ml-3">Technology</p>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                            </div>
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('work-orders.index')" :active="route().current('work-orders.index')">
                                    Work Orders
                                </NavLink>
                            </div>
                        </div>
                        
                        <!-- Search Component with error handling -->
                        <div class="p-3 search-container relative">
                            <Search placeholder="Search work orders..." @search="handleSearch" />
                            
                            <!-- Search Results Dropdown with error state -->
                            <div v-if="showSearchResults" 
                                 class="absolute mt-1 w-full bg-white dark:bg-gray-800 shadow-lg rounded-md overflow-hidden z-50">
                                <div v-if="isSearching" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                    Searching work orders...
                                </div>
                                <ul v-else-if="workOrders.length > 0" class="max-h-80 overflow-y-auto">
                                    <li v-for="order in workOrders" :key="order.id" 
                                        class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b dark:border-gray-700 last:border-b-0"
                                        @click="openWorkOrderModal(order.id)">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">
                                                    {{ order.title || `Work Order #${order.id}` }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ order.customer_name || order.customer_id || 'No customer info' }}
                                                </div>
                                            </div>
                                            <div>
                                                <span v-if="order.status" class="text-xs px-2 py-1 rounded" 
                                                    :class="{
                                                        'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100': 
                                                            order.status.toLowerCase().includes('complete'),
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100': 
                                                            order.status.toLowerCase().includes('scheduled'),
                                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100': 
                                                            order.status.toLowerCase().includes('progress'),
                                                        'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100': 
                                                            order.status.toLowerCase().includes('cancel'),
                                                        'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100': 
                                                            order.status.toLowerCase().includes('part') || order.status.toLowerCase().includes('return')
                                                    }">
                                                    {{ order.status }}
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <div v-else-if="searchQuery.length >= 2" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    No work orders found matching "{{ searchQuery }}"
                                </div>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <div class="ms-3 relative">
                                <!-- Teams Dropdown -->
                                <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.current_team.name }}

                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="w-60">
                                            <!-- Team Management -->
                                            <div class="block px-4 py-2 text-xs text-gray-800">
                                                Manage Team
                                            </div>

                                            <!-- Team Settings -->
                                            <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">
                                                Team Settings
                                            </DropdownLink>

                                            <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">
                                                Create New Team
                                            </DropdownLink>

                                            <!-- Team Switcher -->
                                            <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                <div class="border-t border-gray-900" />

                                                <div class="block px-4 py-2 text-xs text-lime-400">
                                                    Switch Teams
                                                </div>

                                                <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                                    <form @submit.prevent="switchToTeam(team)">
                                                        <DropdownLink as="button">
                                                            <div class="flex items-center">
                                                                <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 size-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>

                                                                <div>{{ team.name }}</div>
                                                            </div>
                                                        </DropdownLink>
                                                    </form>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Account
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                                            API Tokens
                                        </DropdownLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button class="inline-flex items-center justify-center p-2 rounded-md text-lime-400 hover:text-gray-800 hover:bg-gray-900 focus:outline-none focus:bg-gray-800 focus:text-gray-500 transition duration-150 ease-in-out" @click="showingNavigationDropdown = ! showingNavigationDropdown">
                                <svg
                                    class="size-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('work-orders.index')" :active="route().current('work-orders.index')">
                            Work Orders
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <img class="size-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                Profile
                            </ResponsiveNavLink>

                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">
                                API Tokens
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Log Out
                                </ResponsiveNavLink>
                            </form>

                            <!-- Team Management -->
                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="border-t border-gray-200" />

                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    Manage Team
                                </div>

                                <!-- Team Settings -->
                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)" :active="route().current('teams.show')">
                                    Team Settings
                                </ResponsiveNavLink>

                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')" :active="route().current('teams.create')">
                                    Create New Team
                                </ResponsiveNavLink>

                                <!-- Team Switcher -->
                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                    <div class="border-t border-gray-200" />

                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        Switch Teams
                                    </div>

                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                        <form @submit.prevent="switchToTeam(team)">
                                            <ResponsiveNavLink as="button">
                                                <div class="flex items-center">
                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 size-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div>{{ team.name }}</div>
                                                </div>
                                            </ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Background Element -->
            <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
                <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[46.125rem] -translate-x-1/2 rotate-[45deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 20.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
            </div>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-gray-900 shadow mt-16">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="mt-16">
                <slot />
            </main>
        </div>
        
        <!-- Work Order Modal - Displays data from your actual database -->
        <div v-if="showWorkOrderModal && selectedWorkOrder" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-800 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <!-- Header with close button -->
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                                        Work Order #{{ selectedWorkOrder.id }}
                                    </h3>
                                    <button @click="closeModal" class="text-gray-400 hover:text-gray-200">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <!-- Work order details from your database -->
                                <div class="text-gray-300 space-y-3">
                                    <div v-if="selectedWorkOrder.title" class="mb-2">
                                        <h4 class="text-lg font-medium text-white">{{ selectedWorkOrder.title }}</h4>
                                    </div>
                                    
                                    <div v-if="selectedWorkOrder.status" class="flex justify-between">
                                        <div>
                                            <p class="text-sm text-gray-400">Status:</p>
                                            <span class="inline-flex px-2 py-1 text-xs rounded" 
                                                  :class="{
                                                    'bg-green-800 text-green-100': selectedWorkOrder.status.toLowerCase().includes('complete'),
                                                    'bg-blue-800 text-blue-100': selectedWorkOrder.status.toLowerCase().includes('scheduled'),
                                                    'bg-yellow-800 text-yellow-100': selectedWorkOrder.status.toLowerCase().includes('progress'),
                                                    'bg-red-800 text-red-100': selectedWorkOrder.status.toLowerCase().includes('cancel'),
                                                    'bg-purple-800 text-purple-100': selectedWorkOrder.status.toLowerCase().includes('part') || 
                                                                                    selectedWorkOrder.status.toLowerCase().includes('return')
                                                  }">
                                                {{ selectedWorkOrder.status }}
                                            </span>
                                        </div>
                                        
                                        <div v-if="selectedWorkOrder.price">
                                            <p class="text-sm text-gray-400">Price:</p>
                                            <span>${{ selectedWorkOrder.price }}</span>
                                        </div>
                                    </div>
                                    
                                    <div v-if="selectedWorkOrder.customer_id || selectedWorkOrder.customer_name" class="mt-4">
                                        <p class="text-sm text-gray-400">Customer:</p>
                                        <p v-if="selectedWorkOrder.customer_name">{{ selectedWorkOrder.customer_name }}</p>
                                        <p v-if="selectedWorkOrder.customer_id" class="text-sm">ID: {{ selectedWorkOrder.customer_id }}</p>
                                    </div>
                                    
                                    <div v-if="selectedWorkOrder.created_at || selectedWorkOrder.date">
                                        <p class="text-sm text-gray-400">Date:</p>
                                        <p>{{ new Date(selectedWorkOrder.created_at || selectedWorkOrder.date).toLocaleString() }}</p>
                                    </div>
                                    
                                    <div v-if="selectedWorkOrder.description" class="mt-4">
                                        <p class="text-sm text-gray-400">Description:</p>
                                        <p class="whitespace-pre-line mt-1 text-sm bg-gray-800 p-3 rounded-md">
                                            {{ selectedWorkOrder.description }}
                                        </p>
                                    </div>
                                    
                                    <!-- Display any additional fields from your database -->
                                    <template v-for="(value, key) in selectedWorkOrder" :key="key">
                                        <div v-if="!['id', 'title', 'status', 'price', 'customer_id', 'customer_name', 'created_at', 'date', 'description'].includes(key) && value">
                                            <p class="text-sm text-gray-400">{{ key.charAt(0).toUpperCase() + key.slice(1).replace('_', ' ') }}:</p>
                                            <p>{{ value }}</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal footer -->
                    <div class="bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <Link :href="`/work-orders/${selectedWorkOrder.id}`" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                            View Full Details
                        </Link>
                        <button @click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-700 shadow-sm px-4 py-2 bg-gray-700 text-base font-medium text-gray-300 hover:bg-gray-600 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.search-container {
    width: 300px;
    max-width: 100%;
}

@media (max-width: 640px) {
    .search-container {
        width: 100%;
    }
}
</style>