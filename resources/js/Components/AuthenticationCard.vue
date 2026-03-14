<template>
    <div class="relative flex min-h-screen flex-col items-center justify-center p-4 bg-background-light dark:bg-background-dark">
        <div class="w-full max-w-[400px] space-y-8">
            <!-- Logo Section -->
            <div class="flex flex-col items-center gap-6">
                <slot name="logo" />
                <h1 class="text-slate-900 dark:text-slate-100 text-3xl font-bold tracking-tight">InstaApp</h1>
            </div>

            <!-- Card Section -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-xl shadow-sm border border-primary/10">
                <slot />
            </div>

            <!-- Footer Links -->
            <div class="flex justify-center gap-6 opacity-50">
                <a href="#" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest hover:opacity-70">About</a>
                <a href="#" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest hover:opacity-70">Privacy</a>
                <a href="#" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest hover:opacity-70">Terms</a>
            </div>
        </div>

        <!-- Bottom Gradient Bar -->
        <div class="fixed bottom-0 left-0 w-full h-1 bg-gradient-to-r from-[#ffcc00] via-primary to-[#8a3ab9]"></div>
    </div>
</template>

<style scoped>
:global(html) {
    --color-primary: #f4258c;
    --color-background-light: #f8f5f7;
    --color-background-dark: #221019;
}
</style>
