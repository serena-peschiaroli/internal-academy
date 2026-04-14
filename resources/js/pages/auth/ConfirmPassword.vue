<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { AtomPasswordInput } from '@/components/Atoms';
import { AtomButton as Button } from '@/components/Atoms';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/password/confirm';

defineOptions({
    layout: {
        title: 'Confirm your password',
        description:
            'This is a secure area of the application. Please confirm your password before continuing.',
    },
});
</script>

<template>
    <Head title="Confirm password" />

    <Form
        v-bind="store.form()"
        reset-on-success
        v-slot="{ errors, processing }"
    >
        <div class="space-y-6">
            <AtomPasswordInput
                id="password"
                name="password"
                label="Password"
                required
                autocomplete="current-password"
                autofocus
                :error="errors.password"
            />

            <div class="flex items-center">
                <Button
                    class="w-full"
                    :disabled="processing"
                    data-test="confirm-password-button"
                >
                    <Spinner v-if="processing" />
                    Confirm password
                </Button>
            </div>
        </div>
    </Form>
</template>

