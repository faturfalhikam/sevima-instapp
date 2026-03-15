<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['like', 'open-comments']);

const currentImageIndex = ref(0);

const currentImage = computed(() => {
    return props.post.images[currentImageIndex.value];
});

const handleLike = () => {
    emit('like', props.post.id, props.post.is_liked);
};

const openComments = () => {
    emit('open-comments', props.post);
};

const nextImage = () => {
    if (currentImageIndex.value < props.post.images.length - 1) {
        currentImageIndex.value++;
    }
};

const prevImage = () => {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
    }
};
</script>

<template>
    <!-- Post Card -->
    <article class="bg-white dark:bg-slate-900 mb-6 border-b border-primary/5">
        <!-- Post Header -->
        <div class="flex items-center justify-between p-4">
            <div class="flex items-center gap-3">
                <!-- Avatar + Name → public profile -->
                <Link :href="`/users/${post.user.id}`" class="flex items-center gap-3 group">
                    <div
                        class="w-9 h-9 rounded-full p-[1.5px] bg-gradient-to-tr from-yellow-400 via-primary to-purple-600">
                        <div class="w-full h-full rounded-full border-2 border-white dark:border-slate-900 bg-center bg-cover"
                            :style="`background-image: url('${post.user.profile_photo_url}')`"></div>
                    </div>
                    <div>
                        <p class="text-sm font-bold group-hover:text-primary transition-colors">{{ post.user.name }}</p>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Post Image(s) with Navigation -->
        <div v-if="post.images.length > 0" class="relative">
            <div class="aspect-square bg-slate-100 dark:bg-slate-800 bg-center bg-cover"
                :style="`background-image: url('${currentImage.url}')`"></div>

            <!-- Image Navigation -->
            <div v-if="post.images.length > 1" class="absolute inset-0 flex items-center justify-between px-4">
                <button v-if="currentImageIndex > 0" @click="prevImage"
                    class="bg-white/50 dark:bg-slate-900/50 hover:bg-white/70 dark:hover:bg-slate-900/70 rounded-full p-2 transition-colors">
                    <span class="material-symbols-outlined text-slate-900 dark:text-white">navigate_before</span>
                </button>
                <div class="ml-auto"></div>
                <button v-if="currentImageIndex < post.images.length - 1" @click="nextImage"
                    class="bg-white/50 dark:bg-slate-900/50 hover:bg-white/70 dark:hover:bg-slate-900/70 rounded-full p-2 transition-colors">
                    <span class="material-symbols-outlined text-slate-900 dark:text-white">navigate_next</span>
                </button>
            </div>

            <!-- Image Indicators -->
            <div v-if="post.images.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5">
                <button v-for="(image, index) in post.images" :key="index" @click="currentImageIndex = index" :class="[
                    'w-2 h-2 rounded-full transition-all',
                    currentImageIndex === index ? 'bg-white w-4' : 'bg-white/50'
                ]"></button>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button @click="handleLike" class="hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-2xl text-primary"
                        style="font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24" v-if="post.is_liked">
                        favorite
                    </span>
                    <span class="material-symbols-outlined text-2xl" v-else>
                        favorite
                    </span>
                </button>
                <!-- Comment button → opens CommentModal -->
                <button @click="openComments" class="hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-2xl">mode_comment</span>
                </button>
            </div>
        </div>

        <!-- Post Details -->
        <div class="px-4 pb-4">
            <p class="text-sm font-bold mb-1">{{ post.likes_count }} likes</p>
            <div class="text-sm mb-2">
                <span class="font-bold mr-2">{{ post.user.name }}</span>
                <span class="text-slate-700 dark:text-slate-300">{{ post.caption }}</span>
            </div>

            <!-- View Comments Button -->
            <button v-if="post.comments_count > 0" @click="openComments"
                class="text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors mb-2 block">
                View all {{ post.comments_count }} comments
            </button>

            <!-- Recent Comments Preview (2 max) -->
            <div v-if="post.comments.length > 0" class="space-y-1 mb-2">
                <div v-for="comment in post.comments.slice(0, 2)" :key="comment.id" class="text-sm">
                    <span class="font-bold mr-2">{{ comment.user.name }}</span>
                    <span class="text-slate-700 dark:text-slate-300">{{ comment.content }}</span>
                </div>
            </div>

            <!-- Timestamp -->
            <p class="text-[10px] text-slate-400 uppercase">{{ post.created_at }}</p>
        </div>
    </article>
</template>
