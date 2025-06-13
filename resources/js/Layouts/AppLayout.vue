<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ToastContainer from '@/Components/ToastContainer.vue';
import axios from 'axios';
import NotificationsDropdown from '@/Components/NotificationsDropdown.vue';
import Search from '@/Components/Search.vue';

defineProps({
    title: String,
});

// Get page object using the usePage composable
const page = usePage();

// Search functionality variables
const searchQuery = ref('');
const workOrders = ref([]);
const isSearching = ref(false);
const showSearchResults = ref(false);
const selectedWorkOrder = ref(null);
const showWorkOrderModal = ref(false);
const showingNavigationDropdown = ref(false);
const isScrolled = ref(false);

// Add scroll event listener to detect scrolling for navbar effects
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    
    // Add scroll event listener for header effects
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('scroll', handleScroll);
});

// Handle scroll events
function handleScroll() {
    if (window.scrollY > 10) {
        isScrolled.value = true;
    } else {
        isScrolled.value = false;
    }
}

// Work order search functionality
function handleSearch(query) {
    isSearching.value = true;
    searchQuery.value = query;
    
    if (query.length < 2) {
        workOrders.value = [];
        showSearchResults.value = false;
        isSearching.value = false;
        return;
    }
    
    showSearchResults.value = true;
    
    // Connect to your actual Laravel backend endpoint for searching work orders
    axios.get('/search-work-orders', {
        params: { query }
    })
    .then(response => {
        workOrders.value = response.data;
    })
    .catch(error => {
        console.error('Error searching work orders:', error);
        workOrders.value = [];
    })
    .finally(() => {
        isSearching.value = false;
    });
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

        <div class="min-h-screen bg-gray-900 relative isolate overflow-hidden bg-opacity-95">
            <nav class="navbar fixed-navbar bg-base-100 shadow-sm top-0 left-0 right-0 z-50">
                <div class="navbar-start">
                    <div class="dropdown">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                            </svg>
                        </div>
                        <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
                            <li><a href="/dashboard">Dashboard</a></li>
                            <li><a href="/work-orders">Work Orders</a></li>
                            <li><a href="/customers">Customers</a></li>
                            <li><a href="/technicians">Technicians</a></li>
                            <li><a href="/gallery">Gallery</a></li>
                            <li><a>About</a></li>
                        </ul>
                    </div>
                </div>
                <div class="navbar-center">
                    <a class="btn-ghost text-xl">TekDash</a>
                </div>
                <div class="navbar-end">
                    <div class="w-24 md:w-auto">
                        <Search placeholder="Search Work Orders..." @search="handleSearch" />
                    </div>
                    <NotificationsDropdown />
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                            <div class="w-10 rounded-full">
                                <img :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" />
                            </div>
                        </div>
                        <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
                            <li>
                                <a href="#">Profile</a>
                            </li>
                            <li>
                                <a href="#">Settings</a>
                            </li>
                            <li>
                                <form method="POST" @submit.prevent="logout">
                                    <button type="submit">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Toast Container -->
            <ToastContainer />

            <!-- Background Element -->
            <div class="fixed inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
                <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[46.125rem] -translate-x-1/2 rotate-[45deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 20.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
            </div>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="glossy-header shadow pt-16">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="content-container pt-16">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.search-container {
    width: 200px;
    max-width: 100%;
}

@media (max-width: 640px) {
    .search-container {
        width: 100%;
    }
}


.dark .dropdown-content {
    background-color: #1f2937;
    border-color: #374151;
}

/* Glass morphism styles */
.glossy-card {
  background: linear-gradient(135deg, rgba(17, 24, 39, 0.95), rgba(31, 41, 55, 0.85));
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 0.5rem;
}

.fixed-navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  background: linear-gradient(180deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.85) 100%);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  z-index: 50;
  transition: all 0.3s ease;
}

.fixed-navbar.scrolled {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
  background: linear-gradient(180deg, rgba(17, 24, 39, 0.95), rgba(31, 41, 55, 0.9));
  border-bottom: 1px solid rgba(243, 244, 246, 0.2);
}

.fixed-navbar::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(243, 244, 246, 0.3), transparent);
}

.content-container {
  padding-top: 64px; /* Match the height of your navbar */
  min-height: calc(100vh - 64px); /* 100vh minus navbar height */
  position: relative;
  z-index: 1;
}

:deep(body) {
  margin: 0;
  padding: 0;
  overflow-x: hidden;
  scroll-padding-top: 64px; /* Height of navbar for smooth scrolling to anchors */
}

.glossy-header {
  background: linear-gradient(180deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.85) 100%);
  position: relative;
  overflow: hidden;
  z-index: 10;
  transition: all 0.3s ease;
}

.glossy-header.scrolled {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
}

.glossy-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(243, 244, 246, 0.3), transparent);
}

/* Nav Link Spotlight Effect */
.nav-link-spotlight {
  position: relative;
  overflow: hidden;
}

.nav-link-spotlight::before {
  content: '';
  position: absolute;
  top: -20px; /* Positioned above the link */
  left: 50%;
  transform: translateX(-50%);
  width: 30px;
  height: 30px;
  background: radial-gradient(
    circle,
    rgba(1, 13, 38, 0.4) 0%,
    rgba(24, 53, 112, 0.1) 40%,
    transparent 70%
  );
  border-radius: 50%;
  opacity: 0;
  transition: opacity 0.3s, transform 0.5s;
  pointer-events: none;
  z-index: 1;
}

.nav-link-spotlight:hover::before {
  opacity: 1;
  transform: translateX(-50%) scale(1.5);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    opacity: 0.4;
    transform: translateX(-50%) scale(1);
  }
  50% {
    opacity: 0.6;
    transform: translateX(-50%) scale(1.5);
  }
  100% {
    opacity: 0.4;
    transform: translateX(-50%) scale(1);
  }
}

/* Active link style */
.nav-link-spotlight.active::before {
  opacity: 0.7;
  background: radial-gradient(
    circle,
    rgba(243, 244, 246, 0.6) 0%,
    rgba(243, 244, 246, 0.3) 40%,
    transparent 70%
  );
  transform: translateX(-50%) scale(1.5);
}

/* Additional header glow effect */
.glossy-header::after {
  content: '';
  position: absolute;
  top: -10px;
  left: 0;
  right: 0;
  height: 10px;
  background: linear-gradient(180deg, rgba(243, 244, 246, 0.1), transparent);
  pointer-events: none;
}

.glossy-footer {
  background: linear-gradient(0deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.85) 100%);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  position: relative;
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
}

.glossy-section {
  background: linear-gradient(145deg, rgba(17, 24, 39, 0.5), rgba(31, 41, 55, 0.3));
  border-radius: 8px;
  padding: 10px;
  position: relative;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.glossy-content {
  background: linear-gradient(145deg, rgba(31, 41, 55, 0.6), rgba(17, 24, 39, 0.4));
  border: 1px solid rgba(255, 255, 255, 0.05);
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
}

/* Make sure navbar sticks to the top even when scrolling */
body {
  padding-top: 0 !important; /* Ensure no default padding interferes */
  scroll-padding-top: 64px; /* Height of your navbar */
}

/* Add vertical spotlight effect for dropdown items */
.dropdown-content .dropdown-item {
  position: relative;
  overflow: hidden;
}

.dropdown-content .dropdown-item::before {
  content: '';
  position: absolute;
  left: 10px;
  top: 0;
  height: 100%;
  width: 3px;
  background: linear-gradient(to bottom, transparent, rgba(243, 244, 246, 0.3), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.dropdown-content .dropdown-item:hover::before {
  opacity: 1;
}

/* Button styling to match the glossy theme */
.glossy-btn {
  background: linear-gradient(135deg, rgb(25, 29, 37), rgb(25, 29, 40));
  backdrop-filter: blur(4px);
  transition: all 0.3s ease;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.glossy-btn:hover {
  background: linear-gradient(135deg, rgb(25, 29, 37), rgba(229, 231, 235, 0.2));
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(243, 244, 246, 0.4);
}

/* Custom scrollbar for webkit browsers */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: rgba(17, 24, 39, 0.3);
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgba(243, 244, 246, 0.3);
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(243, 244, 246, 0.5);
}

/* Enhanced modal layout */
.glossy-card {
  display: flex;
  flex-direction: column;
  height: 65vh; /* Adjust this value as needed */
  max-height: 85vh;
}

/* Fixed header styling */
.glossy-header {
  background: linear-gradient(180deg, rgba(31, 41, 55, 0.95) 0%, rgba(17, 24, 39, 0.9) 100%);
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
  flex-shrink: 0;
}

/* Scrollable content area */
.overflow-y-auto {
  flex-grow: 1;
  overflow-y: auto;
  scrollbar-color: rgba(243, 244, 246, 0.3) rgba(17, 24, 39, 0.3);
  scrollbar-width: thin;
}

/* Fixed footer styling */
.glossy-footer {
  background: linear-gradient(0deg, rgba(31, 41, 55, 0.95) 0%, rgba(17, 24, 39, 0.9) 100%);
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
  flex-shrink: 0;
}

/* Additional spotlight effect for active page */
.active .nav-link-spotlight::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, transparent, rgba(243, 244, 246, 0.7), transparent);
  opacity: 1;
}

/* Glass effect for cards and content areas */
.glossy-panel {
  background: linear-gradient(145deg, 
    rgba(31, 41, 55, 0.7), 
    rgba(17, 24, 39, 0.6)
  );
  border-radius: 0.5rem;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.05);
  overflow: hidden;
}
</style>