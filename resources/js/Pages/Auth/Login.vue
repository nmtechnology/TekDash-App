<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import axios from 'axios';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = async () => {
    // Ensure we have a fresh CSRF token before submitting
    try {
        const response = await axios.get('/csrf/refresh', {
            withCredentials: true,
            headers: { 'Accept': 'application/json' }
        });
        
        if (response.data?.token) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.token;
            axios.defaults.headers.common['X-XSRF-TOKEN'] = response.data.token;
        }
    } catch (error) {
        console.warn('Failed to refresh CSRF token before login:', error);
    }

    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onError: (errors) => {
            if (errors?.response?.status === 419) {
                // If we get a CSRF error, try to refresh and retry once
                submit();
            }
        },
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black relative overflow-hidden flex items-center justify-center p-4">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <!-- Desktop blobs -->
            <div class="hidden md:block absolute top-10 left-10 w-96 h-96 bg-lime-400/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
            <div class="hidden md:block absolute top-20 right-20 w-96 h-96 bg-cyan-400/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
            <div class="hidden md:block absolute bottom-20 left-1/3 w-96 h-96 bg-purple-400/10 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
            
            <!-- Mobile-optimized blobs -->
            <div class="md:hidden absolute top-20 left-5 w-64 h-64 bg-lime-400/8 rounded-full mix-blend-multiply filter blur-xl opacity-60 animate-blob"></div>
            <div class="md:hidden absolute top-32 right-5 w-48 h-48 bg-cyan-400/8 rounded-full mix-blend-multiply filter blur-xl opacity-60 animate-blob animation-delay-2000"></div>
            <div class="md:hidden absolute bottom-32 left-1/4 w-56 h-56 bg-purple-400/8 rounded-full mix-blend-multiply filter blur-xl opacity-60 animate-blob animation-delay-4000"></div>
        </div>

        <!-- Login Card -->
        <div class="relative z-10 w-full max-w-md mx-auto">
            <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 sm:p-8 border border-white/10 shadow-2xl">
                <!-- Header -->
                <div class="text-center mb-6 sm:mb-8">
                    <div class="flex justify-center mb-3 sm:mb-4">
                        <div class="relative">
                            <ApplicationMark class="w-12 h-12 sm:w-16 sm:h-16 text-lime-400" />
                            <div class="absolute -inset-1 bg-gradient-to-r from-lime-400 to-cyan-400 rounded-full opacity-20 blur animate-pulse"></div>
                        </div>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold">
                        <span class="bg-gradient-to-r from-lime-400 to-cyan-400 bg-clip-text text-transparent">
                            Welcome Back
                        </span>
                    </h1>
                    <p class="text-gray-300 mt-2 text-sm sm:text-base">Sign in to your TekDash account</p>
                </div>

                <!-- Status Message -->
                <div v-if="status" class="mb-4 sm:mb-6 p-3 sm:p-4 rounded-lg bg-green-400/10 border border-green-400/20">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-green-400 text-xs sm:text-sm font-medium">{{ status }}</span>
                    </div>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-4 sm:space-y-6">
                    <!-- Email Field -->
                    <div>
                        <InputLabel for="email" class="text-gray-300 font-medium mb-2 block text-sm sm:text-base">
                            Email Address
                        </InputLabel>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="modern-input pl-9 sm:pl-10"
                                placeholder="Enter your email"
                                required
                                autofocus
                                autocomplete="username"
                            />
                        </div>
                        <InputError class="mt-2 text-red-400 text-xs sm:text-sm" :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <InputLabel for="password" class="text-gray-300 font-medium text-sm sm:text-base">
                                Password
                            </InputLabel>
                            <Link 
                                v-if="canResetPassword" 
                                :href="route('password.request')" 
                                class="text-lime-400 hover:text-lime-300 text-xs sm:text-sm font-medium transition-colors"
                            >
                                Forgot?
                            </Link>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="modern-input pl-9 sm:pl-10"
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                            />
                        </div>
                        <InputError class="mt-2 text-red-400 text-xs sm:text-sm" :message="form.errors.password" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer group">
                            <Checkbox 
                                v-model:checked="form.remember" 
                                name="remember"
                                class="modern-checkbox"
                            />
                            <span class="ml-3 text-gray-300 group-hover:text-white transition-colors text-sm">
                                Remember me for 30 days
                            </span>
                        </label>
                    </div>

                    <!-- Login Button -->
                    <div>
                        <button
                            type="submit"
                            class="modern-btn w-full group"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing" class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm sm:text-base">Signing in...</span>
                            </span>
                            <span v-else class="flex items-center justify-center">
                                <svg class="mr-2 h-4 w-4 sm:h-5 sm:w-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                <span class="text-sm sm:text-base font-semibold">Sign In</span>
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="my-6 sm:my-8">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-white/20"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-transparent text-gray-400 text-xs sm:text-sm">New to TekDash?</span>
                        </div>
                    </div>
                </div>

                <!-- Register Link -->
                <div class="text-center">
                    <Link 
                        :href="route('register')" 
                        class="signup-btn group w-full inline-flex items-center justify-center"
                    >
                        <svg class="mr-2 h-4 w-4 sm:h-5 sm:w-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <span class="text-sm sm:text-base">Create Account</span>
                    </Link>
                </div>
            </div>

            <!-- Back to Homepage -->
            <div class="text-center mt-4 sm:mt-6">
                <Link 
                    :href="route('welcome')" 
                    class="text-gray-400 hover:text-white transition-colors text-xs sm:text-sm flex items-center justify-center"
                >
                    <svg class="mr-2 h-3 w-3 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Back to Homepage</span>
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Blob animations matching the homepage */
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

/* Modern input styling with responsive padding */
.modern-input {
    width: 100%;
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
    padding-right: 1rem;
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.5rem;
    color: white;
    backdrop-filter: blur(4px);
    transition: all 0.3s ease;
    font-size: 1rem;
    max-width: 100%;
    box-sizing: border-box;
}

@media (max-width: 640px) {
    .modern-input {
        padding-top: 0.875rem;
        padding-bottom: 0.875rem;
        padding-right: 1rem;
        font-size: 1rem;
    }
}

.modern-input::placeholder {
    color: rgba(156, 163, 175, 1);
}

.modern-input:focus {
    outline: none;
    background-color: rgba(255, 255, 255, 0.08);
    border-color: transparent;
    box-shadow: 0 0 0 2px rgba(163, 230, 53, 0.3);
}

/* Modern checkbox styling with responsive size */
.modern-checkbox {
    width: 1.125rem;
    height: 1.125rem;
    border-radius: 0.25rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    background-color: rgba(255, 255, 255, 0.05);
    transition: all 0.2s ease;
}

@media (max-width: 640px) {
    .modern-checkbox {
        width: 1.25rem;
        height: 1.25rem;
    }
}

.modern-checkbox:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(163, 230, 53, 0.4);
    transform: scale(1.1);
}

.modern-checkbox:checked {
    background-color: #a3e635;
    border-color: #a3e635;
}

/* Modern button styling with responsive text */
.modern-btn {
    width: 100%;
    padding: 0.875rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    color: black;
    background: linear-gradient(to right, #a3e635, #84cc16);
    border: none;
    cursor: pointer;
    transform: scale(1);
    transition: all 0.2s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    font-size: 1rem;
    min-height: 48px;
    max-width: 100%;
    box-sizing: border-box;
}

@media (max-width: 640px) {
    .modern-btn {
        padding: 1rem 1.5rem;
        font-size: 1rem;
        min-height: 52px;
    }
}

.modern-btn:hover {
    background: linear-gradient(to right, #84cc16, #65a30d);
    transform: translateY(-1px);
    box-shadow: 0 10px 25px rgba(163, 230, 53, 0.3);
}

.modern-btn:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(163, 230, 53, 0.4);
}

.modern-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: scale(1);
}

/* Signup button styling with responsive design */
.signup-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 0.875rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
    color: white;
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-decoration: none;
    backdrop-filter: blur(4px);
    transform: scale(1);
    transition: all 0.2s ease;
    font-size: 1rem;
    min-height: 48px;
    max-width: 100%;
    box-sizing: border-box;
}

@media (max-width: 640px) {
    .signup-btn {
        padding: 1rem 1.5rem;
        font-size: 1rem;
        min-height: 52px;
    }
}

.signup-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
    color: white;
    text-decoration: none;
}

.signup-btn:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(163, 230, 53, 0.4);
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

/* Input field animations */
.modern-input:focus::placeholder {
    opacity: 0.5;
    transform: translateX(4px);
    transition: all 0.3s ease;
}

/* Loading animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Form field focus enhancement */
.modern-input:focus + .absolute {
    color: #a3e635;
}

/* Improved mobile responsiveness */
@media (max-width: 640px) {
    .bg-white\/5 {
        margin: 0;
        padding: 1.5rem;
        border-radius: 1rem;
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
        width: calc(100% - 2rem);
        box-sizing: border-box;
    }
    
    body {
        overflow-x: hidden;
    }
    
    /* Reduce blob sizes for mobile performance */
    .animate-blob {
        animation-duration: 10s;
    }
    
    /* Ensure better touch targets */
    button, a, input, .modern-checkbox {
        min-height: 44px;
    }
    
    /* Prevent horizontal overflow */
    .modern-btn, .signup-btn, .modern-input {
        max-width: 100%;
        box-sizing: border-box;
    }
}

/* Extra small screens */
@media (max-width: 375px) {
    .bg-white\/5 {
        padding: 1rem;
        margin: 0.5rem;
        width: calc(100% - 1rem);
    }
    
    .modern-btn, .signup-btn {
        padding: 1.125rem 1rem;
        font-size: 0.9rem;
    }
    
    .modern-input {
        font-size: 0.9rem;
        padding: 1rem 0.875rem 1rem 2rem;
    }
}

/* Accessibility improvements */
.modern-btn:focus-visible,
.signup-btn:focus-visible,
.modern-input:focus-visible {
    outline: 2px solid #a3e635;
    outline-offset: 2px;
}

/* Form validation states */
.modern-input.error {
    border-color: #f87171;
}

.modern-input.error:focus {
    box-shadow: 0 0 0 2px rgba(248, 113, 113, 0.3);
}

/* Smooth transitions for all interactive elements */
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Enhanced focus states */
.modern-checkbox:focus {
    transform: scale(1.1);
}

/* Better link hover effects */
a:hover {
    text-decoration: none;
}

/* Card entrance animation with mobile optimization */
.bg-white\/5 {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    0% {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Mobile-first responsive design improvements */
@media (min-width: 375px) {
    .modern-input {
        font-size: 1rem;
    }
}

@media (min-width: 640px) {
    .bg-white\/5 {
        padding: 2rem;
    }
    
    .modern-btn, .signup-btn {
        font-size: 1rem;
        min-height: 48px;
    }
}

/* Landscape mobile optimization */
@media (max-width: 896px) and (orientation: landscape) {
    .bg-white\/5 {
        max-height: calc(100vh - 1rem);
        overflow-y: auto;
        padding: 1rem 1.5rem;
    }
    
    .text-center.mb-6 {
        margin-bottom: 1rem;
    }
    
    .space-y-4 > * + * {
        margin-top: 0.75rem;
    }
}

/* High DPI screens */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .backdrop-blur-xl {
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
}

/* Pulse animation for logo backdrop */
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 0.2;
    }
    50% {
        opacity: 0.4;
    }
}
</style>