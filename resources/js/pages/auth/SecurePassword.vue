<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { AtomButton as Button, AtomInput } from '@/components/Atoms';
import { Spinner } from '@/components/ui/spinner';

defineOptions({
    layout: {
        title: 'Set secure password',
        description: 'First access: choose a new password',
    },
});

const form = useForm({
    password: '',
    password_confirmation: '',
});

const passwordsDoNotMatch = computed(
    () =>
        form.password_confirmation.length > 0
        && form.password !== form.password_confirmation,
);

const isSubmitDisabled = computed(
    () =>
        form.processing
        || !form.password
        || !form.password_confirmation
        || passwordsDoNotMatch.value,
);

const submit = (): void => {
    if (isSubmitDisabled.value) {
        return;
    }

    form.put('/secure-password', {
        preserveScroll: true,
        onSuccess: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Set secure password" />

    <form class="flex flex-col gap-6" @submit.prevent="submit">
        <div class="grid gap-6">
            <AtomInput
                id="password"
                v-model="form.password"
                type="password"
                label="New password"
                required
                autofocus
                autocomplete="new-password"
                placeholder="New password"
                :error="form.errors.password"
            />

            <AtomInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                label="Confirm password"
                required
                autocomplete="new-password"
                placeholder="Confirm password"
                :error="form.errors.password_confirmation"
            />
            <p v-if="passwordsDoNotMatch" class="-mt-4 text-sm text-destructive">
                Passwords do not match.
            </p>

            <Button
                type="submit"
                class="mt-2 w-full"
                :disabled="isSubmitDisabled"
            >
                <Spinner v-if="form.processing" />
                Save password
            </Button>
        </div>
    </form>
</template>
