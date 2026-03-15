<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    profile: Object,
    posts: Array,
});

const viewMode = ref('grid');

// Lightbox
const lightboxPost = ref(null);
const lightboxImageIndex = ref(0);

const openLightbox = (post, imgIndex = 0) => {
    lightboxPost.value = post;
    lightboxImageIndex.value = imgIndex;
};
const closeLightbox = () => { lightboxPost.value = null; };
const lightboxImage = computed(() =>
    lightboxPost.value?.images[lightboxImageIndex.value]?.url ?? null
);
const prevLightboxImage = () => {
    if (lightboxImageIndex.value > 0) lightboxImageIndex.value--;
};
const nextLightboxImage = () => {
    if (lightboxImageIndex.value < lightboxPost.value.images.length - 1)
        lightboxImageIndex.value++;
};

// Follow / Unfollow
const isFollowing = ref(props.profile.is_following);
const followForm = useForm({});

const toggleFollow = () => {
    followForm.post(`/users/${props.profile.id}/follow`, {
        preserveScroll: true,
        onSuccess: () => {
            isFollowing.value = !isFollowing.value;
        },
    });
};
</script>

<template>

    <Head :title="`${profile.name} – Profile`" />

    <div
        class="relative flex h-auto min-h-screen w-full flex-col bg-white dark:bg-background-dark font-display max-w-lg mx-auto shadow-xl overflow-x-hidden">

        <!-- Header -->
        <header
            class="flex items-center gap-3 bg-white dark:bg-background-dark px-4 py-3 border-b border-slate-100 dark:border-slate-800 sticky top-0 z-10">
            <Link href="/"
                class="flex size-9 shrink-0 items-center justify-center rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-slate-700 dark:text-slate-300">
                <span class="material-symbols-outlined">arrow_back</span>
            </Link>
            <h1 class="flex-1 text-base font-bold text-slate-900 dark:text-slate-100 truncate">
                {{ profile.name }}
            </h1>
        </header>

        <!-- Profile Info -->
        <div class="px-5 pt-5 pb-4">
            <!-- Avatar + Stats -->
            <div class="flex items-center gap-6">
                <div class="shrink-0 w-20 h-20 rounded-full bg-center bg-cover border-2 border-primary/30 ring-2 ring-primary/10"
                    :style="`background-image: url('${profile.profile_photo_url}')`" />

                <div class="flex flex-1 justify-around text-center">
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100 leading-tight">{{
                            profile.posts_count }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Posts</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100 leading-tight">{{
                            profile.followers_count }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Followers</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100 leading-tight">{{
                            profile.following_count }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Following</p>
                    </div>
                </div>
            </div>

            <!-- Name & Bio -->
            <div class="mt-3">
                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ profile.name }}</p>
                <p v-if="profile.bio" class="text-sm text-slate-600 dark:text-slate-400 mt-0.5 whitespace-pre-line">{{
                    profile.bio }}</p>
            </div>

            <!-- Follow / Unfollow button -->
            <div class="mt-3">
                <button @click="toggleFollow" :disabled="followForm.processing"
                    class="block w-full text-center text-sm font-semibold py-1.5 rounded-lg transition-colors disabled:opacity-50"
                    :class="isFollowing
                        ? 'border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800'
                        : 'bg-primary text-white hover:opacity-90'">
                    <span v-if="followForm.processing" class="flex items-center justify-center gap-1">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                        </svg>
                    </span>
                    <span v-else>{{ isFollowing ? 'Unfollow' : 'Follow' }}</span>
                </button>
            </div>
        </div>

        <!-- View Mode Tabs -->
        <div class="flex border-t border-slate-100 dark:border-slate-800">
            <button @click="viewMode = 'grid'"
                class="flex-1 flex justify-center items-center py-2.5 transition-colors border-b-2" :class="viewMode === 'grid'
                    ? 'border-slate-900 dark:border-slate-100 text-slate-900 dark:text-slate-100'
                    : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
                <span class="material-symbols-outlined text-xl">grid_on</span>
            </button>
            <button @click="viewMode = 'list'"
                class="flex-1 flex justify-center items-center py-2.5 transition-colors border-b-2" :class="viewMode === 'list'
                    ? 'border-slate-900 dark:border-slate-100 text-slate-900 dark:text-slate-100'
                    : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
                <span class="material-symbols-outlined text-xl">view_agenda</span>
            </button>
        </div>

        <!-- Posts Grid -->
        <div v-if="viewMode === 'grid'">
            <template v-if="posts.length === 0">
                <div class="flex flex-col items-center justify-center py-20 gap-3 text-slate-400">
                    <span class="material-symbols-outlined text-6xl">photo_camera</span>
                    <p class="text-sm font-medium">No posts yet</p>
                </div>
            </template>
            <div v-else class="grid grid-cols-3 gap-px bg-slate-100 dark:bg-slate-800">
                <template v-for="post in posts" :key="post.id">
                    <div v-if="post.images.length > 0"
                        class="relative aspect-square bg-slate-200 dark:bg-slate-700 cursor-pointer overflow-hidden group"
                        @click="openLightbox(post, 0)">
                        <img :src="post.images[0].url" :alt="post.caption || 'Post'"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                        <div v-if="post.images.length > 1" class="absolute top-1.5 right-1.5 text-white">
                            <span class="material-symbols-outlined text-base drop-shadow">filter_none</span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Posts List -->
        <div v-else class="divide-y divide-slate-100 dark:divide-slate-800 pb-24">
            <template v-if="posts.length === 0">
                <div class="flex flex-col items-center justify-center py-20 gap-3 text-slate-400">
                    <span class="material-symbols-outlined text-6xl">photo_camera</span>
                    <p class="text-sm font-medium">No posts yet</p>
                </div>
            </template>
            <div v-for="post in posts" :key="post.id" class="py-3 px-4">
                <div v-if="post.images.length > 0" class="flex gap-2 overflow-x-auto no-scrollbar -mx-4 px-4 mb-2">
                    <img v-for="(img, i) in post.images" :key="i" :src="img.url" :alt="post.caption || 'Post'"
                        @click="openLightbox(post, i)"
                        class="h-48 w-48 object-cover rounded-xl shrink-0 cursor-pointer hover:brightness-95 transition" />
                </div>
                <p v-if="post.caption" class="text-sm text-slate-700 dark:text-slate-300 line-clamp-2">{{ post.caption
                    }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ post.created_at }}</p>
            </div>
        </div>

        <div class="h-20" />

        <!-- Bottom Navigation -->
        <nav
            class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-lg z-50 bg-white dark:bg-background-dark border-t border-primary/10 px-6 py-2 pb-6">
            <div class="flex items-center justify-around">
                <Link href="/"
                    class="flex flex-col items-center justify-center text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-3xl">home</span>
                </Link>
                <Link href="/posts/create"
                    class="flex flex-col items-center justify-center text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-3xl">add_box</span>
                </Link>
                <Link href="/profile"
                    class="flex flex-col items-center justify-center text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-3xl">person</span>
                </Link>
            </div>
        </nav>
    </div>

    <!-- Lightbox -->
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="lightboxPost" class="fixed inset-0 z-[100] bg-black/90 flex items-center justify-center"
                @click.self="closeLightbox">
                <button @click="closeLightbox" class="absolute top-4 right-4 text-white/80 hover:text-white">
                    <span class="material-symbols-outlined text-3xl">close</span>
                </button>
                <img :src="lightboxImage" :alt="lightboxPost.caption || 'Post'"
                    class="max-h-[85vh] max-w-[90vw] object-contain rounded-lg" />
                <button v-if="lightboxImageIndex > 0" @click="prevLightboxImage"
                    class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/40 text-white p-2 rounded-full">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <button v-if="lightboxPost.images.length > 1 && lightboxImageIndex < lightboxPost.images.length - 1"
                    @click="nextLightboxImage"
                    class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/40 text-white p-2 rounded-full">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
                <div v-if="lightboxPost.caption"
                    class="absolute bottom-6 left-1/2 -translate-x-1/2 max-w-xs text-center text-white/80 text-sm px-4">
                    {{ lightboxPost.caption }}
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
