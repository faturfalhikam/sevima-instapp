<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name,
    bio: props.user.bio ?? '',
    photo: null,
});

// Photo preview
const preview = ref(props.user.profile_photo_url);
const photoInput = ref(null);

const openPhotoPicker = () => photoInput.value?.click();

const handlePhotoChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    form.photo = file;
    const reader = new FileReader();
    reader.onload = (e) => { preview.value = e.target.result; };
    reader.readAsDataURL(file);
};

const bioLength = computed(() => form.bio.length);

const submit = () => {
    form.post('/profile/edit', { forceFormData: true });
};
</script>

<template>

    <Head title="Edit Profile" />

    <div
        class="relative flex h-auto min-h-screen w-full flex-col bg-white dark:bg-background-dark font-display max-w-lg mx-auto shadow-xl">

        <!-- Header -->
        <header
            class="flex items-center gap-3 bg-white dark:bg-background-dark px-4 py-3 border-b border-slate-100 dark:border-slate-800 sticky top-0 z-10">
            <Link href="/profile"
                class="flex size-9 shrink-0 items-center justify-center rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-slate-700 dark:text-slate-300">
                <span class="material-symbols-outlined">close</span>
            </Link>
            <h1 class="flex-1 text-base font-bold text-slate-900 dark:text-slate-100">
                Edit profile
            </h1>
            <button @click="submit" :disabled="form.processing"
                class="text-primary text-sm font-bold hover:opacity-80 disabled:opacity-40 transition-opacity">
                <span v-if="form.processing" class="flex items-center gap-1">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                    </svg>
                </span>
                <span v-else>Save</span>
            </button>
        </header>

        <!-- Errors -->
        <div v-if="Object.keys(form.errors).length"
            class="mx-4 mt-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg p-3">
            <p v-for="(error, field) in form.errors" :key="field" class="text-red-600 dark:text-red-400 text-sm">
                {{ error }}
            </p>
        </div>

        <!-- Avatar picker -->
        <div class="flex flex-col items-center pt-6 pb-4 gap-2">
            <div class="relative cursor-pointer group" @click="openPhotoPicker">
                <div class="w-20 h-20 rounded-full bg-center bg-cover border-2 border-primary/30"
                    :style="`background-image: url('${preview}')`" />
                <!-- Overlay -->
                <div
                    class="absolute inset-0 rounded-full bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-xl">photo_camera</span>
                </div>
            </div>
            <button type="button" @click="openPhotoPicker"
                class="text-sm font-semibold text-primary hover:opacity-80 transition-opacity">
                Change profile photo
            </button>
            <input ref="photoInput" type="file" accept="image/*" class="hidden" @change="handlePhotoChange" />
        </div>

        <!-- Form fields -->
        <div class="px-5 space-y-5 pb-10">

            <!-- Name -->
            <div class="space-y-1">
                <label
                    class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Name</label>
                <input v-model="form.name" type="text" maxlength="255" placeholder="Your name"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary/40 transition" />
                <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
            </div>

            <!-- Bio -->
            <div class="space-y-1">
                <label
                    class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Bio</label>
                <textarea v-model="form.bio" maxlength="500" rows="4" placeholder="Tell us about yourself…"
                    class="w-full resize-none bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary/40 transition" />
                <div class="flex justify-end">
                    <span class="text-xs text-slate-400">{{ bioLength }}/500</span>
                </div>
                <p v-if="form.errors.bio" class="text-xs text-red-500">{{ form.errors.bio }}</p>
            </div>

        </div>

    </div>
</template>
