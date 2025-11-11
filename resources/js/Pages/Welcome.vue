<script setup>
import { ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import WorkOrder from '@/Pages/WorkOrders/WorkOrder.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const { props } = usePage();

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}

const selectedWorkOrder = ref(null);
const showWorkOrderModal = ref(false);

async function openWorkOrder(workOrderId) {
  try {
    // Fetch work order with customer details
    const response = await axios.get(`/work-orders/${workOrderId}/details`);
    selectedWorkOrder.value = response.data;
    showWorkOrderModal.value = true;
  } catch (error) {
    console.error('Error loading work order:', error);
    alert('Failed to load work order details');
  }
}

function closeWorkOrderModal() {
  showWorkOrderModal.value = false;
  selectedWorkOrder.value = null;
}
</script>

<style scoped>
/* Custom animations for the blob elements */
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

/* Hover effects */
.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Glass morphism effects */
.backdrop-blur-xl {
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
}

.backdrop-blur-sm {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

/* Responsive grid improvements */
@media (max-width: 640px) {
    .grid.grid-cols-1.sm\\:grid-cols-3 {
        grid-template-columns: 1fr;
    }
}

@media (min-width: 641px) and (max-width: 1023px) {
    .grid.grid-cols-1.sm\\:grid-cols-3 {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .grid.grid-cols-1.sm\\:grid-cols-3 > :last-child {
        grid-column: span 2;
        justify-self: center;
        max-width: 50%;
    }
}

/* Enhanced mobile responsiveness */
@media (max-width: 480px) {
    .text-4xl {
        font-size: 2.5rem;
    }
    
    .text-lg {
        font-size: 1rem;
    }
    
    .space-y-6 > * + * {
        margin-top: 1rem;
    }
    
    .space-y-8 > * + * {
        margin-top: 1.5rem;
    }
}

/* Smooth scrolling and better typography */
html {
    scroll-behavior: smooth;
}

.bg-gradient-to-r.from-lime-400.to-cyan-400 {
    background-image: linear-gradient(to right, #a3e635, #22d3ee);
}

/* Focus states for accessibility */
.btn:focus-visible {
    outline: 2px solid #a3e635;
    outline-offset: 2px;
}

/* Loading state for buttons */
.btn.loading {
    pointer-events: none;
}

.btn.loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    margin: auto;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Enhanced card hover effects */
.feature-card:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

/* Improved text contrast for accessibility */
.text-gray-300 {
    color: #d1d5db;
}

.text-gray-400 {
    color: #9ca3af;
}

/* Better spacing for mobile navigation */
@media (max-width: 640px) {
    nav {
        padding: 1rem;
    }
    
    nav .flex.items-center.space-x-4 div {
        display: block;
    }
    
    nav .flex.items-center.space-x-2 {
        gap: 0.5rem;
    }
}

/* Improved button sizing on mobile */
@media (max-width: 640px) {
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
    
    .btn-md {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
    
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
    }
}
</style>

<template>
    <Head title="Welcome" />
    
    <!-- Modern Hero Section -->
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black relative overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-4 -left-4 w-72 h-72 bg-lime-400/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
            <div class="absolute -top-4 -right-4 w-72 h-72 bg-cyan-400/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-20 w-72 h-72 bg-purple-400/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
        </div>

        <!-- Navigation -->
        <nav class="relative z-10 flex items-center justify-between p-4 lg:p-6">
            <div class="flex items-center space-x-4">
                <ApplicationMark class="w-10 h-10 lg:w-12 lg:h-12 text-lime-400" />
                <div class="hidden sm:block">
                    <h1 class="text-xl lg:text-2xl font-bold text-white">TekDash</h1>
                    <p class="text-xs lg:text-sm text-gray-400">NM Technology CMS</p>
                </div>
            </div>
            
            <div v-if="canLogin" class="flex items-center space-x-2 lg:space-x-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="btn btn-primary btn-sm lg:btn-md"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5 mr-1 lg:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Dashboard
                </Link>

                <template v-else>
                    <Link
                        :href="route('login')"
                        class="btn btn-ghost btn-sm lg:btn-md text-white hover:text-lime-400"
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="btn btn-sm lg:btn-md bg-lime-400 hover:bg-lime-500 text-black border-lime-400 hover:border-lime-500"
                    >
                        Register
                    </Link>
                </template>
            </div>
        </nav>

        <!-- Hero Content -->
        <div class="relative z-10 flex items-center justify-center min-h-[calc(100vh-100px)] px-4 lg:px-6">
            <div class="max-w-6xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                    <!-- Left Column - Content -->
                    <div class="text-center lg:text-left space-y-6 lg:space-y-8">
                        <div class="space-y-4">
                            <div class="inline-flex items-center px-3 py-1 rounded-full bg-lime-400/10 border border-lime-400/20">
                                <span class="text-lime-400 text-sm font-medium">🚀 Advanced CMS Platform</span>
                            </div>
                            
                            <h1 class="text-4xl sm:text-5xl lg:text-7xl font-bold text-white leading-tight">
                                <span class="bg-gradient-to-r from-lime-400 to-cyan-400 bg-clip-text text-transparent">
                                    TekDash
                                </span>
                            </h1>
                            
                            <p class="text-lg sm:text-xl lg:text-2xl text-gray-300 max-w-2xl mx-auto lg:mx-0">
                                Streamline your technology operations with our powerful, 
                                <span class="text-lime-400 font-semibold">modern CMS platform</span> 
                                designed for efficiency and growth.
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link 
                                :href="route('login')" 
                                class="btn btn-lg group bg-lime-400 hover:bg-lime-500 text-black border-lime-400 hover:border-lime-500"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                Get Started
                            </Link>
                            
                            <Link 
                                :href="route('register')" 
                                class="btn btn-lg group bg-transparent hover:bg-lime-400/10 text-lime-400 border-lime-400 hover:border-lime-500 hover:text-lime-300"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                Create Account
                            </Link>
                        </div>

                        <!-- Features List -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:gap-6 mt-8 lg:mt-12">
                            <div class="feature-card flex items-center space-x-3 p-3 rounded-lg bg-white/5 backdrop-blur-sm border border-white/10">
                                <div class="flex-shrink-0 w-8 h-8 bg-lime-400 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="text-white font-medium text-sm lg:text-base">Work Orders</span>
                            </div>
                            
                            <div class="feature-card flex items-center space-x-3 p-3 rounded-lg bg-white/5 backdrop-blur-sm border border-white/10">
                                <div class="flex-shrink-0 w-8 h-8 bg-lime-400 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="text-white font-medium text-sm lg:text-base">Customer Management</span>
                            </div>
                            
                            <div class="feature-card flex items-center space-x-3 p-3 rounded-lg bg-white/5 backdrop-blur-sm border border-white/10">
                                <div class="flex-shrink-0 w-8 h-8 bg-lime-400 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="text-white font-medium text-sm lg:text-base">Real-time Analytics</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Visual Element -->
                    <div class="relative">
                        <div class="relative z-10 bg-white/5 backdrop-blur-xl rounded-2xl p-6 lg:p-8 border border-white/10 shadow-2xl">
                            <!-- Dashboard Preview Mock -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-white font-semibold text-lg">Dashboard Preview</h3>
                                    <div class="flex space-x-1">
                                        <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                                        <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                                        <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                                    </div>
                                </div>
                                
                                <div class="bg-black/20 rounded-lg p-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="h-4 bg-lime-400 rounded w-24"></div>
                                        <div class="h-4 bg-gray-400 rounded w-16"></div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="h-3 bg-gray-500 rounded w-full"></div>
                                        <div class="h-3 bg-gray-500 rounded w-3/4"></div>
                                        <div class="h-3 bg-gray-500 rounded w-1/2"></div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 pt-2">
                                        <div class="h-12 bg-gradient-to-br from-lime-400/20 to-lime-400/5 rounded"></div>
                                        <div class="h-12 bg-gradient-to-br from-cyan-400/20 to-cyan-400/5 rounded"></div>
                                        <div class="h-12 bg-gradient-to-br from-purple-400/20 to-purple-400/5 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Elements -->
                        <div class="absolute -top-4 -right-4 w-16 h-16 bg-lime-400/20 rounded-full animate-pulse"></div>
                        <div class="absolute -bottom-4 -left-4 w-12 h-12 bg-cyan-400/20 rounded-full animate-pulse delay-1000"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Order Modal -->
        <div v-if="selectedWorkOrder">
            <WorkOrder
                :work-order="selectedWorkOrder"
                :show-modal="showWorkOrderModal"
                :users="$page.props.users || []"
                @close="closeWorkOrderModal"
            />
        </div>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-white/10 backdrop-blur-sm bg-black/20">
            <div class="max-w-6xl mx-auto px-4 lg:px-6 py-6">
                <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                    <div class="flex items-center space-x-4">
                        <ApplicationMark class="w-6 h-6 text-lime-400" />
                        <span class="text-gray-300 text-sm">© 2025 NM Technology. All rights reserved.</span>
                    </div>
                    
                    <div class="flex items-center space-x-4 text-xs text-gray-400">
                        <span>TekDash v{{ laravelVersion }}</span>
                        <span>•</span>
                        <span>PHP v{{ phpVersion }}</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>