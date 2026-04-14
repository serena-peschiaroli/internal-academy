<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { useVModel } from '@vueuse/core';
import AtomField from '@/components/Atoms/Field/index.vue';
import { cn } from '@/lib/utils';

defineOptions({
    inheritAttrs: false,
});

const props = withDefaults(
    defineProps<{
        id?: string;
        name?: string;
        label?: string;
        hint?: string;
        modelValue?: string;
        defaultValue?: string;
        placeholder?: string;
        rows?: number;
        required?: boolean;
        disabled?: boolean;
        readonly?: boolean;
        error?: string;
        class?: HTMLAttributes['class'];
        textareaClass?: HTMLAttributes['class'];
    }>(),
    {
        id: undefined,
        name: undefined,
        label: '',
        hint: '',
        modelValue: undefined,
        defaultValue: '',
        placeholder: '',
        rows: 4,
        required: false,
        disabled: false,
        readonly: false,
        error: '',
        class: '',
        textareaClass: '',
    },
);

const emits = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const model = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue,
});
</script>

<template>
    <AtomField :id="id" :label="label" :hint="hint" :error="error" :class="props.class">
        <textarea
            :id="id"
            v-model="model"
            :name="name"
            :rows="rows"
            :required="required"
            :disabled="disabled"
            :readonly="readonly"
            :placeholder="placeholder"
            :aria-invalid="Boolean(error)"
            :class="cn('min-h-[120px] w-full rounded-lg border border-input bg-white px-4 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] duration-200 placeholder:text-muted-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring', textareaClass)"
            v-bind="$attrs"
        />
    </AtomField>
</template>
