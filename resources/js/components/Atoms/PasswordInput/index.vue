<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { useTemplateRef } from 'vue';
import AtomField from '@/components/Atoms/Field/index.vue';
import PasswordInput from '@/components/PasswordInput.vue';

defineOptions({
    inheritAttrs: false,
});

const props = withDefaults(
    defineProps<{
        id?: string;
        name?: string;
        label?: string;
        hint?: string;
        error?: string;
        class?: HTMLAttributes['class'];
        inputClass?: HTMLAttributes['class'];
    }>(),
    {
        id: undefined,
        name: undefined,
        label: '',
        hint: '',
        error: '',
        class: '',
        inputClass: '',
    },
);

const inputRef = useTemplateRef('inputRef');

defineExpose({
    focus: () => inputRef.value?.focus?.(),
});
</script>

<template>
    <AtomField :id="id" :label="label" :hint="hint" :error="error" :class="props.class">
        <PasswordInput
            :id="id"
            ref="inputRef"
            :name="name"
            :class="inputClass"
            :aria-invalid="Boolean(error)"
            v-bind="$attrs"
        />
    </AtomField>
</template>
