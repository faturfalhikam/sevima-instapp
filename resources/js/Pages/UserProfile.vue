<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    profile: Object,
    posts: Array,
});

// Grid / List view toggle (starts with grid like Instagram)
const viewMode = ref('grid'); // 'grid' | 'list'

// Lightbox
const lightboxPost = ref(null);
const lightboxImageIndex = ref(0);

const openLightbox = (post, imgIndex = 0) => {
    lightboxPost.value = post;
    lightboxImageIndex.value = imgIndex;
};

const closeLightbox = () => {
    lightboxPost.value = null;
};

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
// Sidebar
const showSidebar = ref(false);

// Logout
const logoutForm = useForm({});
const logout = () => logoutForm.post('/logout');
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
            <!-- Settings sidebar trigger -->
            <button @click="showSidebar = true"
                class="flex size-9 shrink-0 items-center justify-center rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors text-slate-700 dark:text-slate-300">
                <span class="material-symbols-outlined">settings</span>
            </button>
        </header>

        <!-- Profile Info Section -->
        <div class="px-5 pt-5 pb-4">
            <!-- Avatar + Stats row -->
            <div class="flex items-center gap-6">
                <!-- Avatar -->
                <div class="shrink-0 w-20 h-20 rounded-full bg-center bg-cover border-2 border-primary/30 ring-2 ring-primary/10"
                    :style="`background-image: url('${profile.profile_photo_url}')`" />

                <!-- Stats -->
                <div class="flex flex-1 justify-around text-center">
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100 leading-tight">
                            {{ profile.posts_count }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Posts</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100 leading-tight">
                            {{ profile.followers_count }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Followers</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100 leading-tight">
                            {{ profile.following_count }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Following</p>
                    </div>
                </div>
            </div>

            <!-- Name & Bio -->
            <div class="mt-3">
                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ profile.name }}</p>
                <p v-if="profile.bio" class="text-sm text-slate-600 dark:text-slate-400 mt-0.5 whitespace-pre-line">
                    {{ profile.bio }}
                </p>
            </div>

            <!-- Edit Profile button -->
            <div class="mt-3">
                <Link href="/profile/edit"
                    class="block w-full text-center text-sm font-semibold py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Edit profile
                </Link>
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
            <!-- No posts -->
            <template v-if="posts.length === 0">
                <div class="flex flex-col items-center justify-center py-20 gap-3 text-slate-400">
                    <span class="material-symbols-outlined text-6xl">photo_camera</span>
                    <p class="text-sm font-medium">No posts yet</p>
                </div>
            </template>

            <!-- Image grid (3 columns) -->
            <div v-else class="grid grid-cols-3 gap-px bg-slate-100 dark:bg-slate-800">
                <template v-for="post in posts" :key="post.id">
                    <!-- Show first image of each post -->
                    <div v-if="post.images.length > 0"
                        class="relative aspect-square bg-slate-200 dark:bg-slate-700 cursor-pointer overflow-hidden group"
                        @click="openLightbox(post, 0)">
                        <img :src="post.images[0].url" :alt="post.caption || 'Post'"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                        <!-- Multi-image badge -->
                        <div v-if="post.images.length > 1" class="absolute top-1.5 right-1.5 text-white">
                            <span class="material-symbols-outlined text-base drop-shadow">filter_none</span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Posts List view -->
        <div v-else class="divide-y divide-slate-100 dark:divide-slate-800 pb-24">
            <template v-if="posts.length === 0">
                <div class="flex flex-col items-center justify-center py-20 gap-3 text-slate-400">
                    <span class="material-symbols-outlined text-6xl">photo_camera</span>
                    <p class="text-sm font-medium">No posts yet</p>
                </div>
            </template>

            <div v-for="post in posts" :key="post.id" class="py-3 px-4">
                <!-- Images horizontal scroll -->
                <div v-if="post.images.length > 0" class="flex gap-2 overflow-x-auto no-scrollbar -mx-4 px-4 mb-2">
                    <img v-for="(img, i) in post.images" :key="i" :src="img.url" :alt="post.caption || 'Post'"
                        @click="openLightbox(post, i)"
                        class="h-48 w-48 object-cover rounded-xl shrink-0 cursor-pointer hover:brightness-95 transition" />
                </div>
                <p v-if="post.caption" class="text-sm text-slate-700 dark:text-slate-300 line-clamp-2">
                    {{ post.caption }}
                </p>
                <p class="text-xs text-slate-400 mt-1">{{ post.created_at }}</p>
            </div>
        </div>

        <!-- Bottom Navigation spacing -->
        <div class="h-20" />

        <!-- Bottom Navigation (inline anchors) -->
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
                <Link href="/profile" class="flex flex-col items-center justify-center">
                    <div class="w-7 h-7 rounded-full border-2 border-primary p-[1px] overflow-hidden">
                        <div class="w-full h-full rounded-full bg-center bg-cover"
                            :style="`background-image: url('${profile.profile_photo_url}')`" />
                    </div>
                </Link>
            </div>
        </nav>
    </div>

    <!-- Settings Sidebar -->
    <Teleport to="body">
        <Transition name="sidebar-fade">
            <div v-if="showSidebar" class="fixed inset-0 z-[200] flex justify-end">
                <!-- Overlay -->
                <div class="absolute inset-0 bg-black/40" @click="showSidebar = false" />

                <!-- Panel -->
                <Transition name="sidebar-slide">
                    <div v-if="showSidebar"
                        class="relative z-10 flex flex-col w-64 h-full bg-white dark:bg-background-dark shadow-2xl">

                        <!-- Panel header -->
                        <div
                            class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-sm font-bold text-slate-900 dark:text-slate-100">Menu</span>
                            <button @click="showSidebar = false"
                                class="flex size-8 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>
                        </div>

                        <!-- Menu items -->
                        <div class="flex flex-col flex-1 py-3">
                            <Link href="/profile/edit"
                                class="flex items-center gap-3 px-5 py-3.5 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                <span class="material-symbols-outlined text-xl">edit</span>
                                <span class="text-sm font-medium">Edit profile</span>
                            </Link>
                        </div>

                        <!-- Logout at bottom -->
                        <div class="border-t border-slate-100 dark:border-slate-800 p-4">
                            <button @click="logout"
                                class="flex w-full items-center gap-3 px-1 py-3 text-red-500 hover:text-red-600 transition-colors">
                                <span class="material-symbols-outlined text-xl">logout</span>
                                <span class="text-sm font-semibold">Log out</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>

    <Teleport to="body">
        <Transition name="fade">
            <div v-if="lightboxPost" class="fixed inset-0 z-[100] bg-black/90 flex items-center justify-center"
                @click.self="closeLightbox">

                <!-- Close -->
                <button @click="closeLightbox"
                    class="absolute top-4 right-4 text-white/80 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-3xl">close</span>
                </button>

                <!-- Image -->
                <img :src="lightboxImage" :alt="lightboxPost.caption || 'Post'"
                    class="max-h-[85vh] max-w-[90vw] object-contain rounded-lg" />

                <!-- Prev / Next -->
                <button v-if="lightboxImageIndex > 0" @click="prevLightboxImage"
                    class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/40 text-white p-2 rounded-full transition-colors">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <button v-if="lightboxPost.images.length > 1 && lightboxImageIndex < lightboxPost.images.length - 1"
                    @click="nextLightboxImage"
                    class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/40 text-white p-2 rounded-full transition-colors">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>

                <!-- Caption -->
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

/* Sidebar overlay */
.sidebar-fade-enter-active,
.sidebar-fade-leave-active {
    transition: opacity 0.25s ease;
}

.sidebar-fade-enter-from,
.sidebar-fade-leave-to {
    opacity: 0;
}

/* Sidebar panel slide */
.sidebar-slide-enter-active,
.sidebar-slide-leave-active {
    transition: transform 0.25s ease;
}

.sidebar-slide-enter-from,
.sidebar-slide-leave-to {
    transform: translateX(100%);
}
</style>
