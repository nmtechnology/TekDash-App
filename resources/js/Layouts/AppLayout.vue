<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';
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

// Customer dropdown state
const showCustomerDropdown = ref(false);
const customers = ref([]);
const customerColors = ['bg-emerald-500', 'bg-blue-500', 'bg-indigo-500', 'bg-violet-500', 'bg-fuchsia-500', 'bg-pink-500', 'bg-rose-500', 'bg-amber-500', 'bg-lime-500', 'bg-cyan-500', 'bg-sky-500', 'bg-teal-500'];
const isLoadingCustomers = ref(false);
const customerError = ref(false);
const focusedCustomerIndex = ref(-1);

// Function to fetch customers
async function fetchCustomers() {
  if (customers.value.length > 0) return; // Don't fetch again if we already have data
  
  try {
    isLoadingCustomers.value = true;
    customerError.value = false;
    
    const response = await axios.get('/api/customers');
    
    // Parse the response based on its structure
    // It could be an array of customers directly, or nested in data property,
    // or in a paginated structure like {data: [...], meta: {...}}
    let customersData = [];
    
    if (Array.isArray(response.data)) {
      // Direct array of customers
      customersData = response.data;
    } else if (response.data && Array.isArray(response.data.data)) {
      // Nested in data property or paginated response
      customersData = response.data.data;
    } else if (response.data && typeof response.data === 'object') {
      // Single customer object
      customersData = [response.data];
    }
    
    // Filter out any null or invalid customers and ensure they have required fields
    customers.value = customersData
      .filter(customer => customer && typeof customer === 'object')
      .map(customer => {
        // Log any customers without an ID for debugging
        if (!customer.id) {
          console.warn('Customer without ID found', customer);
        }
        
        // Ensure customer has at least one of business_name or poc_name
        if (!customer.business_name && !customer.poc_name) {
          customer.business_name = `Customer ${customer.id || ''}`;
        }
        
        return customer;
      })
      // Sort customers alphabetically by business_name or poc_name
      .sort((a, b) => {
        const nameA = (a.business_name || a.poc_name || '').toLowerCase();
        const nameB = (b.business_name || b.poc_name || '').toLowerCase();
        return nameA.localeCompare(nameB);
      });
      
    console.log('Fetched customers:', customers.value);
  } catch (error) {
    console.error('Error fetching customers:', error);
    customers.value = [];
    customerError.value = true;
  } finally {
    isLoadingCustomers.value = false;
  }
}

// Function to retry fetching customers
function retryFetchCustomers() {
  if (!isLoadingCustomers.value) {
    fetchCustomers();
  }
}

// Get a consistent color for each customer based on their ID
function getCustomerColor(customerId) {
  // Handle null/undefined customerId gracefully
  if (customerId === null || customerId === undefined) {
    return customerColors[0]; // Default to first color
  }
  
  const index = typeof customerId === 'number' ? customerId % customerColors.length : 0;
  return customerColors[index];
}

// Get customer initials for avatar display
function getCustomerInitials(customer) {
  if (!customer) return '?';
  
  if (customer.business_name) {
    // For business names, get the first letter or first letters of multiple words
    const businessParts = customer.business_name.split(' ');
    if (businessParts.length > 1 && businessParts[0].length > 2) {
      // For multi-word business names, take first letter of first word
      return businessParts[0].charAt(0).toUpperCase();
    } else if (businessParts.length > 1) {
      // For short business names with multiple words, take first letter of first two words
      return (businessParts[0].charAt(0) + businessParts[1].charAt(0)).toUpperCase();
    }
    // Just the first letter if only one word
    return customer.business_name.charAt(0).toUpperCase();
  } else if (customer.poc_name) {
    // If it's a person's name (point of contact), try to get first and last initials
    const nameParts = customer.poc_name.split(' ');
    if (nameParts.length > 1) {
      // Get first letter of first name and first letter of last name
      return (nameParts[0].charAt(0) + nameParts[nameParts.length - 1].charAt(0)).toUpperCase();
    }
    // Just the first letter if only one name part
    return customer.poc_name.charAt(0).toUpperCase();
  }
  
  // Fallback
  return '•';
}

// Add event listeners on mount
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    
    // Add scroll event listener for header effects
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    // Clean up all event listeners on unmount
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('scroll', handleScroll);
    removeKeyboardNavigation();
});

// Handle scroll events
function handleScroll() {
    if (window.scrollY > 10) {
        isScrolled.value = true;
    } else {
        isScrolled.value = false;
    }
}

// Toggle customer dropdown and fetch customers when opened
function toggleCustomerDropdown() {
  showCustomerDropdown.value = !showCustomerDropdown.value;
  if (showCustomerDropdown.value) {
    // Reset focused index when opening dropdown
    focusedCustomerIndex.value = -1;
    
    // Announce to screen readers that dropdown is open
    announceToScreenReader('Customer dropdown opened');
    
    fetchCustomers().then(() => {
      // Add a small delay to ensure DOM is updated
      setTimeout(() => {
        setupShineEffect();
        setupKeyboardNavigation();
        
        // Announce number of customers loaded
        const count = customers.value.length;
        if (count > 0) {
          announceToScreenReader(`${count} customers loaded`);
        }
      }, 100);
    });
  } else {
    // Remove keyboard event listeners when closing dropdown
    removeKeyboardNavigation();
    
    // Announce to screen readers that dropdown is closed
    announceToScreenReader('Customer dropdown closed');
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
    axios.get('/api/search-work-orders', {
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
    
    // Close customer dropdown when clicking outside
    if (!event.target.closest('.customer-dropdown') && !event.target.closest('.customer-dropdown-button')) {
        if (showCustomerDropdown.value) {
            showCustomerDropdown.value = false;
            // Return focus to the dropdown button when closing
            const dropdownButton = document.querySelector('.customer-dropdown-button');
            if (dropdownButton) dropdownButton.focus();
        }
    }
}

// Handle shine effect for customer tiles
function setupShineEffect() {
  const customerDropdown = document.querySelector('.customer-dropdown');
  if (!customerDropdown) return;
  
  const customerLinks = customerDropdown.querySelectorAll('.customer-tile');
  console.log('Setting up shine effect for', customerLinks.length, 'customer tiles');
  
  // Remove existing listeners from all links first to prevent duplicates
  customerLinks.forEach(link => {
    link.removeEventListener('mousemove', handleShineEffect);
  });
  
  // Add fresh listeners
  customerLinks.forEach(link => {
    link.addEventListener('mousemove', handleShineEffect);
    
    // Reset values when mouse leaves
    link.addEventListener('mouseleave', () => {
      link.style.setProperty('--x', '50%');
      link.style.setProperty('--y', '50%');
    });
    
    // Set initial position at center
    link.style.setProperty('--x', '50%');
    link.style.setProperty('--y', '50%');
  });
}

// Separate handler function for shine effect to avoid duplicate anonymous functions
function handleShineEffect(e) {
  const rect = this.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;
  
  this.style.setProperty('--x', `${x}px`);
  this.style.setProperty('--y', `${y}px`);
}

// Setup keyboard navigation for customer dropdown
function setupKeyboardNavigation() {
  document.addEventListener('keydown', handleCustomerKeyNavigation);
}

// Remove keyboard navigation event listeners
function removeKeyboardNavigation() {
  document.removeEventListener('keydown', handleCustomerKeyNavigation);
}

// Handle keyboard navigation for customer tiles
function handleCustomerKeyNavigation(e) {
  // Only handle keyboard navigation when the dropdown is open
  if (!showCustomerDropdown.value || customers.value.length === 0) return;
  
  const gridCols = 3; // Number of columns in the grid
  const totalCustomers = customers.value.length;
  
  switch (e.key) {
    case 'ArrowRight':
      e.preventDefault();
      if (focusedCustomerIndex.value < totalCustomers - 1) {
        focusedCustomerIndex.value++;
        focusCustomerTile();
      }
      break;
      
    case 'ArrowLeft':
      e.preventDefault();
      if (focusedCustomerIndex.value > 0) {
        focusedCustomerIndex.value--;
        focusCustomerTile();
      }
      break;
      
    case 'ArrowUp':
      e.preventDefault();
      if (focusedCustomerIndex.value >= gridCols) {
        focusedCustomerIndex.value -= gridCols;
        focusCustomerTile();
      }
      break;
      
    case 'ArrowDown':
      e.preventDefault();
      if (focusedCustomerIndex.value + gridCols < totalCustomers) {
        focusedCustomerIndex.value += gridCols;
        focusCustomerTile();
      }
      break;
      
    case 'Enter':
    case ' ': // Space
      e.preventDefault();
      if (focusedCustomerIndex.value >= 0 && focusedCustomerIndex.value < totalCustomers) {
        navigateToCustomer(focusedCustomerIndex.value);
      }
      break;
      
    case 'Escape':
      e.preventDefault();
      showCustomerDropdown.value = false;
      // Return focus to the dropdown button
      const dropdownButton = document.querySelector('.customer-dropdown-button');
      if (dropdownButton) dropdownButton.focus();
      break;
      
    case 'Tab':
      // Don't prevent default for Tab, but reset the dropdown if focus leaves
      setTimeout(() => {
        if (!document.activeElement.closest('.customer-dropdown')) {
          showCustomerDropdown.value = false;
        }
      }, 10);
      break;
      
    case 'Home':
      e.preventDefault();
      focusedCustomerIndex.value = 0;
      focusCustomerTile();
      break;
      
    case 'End':
      e.preventDefault();
      focusedCustomerIndex.value = totalCustomers - 1;
      focusCustomerTile();
      break;
      
    default:
      // Handle letter key navigation - find first customer starting with pressed key
      if (e.key.length === 1 && e.key.match(/[a-z0-9]/i)) {
        e.preventDefault();
        
        const letter = e.key.toLowerCase();
        const currentIndex = focusedCustomerIndex.value;
        
        // Start searching from the next index after current, or from beginning if at end
        let startIndex = currentIndex >= 0 ? (currentIndex + 1) % totalCustomers : 0;
        let index = startIndex;
        let found = false;
        
        // First try to find a match after the current position
        do {
          const customer = customers.value[index];
          const name = (customer.business_name || customer.poc_name || '').toLowerCase();
          
          if (name.startsWith(letter)) {
            focusedCustomerIndex.value = index;
            focusCustomerTile();
            found = true;
            break;
          }
          
          index = (index + 1) % totalCustomers;
        } while (index !== startIndex);
        
        // If no match found after current position, search from beginning
        if (!found && currentIndex > 0) {
          startIndex = 0;
          index = startIndex;
          
          while (index < currentIndex) {
            const customer = customers.value[index];
            const name = (customer.business_name || customer.poc_name || '').toLowerCase();
            
            if (name.startsWith(letter)) {
              focusedCustomerIndex.value = index;
              focusCustomerTile();
              found = true;
              break;
            }
            
            index++;
          }
        }
      }
      break;
  }
}

// Focus the currently selected customer tile
function focusCustomerTile() {
  setTimeout(() => {
    const tiles = document.querySelectorAll('.customer-tile');
    if (tiles[focusedCustomerIndex.value]) {
      tiles[focusedCustomerIndex.value].focus();
      
      // Announce to screen readers
      const customer = customers.value[focusedCustomerIndex.value];
      if (customer) {
        announceToScreenReader(`${customer.business_name || customer.poc_name || 'Customer'} selected`);
      }
      
      // Ensure the focused tile is visible by scrolling if necessary
      const container = document.querySelector('.customers-grid');
      const tile = tiles[focusedCustomerIndex.value];
      
      if (container && tile) {
        const containerRect = container.getBoundingClientRect();
        const tileRect = tile.getBoundingClientRect();
        
        if (tileRect.bottom > containerRect.bottom) {
          container.scrollTop += (tileRect.bottom - containerRect.bottom);
        } else if (tileRect.top < containerRect.top) {
          container.scrollTop -= (containerRect.top - tileRect.top);
        }
      }
    }
  }, 10);
}

// Function to announce messages to screen readers
function announceToScreenReader(message) {
  // Create or get existing announcement element
  let announcement = document.getElementById('sr-announcement');
  
  if (!announcement) {
    announcement = document.createElement('div');
    announcement.id = 'sr-announcement';
    announcement.setAttribute('aria-live', 'polite');
    announcement.setAttribute('aria-atomic', 'true');
    announcement.style.position = 'absolute';
    announcement.style.width = '1px';
    announcement.style.height = '1px';
    announcement.style.padding = '0';
    announcement.style.margin = '-1px';
    announcement.style.overflow = 'hidden';
    announcement.style.clip = 'rect(0, 0, 0, 0)';
    announcement.style.whiteSpace = 'nowrap';
    announcement.style.border = '0';
    document.body.appendChild(announcement);
  }
  
  // Clear and set the message
  announcement.textContent = '';
  
  // Use setTimeout to ensure the change is registered by screen readers
  setTimeout(() => {
    announcement.textContent = message;
  }, 50);
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
  <div class="h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black relative overflow-hidden">
    <ToastContainer class="fixed top-0 left-0 w-full z-[99999] pointer-events-none" />
    <Head :title="title" />
    <Banner />
    
    <!-- Animated Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-10 left-10 w-64 h-64 bg-lime-400/6 rounded-full mix-blend-multiply filter blur-xl opacity-60 animate-blob"></div>
      <div class="absolute top-20 right-10 w-48 h-48 bg-cyan-400/6 rounded-full mix-blend-multiply filter blur-xl opacity-60 animate-blob animation-delay-2000"></div>
      <div class="absolute bottom-10 left-1/3 w-56 h-56 bg-purple-400/6 rounded-full mix-blend-multiply filter blur-xl opacity-60 animate-blob animation-delay-4000"></div>
    </div>

    <div class="relative z-10 h-full flex">
      <!-- Compact Glass Sidebar -->
      <aside :class="[
        'fixed top-0 left-0 z-50 h-full w-64 transition-all duration-300 ease-in-out transform',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        'lg:translate-x-0 lg:static lg:inset-0'
      ]">
        <div class="h-full bg-white/5 backdrop-blur-xl border-r border-white/10 shadow-2xl flex flex-col">
          <!-- Compact Sidebar Header -->
          <div class="flex items-center justify-between p-4 border-b border-white/10">
            <Link href="/dashboard" class="flex items-center gap-2 text-lg font-bold bg-gradient-to-r from-lime-400 to-cyan-400 bg-clip-text text-transparent hover:scale-105 transition-transform">
              <ApplicationMark class="h-6 w-6" />
              TekDash
            </Link>
            <button 
              class="lg:hidden text-gray-400 hover:text-lime-400 p-1 rounded-lg hover:bg-white/10 transition-all" 
              @click="sidebarOpen = false"
              aria-label="Close sidebar"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <!-- Compact Navigation Menu -->
          <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <Link href="/dashboard" 
              :class="[
                'flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200 group text-sm',
                $page.url === '/dashboard' 
                  ? 'bg-lime-400/20 text-lime-400 border border-lime-400/30' 
                  : 'text-gray-300 hover:bg-white/10 hover:text-white'
              ]">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0v6m0 0H7m6 0h6" />
              </svg>
              <span class="font-medium">Dashboard</span>
            </Link>
            
            <Link href="/work-orders" 
              :class="[
                'flex items-center gap-3 px-3 py-2 rounded-lg transition-all duration-200 group text-sm',
                $page.url.startsWith('/work-orders') 
                  ? 'bg-purple-400/20 text-purple-400 border border-purple-400/30' 
                  : 'text-gray-300 hover:bg-white/10 hover:text-white'
              ]">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6m-6 0a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2m-6 0v2a2 2 0 002 2h2a2 2 0 002-2v-2" />
              </svg>
              <span class="font-medium">Work Orders</span>
            </Link>
            
            <!-- Compact Customers with dropdown -->
            <div class="relative">
              <button 
                @click="toggleCustomerDropdown"
                @keydown.enter.prevent="toggleCustomerDropdown"
                @keydown.space.prevent="toggleCustomerDropdown"
                :class="[
                  'w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg transition-all duration-200 group text-sm',
                  showCustomerDropdown || $page.url.startsWith('/customers')
                    ? 'bg-cyan-400/20 text-cyan-400 border border-cyan-400/30' 
                    : 'text-gray-300 hover:bg-white/10 hover:text-white'
                ]"
                aria-haspopup="true"
                :aria-expanded="showCustomerDropdown"
              >
                <div class="flex items-center gap-3">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 010 7.75" />
                  </svg>
                  <span class="font-medium">Customers</span>
                </div>
                <svg 
                  class="w-3 h-3 transition-transform duration-200" 
                  :class="showCustomerDropdown ? 'rotate-180' : ''" 
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>
              
              <!-- Compact Glass Dropdown -->
              <div 
                v-show="showCustomerDropdown" 
                class="mt-1 ml-3 bg-white/5 backdrop-blur-xl border border-white/10 rounded-lg p-3 shadow-2xl max-h-48 overflow-y-auto"
                style="transition: all 0.2s ease-in-out;"
              >
                <!-- Loading State -->
                <div v-if="isLoadingCustomers" class="flex flex-col items-center justify-center py-4">
                  <div class="animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-cyan-400"></div>
                  <span class="text-xs text-cyan-400 mt-1">Loading...</span>
                </div>
                
                <!-- Error State -->
                <div v-else-if="customerError" class="flex flex-col items-center justify-center py-4 text-center">
                  <svg class="w-6 h-6 text-red-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="text-xs text-red-400 mb-2">Failed to load</span>
                  <button @click="retryFetchCustomers" class="px-2 py-1 bg-red-500/20 hover:bg-red-500/30 border border-red-400/30 text-red-400 rounded text-xs transition-all">
                    Retry
                  </button>
                </div>
                
                <!-- Customer List (Compact) -->
                <div v-else-if="customers.length > 0" class="space-y-1">
                  <Link v-for="(customer, index) in customers.slice(0, 8)" :key="customer.id" 
                    :href="`/customers/${customer.id}`"
                    :class="[
                      'flex items-center gap-2 p-2 rounded text-xs hover:bg-white/10 transition-all group',
                      customerColors[index % customerColors.length].replace('bg-', 'hover:bg-').replace('500', '500/20')
                    ]"
                  >
                    <div :class="['w-2 h-2 rounded-full', customerColors[index % customerColors.length]]"></div>
                    <span class="text-gray-300 group-hover:text-white truncate">
                      {{ customer.business_name || customer.poc_name || `Customer ${customer.id}` }}
                    </span>
                  </Link>
                  <Link href="/customers" 
                    class="flex items-center justify-between gap-2 p-2 mt-2 bg-cyan-500/20 hover:bg-cyan-500/30 border border-cyan-400/30 text-cyan-400 rounded text-xs transition-all"
                  >
                    <span>View All</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                  </Link>
                </div>
              </div>
            </div>
            
            <Link href="/technicians" 
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group',
                $page.url.startsWith('/technicians')
                  ? 'bg-orange-400/20 text-orange-400 border border-orange-400/30' 
                  : 'text-gray-300 hover:bg-white/10 hover:text-white'
              ]">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
              </svg>
              <span class="font-medium">Technicians</span>
            </Link>
            
            <Link href="/gallery" 
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group',
                $page.url.startsWith('/gallery')
                  ? 'bg-pink-400/20 text-pink-400 border border-pink-400/30' 
                  : 'text-gray-300 hover:bg-white/10 hover:text-white'
              ]">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4-4a3 3 0 014 0l4 4M4 16V8a2 2 0 012-2h12a2 2 0 012 2v8M4 16h16" />
              </svg>
              <span class="font-medium">Gallery</span>
            </Link>
          </nav>
          
          <!-- User Profile Section -->
          <div class="border-t border-white/10 p-4">
            <!-- User Info -->
            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 mb-4">
              <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-lime-400/50">
                <img :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" 
                     class="w-full h-full object-cover" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ $page.props.auth.user.name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $page.props.auth.user.email }}</p>
              </div>
              <NotificationsDropdown />
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 gap-2 mb-4">
              <Link href="/user/profile" 
                class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg bg-white/10 hover:bg-white/20 text-gray-300 hover:text-white transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Profile</span>
              </Link>
              <Link href="/teams/1" 
                class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg bg-white/10 hover:bg-white/20 text-gray-300 hover:text-white transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Settings</span>
              </Link>
            </div>

            <!-- Logout Button -->
            <form method="POST" @submit.prevent="logout" class="w-full">
              <button type="submit" 
                class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-red-500/20 hover:bg-red-500/30 border border-red-400/30 hover:border-red-400/50 text-red-400 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="font-medium">Logout</span>
              </button>
            </form>
          </div>
        </div>
      </aside>
      
      <!-- Mobile Sidebar Overlay -->
      <div v-if="sidebarOpen" 
           class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden" 
           @click="sidebarOpen = false"></div>
      
      <!-- Mobile Sidebar Toggle Button -->
      <button
        class="fixed top-4 left-4 z-50 lg:hidden bg-white/10 backdrop-blur-xl border border-white/20 rounded-xl p-3 shadow-2xl hover:bg-white/20 transition-all"
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

      <!-- Compact Main Content Area -->
      <div class="flex-1 flex flex-col h-full transition-all duration-300 lg:ml-64">
        
        <!-- Compact Glass Header -->
        <header class="fixed top-0 right-0 left-0 lg:left-64 z-30 bg-white/5 backdrop-blur-xl border-b border-white/10 shadow-2xl transition-all duration-300 h-14"
                :class="{ 'shadow-2xl border-white/20': isScrolled }">
          <div class="flex items-center justify-between px-4 py-3 h-full">
            <!-- Left: Mobile menu space / Desktop breadcrumbs -->
            <div class="flex items-center gap-3">
              <div class="w-10 lg:hidden"></div> <!-- Space for mobile toggle -->
              <div class="hidden lg:flex items-center gap-2 text-xs text-gray-400">
                <Link href="/dashboard" class="hover:text-lime-400 transition-colors">Dashboard</Link>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-white font-medium">{{ title || 'Page' }}</span>
              </div>
            </div>
            
            <!-- Center: Brand (mobile only) -->
            <div class="lg:hidden">
              <Link href="/dashboard" class="flex items-center gap-2 text-base font-bold bg-gradient-to-r from-lime-400 to-cyan-400 bg-clip-text text-transparent">
                <ApplicationMark class="h-5 w-5" />
                TekDash
              </Link>
            </div>
            
            <!-- Right: Search and user actions -->
            <div class="flex items-center gap-3">
              <!-- Search will go here -->
              <div class="w-10"></div> <!-- Placeholder for balance -->
            </div>
          </div>
        </header>

        <!-- Compact Page Header -->
        <div v-if="$slots.header" class="pt-14 pb-3 px-4">
          <div class="bg-white/5 backdrop-blur-xl rounded-xl p-4 border border-white/10 shadow-2xl">
            <slot name="header" />
          </div>
        </div>

        <!-- Compact Main Content -->
        <main class="flex-1 px-4 pb-3 overflow-hidden" :class="{ 'pt-14': !$slots.header, 'pt-3': $slots.header }">
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>


<style scoped>
/* Blob animations matching the dashboard and other pages */
@keyframes blob {
  0% { transform: translate(0px, 0px) scale(1); }
  33% { transform: translate(30px, -50px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
  100% { transform: translate(0px, 0px) scale(1); }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

/* Glass morphism effects */
.backdrop-blur-xl {
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
}

/* Enhanced shadow effects */
.shadow-2xl {
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.5),
    0 0 0 1px rgba(255, 255, 255, 0.05),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

/* Gradient text effect */
.bg-gradient-to-r.from-lime-400.to-cyan-400 {
  background-image: linear-gradient(to right, #a3e635, #22d3ee);
}

/* Button and link hover animations */
a, button {
  transition: all 0.2s ease;
}

a:hover, button:hover {
  transform: translateY(-1px);
}

/* Compact sidebar responsive behavior */
@media (max-width: 1024px) {
  .lg\:ml-64 {
    margin-left: 0 !important;
  }
}

/* Mobile adjustments */
@media (max-width: 640px) {
  .sidebar {
    width: 240px;
  }
}

/* Compact glass card styling */
.bg-white\/5 {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.bg-white\/5:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

/* Compact navigation link active states */
.bg-lime-400\/20 {
  background: rgba(163, 230, 53, 0.2);
  border-color: rgba(163, 230, 53, 0.3);
}

.bg-purple-400\/20 {
  background: rgba(196, 181, 253, 0.2);
  border-color: rgba(196, 181, 253, 0.3);
}

.bg-cyan-400\/20 {
  background: rgba(34, 211, 238, 0.2);
  border-color: rgba(34, 211, 238, 0.3);
}

.bg-orange-400\/20 {
  background: rgba(251, 146, 60, 0.2);
  border-color: rgba(251, 146, 60, 0.3);
}

.bg-pink-400\/20 {
  background: rgba(244, 114, 182, 0.2);
  border-color: rgba(244, 114, 182, 0.3);
}

/* No scrolling layout - Fixed viewport */
html, body {
  margin: 0;
  padding: 0;
  overflow: hidden;
  height: 100vh;
}

/* Compact header scroll effects */
.header-scrolled {
  backdrop-filter: blur(30px);
  -webkit-backdrop-filter: blur(30px);
  background: rgba(255, 255, 255, 0.08);
  border-bottom-color: rgba(255, 255, 255, 0.2);
}

/* Compact dropdown animation */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.15s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-5px) scale(0.98);
}

/* Compact focus styles for accessibility */
button:focus,
a:focus {
  outline: 1px solid #a3e635;
  outline-offset: 1px;
  border-radius: 6px;
}

/* Compact loading spinner */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Compact touch-friendly adjustments for mobile */
@media (pointer: coarse) {
  button, 
  a[role="button"] {
    min-height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

/* Compact responsive text sizing */
@media (max-width: 480px) {
  .text-lg {
    font-size: 0.875rem;
  }
  
  .text-base {
    font-size: 0.8rem;
  }
  
  .text-sm {
    font-size: 0.75rem;
  }
}

/* Compact sidebar toggle animation */
.sidebar-toggle {
  transition: transform 0.2s ease;
}

.sidebar-open .sidebar-toggle {
  transform: rotate(180deg);
}
</style>