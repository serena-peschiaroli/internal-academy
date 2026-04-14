<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        id?: string;
        label?: string;
        hint?: string;
        error?: string;
        class?: HTMLAttributes['class'];
        labelClass?: HTMLAttributes['class'];
        hintClass?: HTMLAttributes['class'];
    }>(),
    {
        id: undefined,
        label: '',
        hint: '',
        error: '',
        class: '',
        labelClass: '',
        hintClass: '',
    },
);
</script>

<template>
    <div :class="cn('grid gap-2', props.class)">
        <Label v-if="label" :for="id" :class="labelClass">{{ label }}</Label>
        <slot />
        <p v-if="hint" :class="cn('text-xs text-muted-foreground', hintClass)">
            {{ hint }}
        </p>
        <InputError :message="error" />
    </div>
</template>
