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

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    // Simple approach - let the built-in Inertia form handling do its job
    // Inertia will use our configured axios which already has withCredentials=true
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
        onError: (errors) => {
            console.error('Registration errors:', errors);
        }
    });
};
</script>

<template>
    <Head title="Register" />

    <AuthenticationCard>
        <template #logo>
            <ApplicationMark class="w-20 h-20" />
        </template>

        <form @submit.prevent="submit" class="glossy-card p-6">
            <div>
                <InputLabel for="name" value="Name" class="text-gray-300" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-white rounded-md shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-300 focus:ring-opacity-50 transition"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" class="text-gray-300" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-white rounded-md shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-300 focus:ring-opacity-50 transition"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" class="text-gray-300" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-white rounded-md shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-300 focus:ring-opacity-50 transition"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" class="text-gray-300" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-white rounded-md shadow-sm focus:border-lime-500 focus:ring focus:ring-lime-300 focus:ring-opacity-50 transition"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

                        <div class="ms-2 text-white">
                            I agree to the <a target="_blank" :href="route('terms.show')" class="underline text-sm text-lime-400 hover:text-lime-600 transition">Terms of Service</a> and <a target="_blank" :href="route('policy.show')" class="underline text-sm text-lime-400 hover:text-lime-600 transition">Privacy Policy</a>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link :href="route('login')" class="underline text-sm text-lime-400 hover:text-lime-600 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 transition">
                    Already registered?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>

<style scoped>
.glossy-card {
    background: linear-gradient(135deg, rgba(17, 24, 39, 0.95), rgba(31, 41, 55, 0.85));
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border-radius: 1rem;
}
</style>
