<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        class?: HTMLAttributes['class'];
        contentClass?: HTMLAttributes['class'];
    }>(),
    {
        title: '',
        description: '',
        class: '',
        contentClass: '',
    },
);

const emit = defineEmits<{
    (e: 'submit', event: SubmitEvent): void;
}>();

const onSubmit = (event: SubmitEvent): void => {
    emit('submit', event);
};
</script>

<template>
    <form :class="cn('section-card space-y-6', props.class)" @submit="onSubmit">
        <header v-if="title || description" class="space-y-1">
            <h2 v-if="title" class="text-lg font-semibold">{{ title }}</h2>
            <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
        </header>

        <div :class="cn('space-y-4', props.contentClass)">
            <slot />
        </div>
    </form>
</template>
