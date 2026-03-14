<script setup>
import { onMounted, ref, computed } from 'vue';

const props = defineProps({
    modelValue: String,
    icon: String,
    type: {
        type: String,
        default: 'text',
    },
});

const emit = defineEmits(['update:modelValue', 'togglePassword']);

const input = ref(null);
const showPassword = ref(false);
const inputType = computed(() => {
    if (props.type === 'password' && showPassword.value) {
        return 'text';
    }
    return props.type;
});

const togglePassword = () => {
    if (props.type === 'password') {
        showPassword.value = !showPassword.value;
        emit('togglePassword', showPassword.value);
    }
};

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div class="relative">
        <span v-if="icon" class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-primary/60">
            {{ icon }}
        </span>
        <input
            ref="input"
            :type="inputType"
            class="w-full bg-background-light dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary/50 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 h-14"
            :class="icon ? 'pl-12 pr-4' : 'px-4'"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <button
            v-if="type === 'password'"
            type="button"
            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition"
            @click="togglePassword"
        >
            <span class="material-symbols-outlined">
                {{ showPassword ? 'visibility' : 'visibility_off' }}
            </span>
        </button>
    </div>
</template>
