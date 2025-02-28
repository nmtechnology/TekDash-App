<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

defineProps({
    title: String,
});

// Search functionality variables
const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const showSearchResults = ref(false);
const selectedWorkOrder = ref(null);
const showWorkOrderModal = ref(false);
const showingNavigationDropdown = ref(false);

// Search functionality
let searchTimeout;
function searchWorkOrders() {
    clearTimeout(searchTimeout);
    
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        showSearchResults.value = false;
        return;
    }
    
    isSearching.value = true;
    showSearchResults.value = true;
    
    searchTimeout = setTimeout(async () => {
        try {
            const response = await axios.get('/api/search-work-orders', {
                params: { query: searchQuery.value }
            });
            searchResults.value = response.data;
        } catch (error) {
            console.error('Error searching work orders:', error);
        } finally {
            isSearching.value = false;
        }
    }, 300);
}

// Work order modal functionality
function openWorkOrderModal(workOrderId) {
    showSearchResults.value = false;
    
    axios.get(`/api/work-orders/${workOrderId}/details`)
        .then(response => {
            selectedWorkOrder.value = response.data;
            showWorkOrderModal.value = true;
        })
        .catch(error => {
            console.error('Error fetching work order details:', error);
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

const logout = () => {
    router.post(route('logout'));
};
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

       <!-- Search section -->
<div class="flex flex-1 justify-center lg:justify-end p-4 search-container relative">
    <div class="relative w-full max-w-lg lg:max-w-xs">
        <input 
            type="search" 
            name="search" 
            aria-label="Search work orders" 
            class="peer block w-full rounded-md bg-indigo-400/25 py-1 pl-8 pr-3 text-base text-indigo-100 outline-none placeholder:text-indigo-200 focus:bg-white focus:text-gray-900 focus:placeholder:text-gray-400 sm:text-sm" 
            placeholder="Search work orders" 
            v-model="searchQuery"
            @input="searchWorkOrders"
        />
        <MagnifyingGlassIcon class="pointer-events-none absolute left-2 top-1/2 transform -translate-y-1/2 size-5 text-indigo-200 peer-focus:text-gray-400" aria-hidden="true" />
        
        <!-- Search Results Dropdown -->
        <div v-if="showSearchResults && (searchResults.length > 0 || isSearching)" class="absolute mt-1 w-full bg-gray-800 shadow-lg rounded-md z-30 max-h-60 overflow-y-auto">
            <div v-if="isSearching" class="p-2 text-gray-300 text-sm">Searching...</div>
            <div v-else-if="searchResults.length === 0" class="p-2 text-gray-300 text-sm">No results found</div>
            <ul v-else>
                <li v-for="result in searchResults" :key="result.id" 
                    @click="openWorkOrderModal(result.id)"
                    class="p-2 hover:bg-gray-700 cursor-pointer text-gray-200 text-sm border-b border-gray-700 flex justify-between">
                    <span class="font-medium">{{ result.title }}</span>
                    <span class="text-xs bg-gray-600 rounded px-2 py-1">{{ result.status }}</span>
                </li>
            </ul>
        </div>
    </div>
</div>
                        <div class="flex lg:hidden"></div>

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
        <!-- Add this right before the final </div> closing tag -->

<!-- Work Order Modal -->
<div v-if="showWorkOrderModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
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
                                {{ selectedWorkOrder?.title || 'Work Order Details' }}
                            </h3>
                            <button @click="closeModal" class="text-gray-400 hover:text-gray-200">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Work order details -->
                        <div v-if="selectedWorkOrder" class="text-gray-300 space-y-3">
                            <div class="flex justify-between">
                                <div>
                                    <p class="text-sm text-gray-400">Status:</p>
                                    <span class="inline-flex px-2 py-1 text-xs rounded" 
                                          :class="{
                                            'bg-green-800 text-green-100': selectedWorkOrder.status === 'Complete',
                                            'bg-blue-800 text-blue-100': selectedWorkOrder.status === 'Scheduled',
                                            'bg-yellow-800 text-yellow-100': selectedWorkOrder.status === 'In Progress',
                                            'bg-red-800 text-red-100': selectedWorkOrder.status === 'Cancelled',
                                            'bg-purple-800 text-purple-100': selectedWorkOrder.status === 'Part/Return'
                                          }">
                                        {{ selectedWorkOrder.status }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">Price:</p>
                                    <span>${{ selectedWorkOrder.price }}</span>
                                </div>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-400">Customer ID:</p>
                                <p>{{ selectedWorkOrder.customer_id }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-400">Date:</p>
                                <p>{{ new Date(selectedWorkOrder.date_time).toLocaleString() }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-400">Description:</p>
                                <p class="whitespace-pre-line">{{ selectedWorkOrder.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal footer -->
            <div class="bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <Link :href="`/work-orders/${selectedWorkOrder?.id}`" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                    View Work Order
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