<script setup lang="ts">
import { computed } from 'vue';
import { Spinner as UISpinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        white?: boolean;
        small?: boolean;
        class?: string;
    }>(),
    {
        white: false,
        small: false,
        class: '',
    },
);

const spinnerClass = computed(() =>
    cn(props.small ? 'size-4' : 'size-6', props.white ? 'text-white' : 'text-[color:var(--color-main-darker)]', props.class),
);
</script>

<template>
    <div class="text-center">
        <UISpinner :class="spinnerClass" />
    </div>
</template>
