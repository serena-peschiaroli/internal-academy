<script setup lang="ts">
import { ChevronDown, Plus, SlidersHorizontal, Upload } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button as UIButton } from '@/components/ui/button';
import type { ButtonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type IconName = 'adjustments' | 'plus' | 'arrowDown' | 'upload';

const props = withDefaults(
    defineProps<{
        title?: string;
        icon?: IconName;
        size?: 'small' | 'big' | 'default' | 'sm' | 'lg' | 'icon' | 'icon-sm' | 'icon-lg';
        disabled?: boolean;
        loading?: boolean;
        variant?:
            | 'primary'
            | 'outlined'
            | 'outline'
            | 'text'
            | 'delete'
            | 'danger'
            | 'destructive'
            | 'secondary'
            | 'ghost'
            | 'light'
            | 'warning'
            | 'transparent'
            | 'link'
            | 'default';
        color?: 'red';
        type?: 'button' | 'submit' | 'reset';
        class?: string;
    }>(),
    {
        title: '',
        icon: undefined,
        size: 'small',
        disabled: false,
        loading: false,
        variant: 'default',
        color: undefined,
        type: 'button',
        class: '',
    },
);

const emit = defineEmits<{
    (e: 'onClick', event: MouseEvent): void;
}>();

const mappedVariant = computed<ButtonVariants['variant']>(() => {
    if (props.variant === 'delete' || props.variant === 'danger' || props.variant === 'destructive') {
        return 'danger';
    }

    if (props.variant === 'outlined' || props.variant === 'outline') {
        return 'outline';
    }

    if (props.variant === 'text' || props.variant === 'transparent') {
        return 'transparent';
    }

    if (props.variant === 'secondary') {
        return 'secondary';
    }

    if (props.variant === 'ghost') {
        return 'ghost';
    }

    if (props.variant === 'light') {
        return 'light';
    }

    if (props.variant === 'warning') {
        return 'warning';
    }

    if (props.variant === 'link') {
        return 'link';
    }

    return props.color === 'red' ? 'danger' : 'default';
});

const mappedSize = computed<ButtonVariants['size']>(() => {
    if (props.size === 'big') {
        return 'default';
    }

    if (props.size === 'small') {
        return 'sm';
    }

    if (props.size === 'sm' || props.size === 'default' || props.size === 'lg' || props.size === 'icon' || props.size === 'icon-sm' || props.size === 'icon-lg') {
        return props.size;
    }

    return 'sm';
});

const iconComponent = computed(() => {
    switch (props.icon) {
        case 'adjustments':
            return SlidersHorizontal;
        case 'plus':
            return Plus;
        case 'arrowDown':
            return ChevronDown;
        case 'upload':
            return Upload;
        default:
            return null;
    }
});

const handleClick = (event: MouseEvent): void => {
    emit('onClick', event);
};
</script>

<template>
    <UIButton
        :type="type"
        :variant="mappedVariant"
        :size="mappedSize"
        :disabled="disabled || loading"
        :class="cn('min-w-0', props.class)"
        @click="handleClick"
    >
        <span
            v-if="loading"
            class="size-4 animate-spin rounded-full border-2 border-current border-r-transparent"
            aria-hidden="true"
        />
        <template v-else>
            <component :is="iconComponent" v-if="iconComponent" class="size-4" />
            <slot>{{ title }}</slot>
        </template>
    </UIButton>
</template>
