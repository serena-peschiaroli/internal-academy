<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { useVModel } from '@vueuse/core';
import AtomField from '@/components/Atoms/Field/index.vue';
import { cn } from '@/lib/utils';

defineOptions({
    inheritAttrs: false,
});

type Option = {
    label: string;
    value: string | number;
};

const props = withDefaults(
    defineProps<{
        id?: string;
        name?: string;
        label?: string;
        hint?: string;
        modelValue?: string | number;
        defaultValue?: string | number;
        required?: boolean;
        disabled?: boolean;
        error?: string;
        options: Option[];
        class?: HTMLAttributes['class'];
        selectClass?: HTMLAttributes['class'];
    }>(),
    {
        id: undefined,
        name: undefined,
        label: '',
        hint: '',
        modelValue: undefined,
        defaultValue: undefined,
        required: false,
        disabled: false,
        error: '',
        class: '',
        selectClass: '',
    },
);

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

const model = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue,
});
</script>

<template>
    <AtomField :id="id" :label="label" :hint="hint" :error="error" :class="props.class">
        <select
            :id="id"
            v-model="model"
            :name="name"
            :required="required"
            :disabled="disabled"
            :aria-invalid="Boolean(error)"
            :class="cn('flex h-10 w-full rounded-lg border border-input bg-white px-4 py-2 text-sm shadow-xs transition-[color,box-shadow] duration-200 outline-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring', selectClass)"
            v-bind="$attrs"
        >
            <option
                v-for="option in options"
                :key="`${option.value}`"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>
    </AtomField>
</template>
