<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
