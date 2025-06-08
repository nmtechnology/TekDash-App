<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
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

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-900 relative isolate overflow-hidden">
        <!-- Background Element -->
        <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
            <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[46.125rem] -translate-x-1/2 rotate-[45deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 20.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
        </div>
        
        <div class="glossy-card w-full sm:max-w-md mx-auto">
            <div class="glossy-header py-6 -mx-6 -mt-4 px-6">
                <div class="flex justify-center">
                    <ApplicationMark class="w-16 h-16" />
                </div>
                <h1 class="text-2xl font-bold text-center text-green-400 mt-4">TekDash</h1>
            </div>

            <div v-if="status" class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded mx-6">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="glossy-content mx-6">
                <div>
                    <InputLabel for="email" value="Email" class="text-gray-300" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full bg-gray-800 border-gray-700 text-white rounded-md shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-300 focus:ring-opacity-50 transition"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-5">
                    <div class="flex justify-between">
                        <InputLabel for="password" value="Password" class="text-gray-300" />
                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-lime-400 hover:text-lime-600 transition">
                            Forgot password?
                        </Link>
                    </div>
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full bg-gray-800 border-gray-700 text-white rounded-md shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-300 focus:ring-opacity-50 transition"
                        required
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="block mt-5">
                    <label class="flex items-center">
                        <Checkbox v-model:checked="form.remember" name="remember" />
                        <span class="ms-2 text-sm text-white">Remember me</span>
                    </label>
                </div>

                <div class="mt-6">
                    <PrimaryButton class="w-full justify-center py-3 glossy-btn" :class="{ 'opacity-95': form.processing }" :disabled="form.processing">
                        <span v-if="form.processing">Processing...</span>
                        <span v-else>Log in</span>
                    </PrimaryButton>
                </div>
            </form>
                
            <div class="glossy-footer py-4 -mx-6 -mb-4 px-6 mt-6">
                <div class="text-center text-sm">
                    <span class="text-green-400">Don't have an account?</span>
                    <Link :href="route('register')" class="ml-1 text-white hover:text-lime-600 transition font-medium">
                        Sign up
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.bg-gray-100 {
    background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

/* Enhanced modal layout */
.glossy-card {
  display: flex;
  flex-direction: column;
  min-height: fit-content; /* Change from fixed height to fit content */
  width: 100%;
  margin: 2rem auto;
  background: linear-gradient(135deg, rgba(17, 24, 39, 0.95), rgba(31, 41, 55, 0.85));
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 0.5rem;
}

/* Fixed header styling */
.glossy-header {
  background: linear-gradient(180deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.85) 100%);
  position: relative;
  overflow: hidden;
}

/* Scrollable content area */
.overflow-y-auto {
  flex-grow: 1;
  overflow-y: auto;
  scrollbar-color: rgba(75, 85, 99, 0.5) rgba(17, 24, 39, 0.3);
  scrollbar-width: thin;
}

/* Fixed footer styling */
.glossy-footer {
  background: linear-gradient(0deg, rgba(31, 41, 55, 0.9) 0%, rgba(17, 24, 39, 0.85) 100%);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  position: relative;
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
}

.glossy-content {
  background: linear-gradient(145deg, rgba(31, 41, 55, 0.6), rgba(17, 24, 39, 0.4));
  border: 1px solid rgba(255, 255, 255, 0.05);
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
  padding: 1.5rem;
  margin: 1rem 0;
}

/* Button styling to match the glossy theme */
.glossy-btn {
  background: linear-gradient(135deg, rgba(163, 230, 53, 0.8), rgba(132, 204, 22, 0.8));
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.glossy-btn:hover {
  background: linear-gradient(135deg, rgba(163, 230, 53, 0.9), rgba(132, 204, 22, 0.9));
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
</style>