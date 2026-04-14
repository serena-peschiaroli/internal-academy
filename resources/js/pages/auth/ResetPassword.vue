<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { AtomInput, AtomPasswordInput } from '@/components/Atoms';
import { AtomButton as Button } from '@/components/Atoms';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Reset password',
        description: 'Please enter your new password below',
    },
});

const props = defineProps<{
    token: string;
    email: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <Head title="Reset password" />

    <Form
        v-bind="update.form()"
        :transform="(data) => ({ ...data, token, email })"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
    >
        <div class="grid gap-6">
            <AtomInput
                id="email"
                type="email"
                name="email"
                label="Email"
                autocomplete="email"
                v-model="inputEmail"
                readonly
                :error="errors.email"
            />

            <AtomPasswordInput
                id="password"
                name="password"
                label="Password"
                autocomplete="new-password"
                autofocus
                placeholder="Password"
                :error="errors.password"
            />

            <AtomPasswordInput
                id="password_confirmation"
                name="password_confirmation"
                label="Confirm password"
                autocomplete="new-password"
                placeholder="Confirm password"
                :error="errors.password_confirmation"
            />

            <Button
                type="submit"
                class="mt-4 w-full"
                :disabled="processing"
                data-test="reset-password-button"
            >
                <Spinner v-if="processing" />
                Reset password
            </Button>
        </div>
    </Form>
</template>

