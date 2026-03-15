<script setup>
import { usePage, Link } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <!-- Bottom Navigation Bar -->
    <div
        class="fixed bottom-0 left-0 right-0 z-50 bg-white dark:bg-background-dark border-t border-primary/10 px-6 py-2 pb-6">
        <div class="max-w-md mx-auto flex items-center justify-around">
            <Link href="/" class="flex flex-col items-center justify-center text-primary">
                <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1">
                    home
                </span>
            </Link>
            <Link href="/posts/create"
                class="flex flex-col items-center justify-center text-slate-400 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-3xl">add_box</span>
            </Link>
            <Link href="/profile" class="flex flex-col items-center justify-center">
                <div
                    class="w-7 h-7 rounded-full border-2 border-slate-300 dark:border-slate-700 p-[1px] overflow-hidden">
                    <div class="w-full h-full rounded-full bg-center bg-cover"
                        :style="`background-image: url('${page.props.auth.user.profile_photo_url}')`"></div>
                </div>
            </Link>
        </div>
    </div>
</template>
