<script setup>
import { ref, computed } from 'vue';
import { Head, usePage, useForm, Link } from '@inertiajs/vue3';

const page = usePage();

const currentUser = computed(() => {
    const user = page.props.auth?.user;
    if (!user) return null;
    return {
        ...user,
        profile_photo_url: user.profile_photo_path
            ? `/storage/${user.profile_photo_path}`
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}`,
    };
});

// Form
const form = useForm({
    caption: '',
    location: '',
    images: [],
});

// Image previews
const previews = ref([]);
const fileInput = ref(null);
const currentPreviewIndex = ref(0);

const captionLength = computed(() => form.caption.length);

const openFilePicker = () => {
    fileInput.value?.click();
};

const handleFileChange = (event) => {
    const files = Array.from(event.target.files);
    if (!files.length) return;

    // Limit to 10
    const limited = files.slice(0, 10);
    form.images = limited;

    // Generate previews
    previews.value = [];
    currentPreviewIndex.value = 0;
    limited.forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            previews.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const prevPreview = () => {
    if (currentPreviewIndex.value > 0) currentPreviewIndex.value--;
};

const nextPreview = () => {
    if (currentPreviewIndex.value < previews.value.length - 1) currentPreviewIndex.value++;
};

const submit = () => {
    form.post('/posts', {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            previews.value = [];
        },
    });
};
</script>

<template>

    <Head title="New Post" />

    <div
        class="relative flex h-auto min-h-screen w-full flex-col bg-white dark:bg-background-dark overflow-x-hidden max-w-lg mx-auto shadow-xl font-display">

        <!-- Top Navigation Bar -->
        <header
            class="flex items-center bg-white dark:bg-background-dark p-4 border-b border-slate-100 dark:border-slate-800 sticky top-0 z-10">
            <Link href="/"
                class="text-slate-900 dark:text-slate-100 flex size-10 shrink-0 items-center justify-center rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </Link>
            <h1
                class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] flex-1 text-center">
                New Post
            </h1>
            <div class="flex w-12 items-center justify-end">
                <button @click="submit" :disabled="form.processing || form.images.length === 0"
                    class="text-primary text-base font-bold leading-normal tracking-[0.015em] shrink-0 hover:opacity-80 disabled:opacity-40 disabled:cursor-not-allowed transition-opacity">
                    <span v-if="form.processing" class="flex items-center gap-1">
                        <svg class="animate-spin h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                        </svg>
                    </span>
                    <span v-else>Share</span>
                </button>
            </div>
        </header>

        <!-- Error Messages -->
        <div v-if="Object.keys(form.errors).length" class="px-4 pt-3">
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg p-3">
                <p v-for="(error, field) in form.errors" :key="field" class="text-red-600 dark:text-red-400 text-sm">
                    {{ error }}
                </p>
            </div>
        </div>

        <!-- Image Preview / Picker Area -->
        <div class="flex w-full bg-white dark:bg-background-dark py-2">
            <div class="w-full overflow-hidden bg-slate-100 dark:bg-slate-800 aspect-square flex relative cursor-pointer"
                @click="!previews.length && openFilePicker()">
                <!-- No image selected: placeholder -->
                <template v-if="previews.length === 0">
                    <div
                        class="flex flex-col items-center justify-center w-full h-full gap-3 text-slate-400 select-none">
                        <span class="material-symbols-outlined text-6xl">add_photo_alternate</span>
                        <p class="text-sm font-medium">Tap to select photo</p>
                    </div>
                </template>

                <!-- Image preview -->
                <template v-else>
                    <div class="w-full h-full bg-center bg-no-repeat bg-cover transition-all duration-300"
                        :style="`background-image: url('${previews[currentPreviewIndex]}')`" />

                    <!-- Navigation arrows if multiple images -->
                    <template v-if="previews.length > 1">
                        <button v-if="currentPreviewIndex > 0" @click.stop="prevPreview"
                            class="absolute left-3 top-1/2 -translate-y-1/2 bg-black/40 backdrop-blur-md text-white p-2 rounded-full hover:bg-black/60 transition-colors">
                            <span class="material-symbols-outlined text-sm">chevron_left</span>
                        </button>
                        <button v-if="currentPreviewIndex < previews.length - 1" @click.stop="nextPreview"
                            class="absolute right-3 top-1/2 -translate-y-1/2 bg-black/40 backdrop-blur-md text-white p-2 rounded-full hover:bg-black/60 transition-colors">
                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                        </button>

                        <!-- Dots indicator -->
                        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5">
                            <div v-for="(_, i) in previews" :key="i" class="w-1.5 h-1.5 rounded-full transition-colors"
                                :class="i === currentPreviewIndex ? 'bg-white' : 'bg-white/40'" />
                        </div>
                    </template>

                    <!-- Action buttons -->
                    <div class="absolute bottom-4 right-4 flex gap-2">
                        <button @click.stop="openFilePicker"
                            class="bg-black/40 backdrop-blur-md text-white p-2 rounded-full hover:bg-black/60 transition-colors"
                            title="Change photo">
                            <span class="material-symbols-outlined text-sm">photo_library</span>
                        </button>
                    </div>

                    <!-- Image count badge -->
                    <div v-if="previews.length > 1"
                        class="absolute top-3 right-3 bg-black/50 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ currentPreviewIndex + 1 }}/{{ previews.length }}
                    </div>
                </template>
            </div>
        </div>

        <!-- Caption Section -->
        <div class="flex items-start px-4 py-4 gap-3 border-b border-slate-100 dark:border-slate-800">
            <!-- User avatar -->
            <div v-if="currentUser"
                class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10 shrink-0 border border-slate-200 dark:border-slate-700"
                :style="`background-image: url('${currentUser.profile_photo_url}')`" />

            <div class="flex flex-1 flex-col">
                <textarea v-model="form.caption" maxlength="2200"
                    class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-slate-900 dark:text-slate-100 focus:outline-0 focus:ring-0 border-none bg-transparent placeholder:text-slate-400 dark:placeholder:text-slate-500 text-base font-normal leading-normal pt-2 min-h-[80px]"
                    placeholder="Write a caption..." />
            </div>
        </div>


        <!-- Hidden file input -->
        <input ref="fileInput" type="file" accept="image/*" multiple class="hidden" @change="handleFileChange" />
    </div>
</template>
