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
  <div>
    <ToastContainer class="fixed top-0 left-0 w-full z-[99999] pointer-events-none" />
    <Head :title="title" />
    <Banner />
    <div class="min-h-screen bg-gray-900 relative isolate overflow-hidden bg-opacity-95 flex">
      <!-- Sidebar Toggle Button (always visible, fixed at top left) -->
      <button
        class="fixed top-4 left-4 z-50 bg-gray-900/90 border border-gray-700 rounded-full p-2 shadow-lg hover:bg-gray-800 transition-all hover:border-lime-400/50"
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
      <aside :class="['shadcn-sidebar', sidebarOpen ? 'left-0' : '-left-72', 'fixed top-0 z-50 h-full w-72 transition-all duration-300 bg-gray-900/80 border-r border-gray-800 backdrop-blur-lg']">
        <div class="flex flex-col h-full">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
            <span class="flex items-center gap-2 text-xl font-bold text-lime-400">
              <ApplicationMark class="h-8 w-8" />
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
            <!-- Customers with dropdown -->
            <div class="relative">
              <button 
                @click="toggleCustomerDropdown"
                @keydown.enter.prevent="toggleCustomerDropdown"
                @keydown.space.prevent="toggleCustomerDropdown"
                @keydown.down.prevent="toggleCustomerDropdown(); focusedCustomerIndex = 0; nextTick(() => focusCustomerTile())"
                aria-haspopup="true"
                :aria-expanded="showCustomerDropdown"
                :aria-controls="showCustomerDropdown ? 'customer-dropdown-content' : undefined"
                aria-label="Customers dropdown"
                role="button"
                class="shadcn-nav-link shadcn-nav-link-customers w-full flex justify-between items-center customer-dropdown-button"
              >
                <div class="flex items-center">
                  <svg class="inline-block mr-2 h-5 w-5 text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 010 7.75" /></svg>
                  Customers
                </div>
                <svg 
                  class="h-4 w-4 transition-transform duration-200" 
                  :class="showCustomerDropdown ? 'rotate-180' : ''" 
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>
              
              <!-- Dropdown content -->
              <div 
                v-if="showCustomerDropdown" 
                id="customer-dropdown-content"
                class="customer-dropdown mt-1 pl-6 pr-2 pb-2 overflow-hidden"
                role="menu"
                aria-label="Customer navigation"
                aria-orientation="vertical"
              >
                <!-- Loading indicator -->
                <div v-if="isLoadingCustomers" class="py-6 flex flex-col items-center justify-center">
                  <svg class="animate-spin h-6 w-6 text-cyan-400 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span class="text-xs text-cyan-400">Loading customers...</span>
                </div>
                
                <!-- Error state -->
                <div v-else-if="customerError" class="py-4 text-center px-3">
                  <svg class="w-8 h-8 text-amber-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                  </svg>
                  <p class="text-xs text-gray-300 font-medium">Unable to load customers</p>
                  <button @click="retryFetchCustomers" 
                          class="mt-2 text-xxs bg-gray-800/70 hover:bg-gray-700/70 text-cyan-400 px-2 py-1 rounded-md transition-colors">
                    Try again
                  </button>
                </div>
                
                <!-- Empty state -->
                <div v-else-if="customers.length === 0" class="py-4 text-center">
                  <svg class="w-8 h-8 text-gray-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  <p class="text-sm text-gray-400">No customers found</p>
                </div>
                
                <!-- Customer tiles -->
                <div v-else class="grid grid-cols-3 gap-2 max-h-[280px] overflow-y-auto pt-2 px-1 pr-2 pb-1 customers-grid">
                  <Link 
                    v-for="(customer, index) in customers" 
                    :key="customer && customer.id ? customer.id : index"
                    :href="customer && customer.id ? `/customers/${customer.id}` : '/customers'"
                    :style="{ animationDelay: `${index * 0.05}s` }"
                    :tabindex="showCustomerDropdown ? 0 : -1"
                    role="menuitem"
                    :aria-label="customer ? (customer.business_name || customer.poc_name || `Customer ${customer.id || ''}`) : 'Customer'"
                    :aria-selected="focusedCustomerIndex === index"
                    :class="[
                      'customer-tile group p-2 rounded-md transition-all hover:scale-[1.03] hover:bg-gray-800/50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-cyan-500 focus:ring-offset-gray-900 border border-transparent hover:border-gray-700/50',
                      focusedCustomerIndex === index ? 'bg-gray-800/50 border-gray-600/70 ring-2 ring-cyan-500 ring-offset-1 ring-offset-gray-900' : ''
                    ]"
                    @keydown.space.prevent="navigateToCustomer(index)"
                    @focus="focusedCustomerIndex = index"
                  >
                    <div class="flex flex-col items-center text-center">
                      <div 
                        class="w-12 h-12 rounded-full flex items-center justify-center mb-1.5 text-white font-semibold shadow-lg border border-gray-700/50 group-hover:border-gray-600 transition-all" 
                        :class="getCustomerColor(customer ? customer.id : null)"
                      >
                        <span class="text-lg">
                          {{ getCustomerInitials(customer) }}
                        </span>
                      </div>
                      <div class="flex flex-col min-h-[32px] justify-center">
                        <span class="text-xs font-medium truncate w-full text-gray-200 group-hover:text-white transition-colors">
                          {{ 
                            customer ? 
                            (customer.business_name || customer.poc_name || `Customer ${customer.id || ''}`) : 
                            'Customer' 
                          }}
                        </span>
                        <span v-if="customer && customer.business_name && customer.poc_name" 
                              class="text-xxs truncate w-full text-gray-400 group-hover:text-gray-300 transition-colors mt-0.5">
                          {{ customer.poc_name }}
                        </span>
                      </div>
                    </div>
                  </Link>
                </div>
                
                <!-- View all customers link -->
                <Link 
                  href="/customers" 
                  class="mt-3 flex items-center justify-center py-2 w-full rounded-md text-cyan-400 text-sm font-medium hover:bg-gray-800/50 border border-gray-700/40 hover:border-gray-600 transition-all focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-gray-900"
                >
                  <span>View All Customers</span>
                  <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                  </svg>
                </Link>
              </div>
            </div>
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
          <!-- User profile and notifications in sidebar -->
          <div class="px-4 py-4 border-t border-gray-800">
            <!-- User avatar and profile dropdown -->
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-3">
                <div class="avatar">
                  <div class="w-10 rounded-full">
                    <img :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" />
                  </div>
                </div>
                <div>
                  <p class="font-medium text-white">{{ $page.props.auth.user.name }}</p>
                  <p class="text-xs text-gray-400">{{ $page.props.auth.user.email }}</p>
                </div>
              </div>
              <NotificationsDropdown class="sidebar-notifications" />
            </div>

            <!-- User quick links -->
            <div class="grid grid-cols-2 gap-2 mb-4">
              <Link href="#" class="shadcn-quick-link">
                <span>Profile</span>
              </Link>
              <Link href="http://127.0.0.1:8000/teams/1" class="shadcn-quick-link">
                <span>Settings</span>
              </Link>
            </div>

            <!-- Logout button -->
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
      <div class="flex-1 flex flex-col min-h-screen transition-all duration-300"
           :class="[sidebarOpen ? 'md:ml-72' : 'ml-0']">
        <!-- Top navbar UNDER sidebar, lower z-index -->
        <nav class="navbar fixed-navbar bg-base-100 shadow-sm top-0 left-0 right-0 z-30 glass-header transition-all duration-300"
             :class="{ 'scrolled': isScrolled }">
          <div class="navbar-start flex items-center justify-start">
            <!-- Space for the sidebar toggle button on small screens -->
            <div class="w-16 md:hidden"></div>
          </div>
          <div class="navbar-center flex justify-center items-center">
            <!-- Centered TekDash branding -->
            <Link href="/dashboard" class="btn-ghost text-xl flex items-center justify-center gap-2">
              <ApplicationMark class="h-7 w-7" />
              <span class="font-bold text-lime-400">TekDash</span>
            </Link>
          </div>
          <div class="navbar-end flex items-center justify-end">
            <!-- Empty div to maintain navbar balance -->
            <div class="w-16 md:w-4"></div>
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
    width: 100%;
    max-width: 100%;
    margin-bottom: 8px;
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
  height: 64px; /* Fixed height for consistency */
  display: flex;
  align-items: center;
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
  width: 18rem; /* w-72 = 18rem */
  will-change: transform;
  overflow-y: auto;
  overflow-x: hidden;
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
  white-space: nowrap;
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

/* Styles for search in sidebar */
.shadcn-sidebar .search-container {
  margin-bottom: 1rem;
  width: 100%;
}

.shadcn-sidebar :deep(input) {
  background-color: rgba(17, 24, 39, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #f3f4f6;
  font-size: 0.875rem; /* Slightly smaller font for better fit */
}

.shadcn-sidebar :deep(.input) {
  height: 2.5rem; /* Slightly more compact */
  min-height: 2.5rem;
}

.shadcn-sidebar :deep(.kbd) {
  font-size: 0.65rem; /* Smaller keyboard shortcuts */
  padding: 0.15rem 0.25rem;
}

.shadcn-sidebar :deep(input:focus) {
  border-color: rgba(163, 230, 53, 0.5);
  box-shadow: 0 0 0 2px rgba(163, 230, 53, 0.2);
}

.shadcn-sidebar :deep(input::placeholder) {
  color: rgba(243, 244, 246, 0.5);
}

/* Sidebar notification and profile styles */
.sidebar-notifications :deep(.dropdown-content) {
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 0.5rem;
}

.shadcn-quick-link {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem;
  background-color: rgba(31, 41, 55, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.375rem;
  color: #f3f4f6;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.shadcn-quick-link:hover {
  background-color: rgba(31, 41, 55, 1);
  border-color: rgba(163, 230, 53, 0.5);
}

/* Navbar styling for centered content */
.navbar-center .btn-ghost {
  padding: 0.5rem;
  transition: all 0.3s ease;
  margin: 0 auto;
}

.navbar-center .btn-ghost:hover {
  background: rgba(163, 230, 53, 0.08);
}

/* Customer dropdown styles */
.customer-dropdown {
  background: rgba(17, 24, 39, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 0.6rem;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 
              0 8px 10px -6px rgba(0, 0, 0, 0.2),
              0 0 0 1px rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  animation: slideDown 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  transform-origin: top center;
  perspective: 800px;
}

/* Animation for customer tiles */
.customer-tile {
  animation: fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
  opacity: 0;
}

@keyframes slideDown {
  0% {
    opacity: 0;
    transform: translateY(-8px) scale(0.98) rotateX(-5deg);
    clip-path: polygon(0% 0%, 100% 0%, 100% 0%, 0% 0%);
  }
  20% {
    clip-path: polygon(0% 0%, 100% 0%, 100% 20%, 0% 20%);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1) rotateX(0);
    clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%);
  }
}

@keyframes fadeInUp {
  0% {
    opacity: 0;
    transform: translateY(10px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Customer tiles styling */
.customer-dropdown .grid a {
  position: relative;
  overflow: hidden;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.customer-dropdown .grid a:hover {
  background-color: rgba(31, 41, 55, 0.7);
  box-shadow: 0 4px 8px -2px rgba(0, 0, 0, 0.3), 
              0 2px 4px -1px rgba(0, 0, 0, 0.2);
}

.customer-dropdown .grid a:hover::before,
.customer-dropdown .grid a:focus::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at var(--x) var(--y), rgba(255, 255, 255, 0.06) 0%, transparent 60%);
  pointer-events: none;
}

.customer-dropdown .grid a:active {
  transform: scale(0.97);
}

/* Keyboard focus styles */
.customer-dropdown .grid a:focus-visible {
  outline: none;
  box-shadow: 0 0 0 2px rgba(8, 145, 178, 0.6), 0 0 0 4px rgba(8, 145, 178, 0.2);
  background-color: rgba(31, 41, 55, 0.7);
  border-color: rgba(8, 145, 178, 0.4);
  transform: scale(1.03);
}

/* Add a special indicator for keyboard navigation */
.customer-dropdown .grid a[aria-selected="true"] {
  position: relative;
  z-index: 10;
}

.customer-dropdown .grid a[aria-selected="true"]::after {
  content: '';
  position: absolute;
  inset: 0;
  border: 2px solid rgba(8, 145, 178, 0.7);
  border-radius: 0.375rem;
  pointer-events: none;
  box-shadow: 0 0 8px rgba(8, 145, 178, 0.4);
}

/* Custom scrollbar for customer dropdown */
.customer-dropdown .grid::-webkit-scrollbar {
  width: 4px;
}

.customer-dropdown .grid::-webkit-scrollbar-track {
  background: rgba(17, 24, 39, 0.3);
  border-radius: 4px;
}

.customer-dropdown .grid::-webkit-scrollbar-thumb {
  background: rgba(8, 145, 178, 0.5);
  border-radius: 4px;
}

.customer-dropdown .grid::-webkit-scrollbar-thumb:hover {
  background: rgba(8, 145, 178, 0.7);
}

/* Make TekDash logo always visible and centered */
@media (min-width: 768px) {
  .shadcn-sidebar {
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
  
  /* Center logo on mobile */
  .navbar-center {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    z-index: 5;
  }
}
</style>