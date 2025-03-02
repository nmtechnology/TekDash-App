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

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-900">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
            <div class="flex justify-center mb-6">
                <ApplicationMark class="w-16 h-16" />
            </div>

            <h1 class="text-2xl font-bold text-center text-green-400 mb-6">Welcome Back</h1>

            <div v-if="status" class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="email" value="Email" class="text-gray-700" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-5">
                    <div class="flex justify-between">
                        <InputLabel for="password" value="Password" class="text-gray-700" />
                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-lime-400 hover:text-lime-600 transition">
                            Forgot password?
                        </Link>
                    </div>
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition"
                        required
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="block mt-5">
                    <label class="flex items-center">
                        <Checkbox v-model:checked="form.remember" name="remember" />
                        <span class="ms-2 text-sm text-lime-600">Remember me</span>
                    </label>
                </div>

                <div class="mt-6">
                    <PrimaryButton class="w-full justify-center py-3" :class="{ 'opacity-75': form.processing }" :disabled="form.processing">
                        <span v-if="form.processing">Processing...</span>
                        <span v-else>Log in</span>
                    </PrimaryButton>
                </div>
                
                <div class="mt-6 text-center text-sm">
                    <span class="text-green-400">Don't have an account?</span>
                    <Link :href="route('register')" class="ml-1 text-lime-400 hover:text-lime-600 transition font-medium">
                        Sign up
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.bg-gray-100 {
    background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}
</style>