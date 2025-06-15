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
const sidebarOpen = ref(false);

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
    <div class="min-h-screen bg-gray-900 relative isolate overflow-hidden bg-opacity-95 flex">
      <!-- Sidebar Toggle Button (always visible, fixed at top left) -->
      <button
        class="fixed top-4 left-4 z-50 bg-gray-900/80 border border-gray-800 rounded-full p-2 shadow-lg hover:bg-gray-800 transition"
        @click="sidebarOpen = !sidebarOpen"
        aria-label="Toggle sidebar"
      >
        <svg v-if="!sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
      <!-- Sidebar -->
      <aside :class="['shadcn-sidebar', sidebarOpen ? 'left-0' : '-left-64', 'fixed top-0 z-50 h-full w-64 transition-all duration-300 bg-gray-900/80 border-r border-gray-800 backdrop-blur-lg']">
        <div class="flex flex-col h-full">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
            <span class="flex items-center gap-2 text-xl font-bold text-lime-400">
              <ApplicationMark class="h-7 w-7" />
              TekDash
            </span>
            <button class="md:hidden text-gray-400 hover:text-lime-400" @click="sidebarOpen = false">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <nav class="flex-1 px-4 py-6 space-y-2">
            <Link href="/dashboard" class="shadcn-nav-link shadcn-nav-link-dashboard">
              <svg class="inline-block mr-2 h-5 w-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0v6m0 0H7m6 0h6" /></svg>
              Dashboard
            </Link>
            <Link href="/work-orders" class="shadcn-nav-link shadcn-nav-link-workorders">
              <svg class="inline-block mr-2 h-5 w-5 text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6m-6 0a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2m-6 0v2a2 2 0 002 2h2a2 2 0 002-2v-2" /></svg>
              Work Orders
            </Link>
            <Link href="/customers" class="shadcn-nav-link shadcn-nav-link-customers">
              <svg class="inline-block mr-2 h-5 w-5 text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 010 7.75" /></svg>
              Customers
            </Link>
            <Link href="/technicians" class="shadcn-nav-link shadcn-nav-link-technicians">
              <svg class="inline-block mr-2 h-5 w-5 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" /></svg>
              Technicians
            </Link>
            <Link href="/gallery" class="shadcn-nav-link shadcn-nav-link-gallery">
              <svg class="inline-block mr-2 h-5 w-5 text-pink-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4-4a3 3 0 014 0l4 4M4 16V8a2 2 0 012-2h12a2 2 0 012 2v8M4 16h16" /></svg>
              Gallery
            </Link>
            <Link href="#" class="shadcn-nav-link shadcn-nav-link-about">
              <svg class="inline-block mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" /></svg>
              About
            </Link>
          </nav>
          <div class="px-4 py-4 border-t border-gray-800">
            <form method="POST" @submit.prevent="logout">
              <button type="submit" class="w-full text-left shadcn-nav-link">Logout</button>
            </form>
          </div>
          <!-- Sidebar toggle button for large screens -->
          <div class="hidden md:flex justify-center items-center py-3 border-t border-gray-800">
            <button
              class="px-4 py-2 rounded bg-gray-800 text-lime-400 hover:bg-gray-700 transition"
              @click="sidebarOpen = !sidebarOpen"
              aria-label="Toggle sidebar visibility"
              type="button"
            >
              <svg v-if="sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              <span>{{ sidebarOpen ? 'Hide Sidebar' : 'Show Sidebar' }}</span>
            </button>
          </div>
        </div>
      </aside>
      <!-- Sidebar overlay for mobile -->
      <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/40 md:hidden" @click="sidebarOpen = false"></div>
      <!-- Main content area -->
      <div class="flex-1 flex flex-col min-h-screen ml-0 md:ml-64 transition-all duration-300">
        <!-- Top navbar UNDER sidebar, lower z-index -->
        <nav class="navbar fixed-navbar bg-base-100 shadow-sm top-0 left-0 right-0 z-30 glass-header transition-all duration-300"
             :class="{ 'scrolled': isScrolled }">
          <div class="navbar-start">

          </div>
          <div class="navbar-center">
            <span class="btn-ghost text-xl md:hidden flex items-center gap-2">
              <ApplicationMark class="h-6 w-6" />
              TekDash
            </span>
          </div>
          <div class="navbar-end">
            <div class="w-80 max-w-lg md:w-96 md:max-w-xl lg:w-[420px] xl:w-[500px] search-container">
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
                <li><Link href="#">Profile</Link></li>
                <li><Link href="#">Settings</Link></li>
                <li>
                  <form method="POST" @submit.prevent="logout">
                    <button type="submit">Logout</button>
                  </form>
                </li>
              </ul>
            </div>
          </div>
        </nav>
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
  </div>
</template>

<style scoped>

.glass-header {
  background: rgba(17, 24, 39, 0.95);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

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
  z-index: 30;
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

.shadcn-sidebar {
  transition: left 0.3s cubic-bezier(0.4,0,0.2,1);
}
.shadcn-nav-link {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  text-decoration: none;
  transition: background 0.2s, color 0.2s;
  color: #a3e635;
}
.shadcn-nav-link-dashboard { color: #60a5fa; }
.shadcn-nav-link-workorders { color: #a78bfa; }
.shadcn-nav-link-customers { color: #22d3ee; }
.shadcn-nav-link-technicians { color: #fb923c; }
.shadcn-nav-link-gallery { color: #f472b6; }
.shadcn-nav-link-about { color: #a3a3a3; }
.shadcn-nav-link:hover, .shadcn-nav-link.active {
  background: rgba(163, 230, 53, 0.08);
  color: #bef264;
}
@media (min-width: 768px) {
  .shadcn-sidebar {
    /* Removed left: 0 !important; to allow sidebarOpen to control visibility on all screens */
    position: fixed;
  }
}
@media (max-width: 767px) {
  .shadcn-sidebar {
    z-index: 50;
  }
  .flex-1 {
    margin-left: 0 !important;
  }
}
</style>