<script setup>
import { ref } from 'vue';

const notificationCount = ref(1);
</script>

<template>
    <!-- Top Navigation Bar -->
    <nav class="sticky top-0 z-50 flex items-center justify-between bg-white/80 dark:bg-background-dark/80 backdrop-blur-md px-4 py-3 border-b border-primary/10">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-3xl">photo_camera</span>
            <h1 class="text-xl font-bold tracking-tight bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">
                InstaApp
            </h1>
        </div>
        <div class="flex items-center gap-4">
            <button class="relative p-1 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-slate-700 dark:text-slate-200">favorite</span>
                <span v-if="notificationCount > 0" class="absolute top-1 right-1 flex h-2 w-2 rounded-full bg-primary"></span>
            </button>
        </div>
    </nav>
</template>
