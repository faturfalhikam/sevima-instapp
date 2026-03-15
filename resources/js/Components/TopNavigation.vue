<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

// ── Search modal state ──────────────────────────────────────────────
const showSearch = ref(false);
const query = ref('');
const results = ref([]);
const isLoading = ref(false);
let debounceTimer = null;

const openSearch = () => {
    showSearch.value = true;
    // Wait for the input to mount, then focus it
    setTimeout(() => {
        document.getElementById('search-input')?.focus();
    }, 80);
};

const closeSearch = () => {
    showSearch.value = false;
    query.value = '';
    results.value = [];
};

// Debounced live search
watch(query, (val) => {
    clearTimeout(debounceTimer);
    if (!val.trim()) {
        results.value = [];
        isLoading.value = false;
        return;
    }
    isLoading.value = true;
    debounceTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/users/search?q=${encodeURIComponent(val.trim())}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            results.value = await res.json();
        } catch {
            results.value = [];
        } finally {
            isLoading.value = false;
        }
    }, 300);
});

const goToProfile = (userId) => {
    closeSearch();
    router.visit(`/users/${userId}`);
};
</script>

<template>
    <!-- Top Navigation Bar -->
    <nav
        class="sticky top-0 z-50 flex items-center justify-between bg-white/80 dark:bg-background-dark/80 backdrop-blur-md px-4 py-3 border-b border-primary/10">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-3xl">photo_camera</span>
            <h1
                class="text-xl font-bold tracking-tight bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">
                InstaApp
            </h1>
        </div>
        <div class="flex items-center gap-4">
            <button @click="openSearch" class="relative p-1 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-slate-700 dark:text-slate-200">search</span>
            </button>
        </div>
    </nav>

    <!-- Search Modal -->
    <Teleport to="body">
        <Transition name="search-fade">
            <div v-if="showSearch" class="fixed inset-0 z-[300] flex flex-col bg-white dark:bg-background-dark">

                <!-- Search Header -->
                <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex flex-1 items-center gap-2 bg-slate-100 dark:bg-slate-800 rounded-full px-4 py-2">
                        <span class="material-symbols-outlined text-slate-400 text-xl shrink-0">search</span>
                        <input id="search-input" v-model="query" type="text" placeholder="Search users..."
                            class="flex-1 border-none outline-none focus:outline-none focus:ring-0 bg-transparent text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none"
                            @keydown.escape="closeSearch" />
                        <button v-if="query" @click="query = ''"
                            class="text-slate-400 hover:text-slate-600 transition-colors">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>
                    <button @click="closeSearch"
                        class="shrink-0 text-sm font-semibold text-primary hover:opacity-75 transition-opacity px-1">
                        Cancel
                    </button>
                </div>

                <!-- Results area -->
                <div class="flex-1 overflow-y-auto">

                    <!-- Spinner -->
                    <div v-if="isLoading" class="flex items-center justify-center py-16">
                        <svg class="animate-spin h-7 w-7 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                        </svg>
                    </div>

                    <!-- Empty state (typed but no results) -->
                    <div v-else-if="query && results.length === 0"
                        class="flex flex-col items-center justify-center py-20 gap-2 text-slate-400">
                        <span class="material-symbols-outlined text-5xl">person_search</span>
                        <p class="text-sm">No users found for "<span class="font-semibold">{{ query }}</span>"</p>
                    </div>

                    <!-- Prompt before typing -->
                    <div v-else-if="!query"
                        class="flex flex-col items-center justify-center py-20 gap-2 text-slate-300 dark:text-slate-600">
                        <span class="material-symbols-outlined text-5xl">manage_search</span>
                        <p class="text-sm">Type a name to search</p>
                    </div>

                    <!-- User list -->
                    <ul v-else class="divide-y divide-slate-100 dark:divide-slate-800">
                        <li v-for="user in results" :key="user.id">
                            <button @click="goToProfile(user.id)"
                                class="w-full flex items-center gap-3 px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left">
                                <!-- Avatar -->
                                <div class="w-11 h-11 rounded-full shrink-0 bg-center bg-cover border border-slate-100 dark:border-slate-700"
                                    :style="`background-image: url('${user.profile_photo_url}')`" />
                                <!-- Name -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">{{
                                        user.name }}</p>
                                </div>
                                <span
                                    class="material-symbols-outlined text-slate-300 dark:text-slate-600 text-lg shrink-0">chevron_right</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.search-fade-enter-active,
.search-fade-leave-active {
    transition: opacity 0.18s ease, transform 0.18s ease;
}

.search-fade-enter-from,
.search-fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
