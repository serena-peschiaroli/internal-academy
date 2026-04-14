<script setup lang="ts">
import { computed } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = withDefaults(
    defineProps<{
        title?: string;
        subtitle?: string;
        canClose?: boolean;
        show?: boolean;
    }>(),
    {
        title: 'Modal',
        subtitle: 'Modal dialog',
        canClose: true,
        show: false,
    },
);

const emit = defineEmits<{
    (e: 'onClose'): void;
    (e: 'update:show', value: boolean): void;
}>();

const openModel = computed({
    get: () => props.show,
    set: (value: boolean) => {
        emit('update:show', value);

        if (!value) {
            emit('onClose');
        }
    },
});
</script>

<template>
    <Dialog :open="openModel" @update:open="openModel = $event">
        <DialogContent :show-close-button="canClose" class="max-h-[90vh] overflow-y-auto">
            <DialogHeader class="space-y-2">
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ subtitle }}</DialogDescription>
            </DialogHeader>

            <div class="pt-2">
                <slot />
            </div>
        </DialogContent>
    </Dialog>
</template>
