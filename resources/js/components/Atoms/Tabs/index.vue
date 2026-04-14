<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

type TabItem = {
    key: string;
    label: string;
    count?: number;
};

const props = withDefaults(
    defineProps<{
        tabs: TabItem[];
        modelValue: string;
        class?: string;
    }>(),
    {
        class: '',
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const selected = computed({
    get: () => props.modelValue,
    set: (value: string) => emit('update:modelValue', value),
});
</script>

<template>
    <div :class="cn('inline-flex gap-1 rounded-lg border border-gray-200 bg-white p-1 shadow-sm', props.class)">
        <button
            v-for="tab in tabs"
            :key="tab.key"
            type="button"
            class="inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-colors duration-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
            :class="
                selected === tab.key
                    ? 'bg-[color:var(--color-main-darker)] text-white'
                    : 'text-foreground hover:bg-[color:var(--color-light-gray-bg)]'
            "
            @click="selected = tab.key"
        >
            <span>{{ tab.label }}</span>
            <span
                v-if="typeof tab.count === 'number'"
                class="rounded-full px-2 py-0.5 text-xs"
                :class="
                    selected === tab.key
                        ? 'bg-white/20 text-white'
                        : 'bg-[color:var(--color-light-gray-bg)] text-muted-foreground'
                "
            >
                {{ tab.count }}
            </span>
        </button>
    </div>
</template>
