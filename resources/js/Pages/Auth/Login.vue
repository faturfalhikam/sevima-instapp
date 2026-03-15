<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

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

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Email/Username Field -->
            <div class="space-y-2">
                <InputLabel for="email" value="Username or Email" />
                <TextInput id="email" v-model="form.email" type="email" icon="person" placeholder="Enter your username"
                    required autofocus autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Password Field -->
            <div class="space-y-2">
                <InputLabel for="password" value="Password" />
                <TextInput id="password" v-model="form.password" type="password" icon="lock"
                    placeholder="Enter your password" required autocomplete="current-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>


            <!-- Login Button -->
            <PrimaryButton class="mt-6" :disabled="form.processing">
                Log In
            </PrimaryButton>
        </form>

        <!-- Sign Up Link -->
        <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 text-center">
            <p class="text-slate-600 dark:text-slate-400 text-sm">
                Don't have an account?
                <Link :href="route('register')" class="text-primary font-bold hover:underline ml-1">
                    Sign up
                </Link>
            </p>
        </div>
    </AuthenticationCard>
</template>
