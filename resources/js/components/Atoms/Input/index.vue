<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { useVModel } from '@vueuse/core';
import AtomField from '@/components/Atoms/Field/index.vue';
import { Input as UIInput } from '@/components/ui/input';

defineOptions({
    inheritAttrs: false,
});

const props = withDefaults(
    defineProps<{
        id?: string;
        name?: string;
        label?: string;
        hint?: string;
        modelValue?: string | number;
        defaultValue?: string | number;
        type?: string;
        placeholder?: string;
        required?: boolean;
        disabled?: boolean;
        readonly?: boolean;
        autocomplete?: string;
        error?: string;
        class?: HTMLAttributes['class'];
        inputClass?: HTMLAttributes['class'];
    }>(),
    {
        id: undefined,
        name: undefined,
        label: '',
        hint: '',
        modelValue: undefined,
        defaultValue: undefined,
        type: 'text',
        placeholder: '',
        required: false,
        disabled: false,
        readonly: false,
        autocomplete: undefined,
        error: '',
        class: '',
        inputClass: '',
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
        <UIInput
            :id="id"
            v-model="model"
            :name="name"
            :type="type"
            :required="required"
            :disabled="disabled"
            :readonly="readonly"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
            :aria-invalid="Boolean(error)"
            :class="inputClass"
            v-bind="$attrs"
        />
    </AtomField>
</template>
