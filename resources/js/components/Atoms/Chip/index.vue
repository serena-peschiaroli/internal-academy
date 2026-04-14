<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        text: string;
        subtext?: string;
        severity?: 'danger' | 'neutral' | 'success' | 'info';
        size?: 'sm' | 'md' | 'xl';
        class?: string;
    }>(),
    {
        subtext: '',
        severity: 'neutral',
        size: 'sm',
        class: '',
    },
);

const badgeVariant = computed(() => {
    switch (props.severity) {
        case 'danger':
            return 'danger';
        case 'success':
            return 'success';
        case 'info':
            return 'outline';
        default:
            return 'secondary';
    }
});

const sizeClass = computed(() => {
    if (props.size === 'xl') {
        return 'px-4 py-2 text-sm';
    }
    if (props.size === 'md') {
        return 'px-3 py-1.5 text-xs';
    }

    return 'px-2.5 py-1 text-xs';
});
</script>

<template>
    <Badge :variant="badgeVariant" :class="cn('inline-flex flex-col gap-0.5', sizeClass, props.class)">
        <span v-if="subtext" class="text-[10px] uppercase tracking-wide opacity-80">{{ subtext }}</span>
        <span class="leading-none">{{ text }}</span>
    </Badge>
</template>
