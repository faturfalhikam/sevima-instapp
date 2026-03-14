<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button
        :type="type"
        class="w-full h-14 bg-gradient-to-r from-primary to-[#ff4d4d] text-white font-bold rounded-lg shadow-md shadow-primary/30 hover:opacity-90 transition-opacity active:scale-[0.98] disabled:opacity-50"
    >
        <slot />
    </button>
</template>
