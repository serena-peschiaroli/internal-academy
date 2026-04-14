<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { AtomInput } from '@/components/Atoms';
import TextLink from '@/components/TextLink.vue';
import { AtomButton as Button } from '@/components/Atoms';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineOptions({
    layout: {
        title: 'Create an account',
        description: 'Enter your details below to create your account',
    },
});
</script>

<template>
    <Head title="Register" />

    <Form
        v-bind="store.form()"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <AtomInput
                id="name"
                type="text"
                label="Name"
                required
                autofocus
                :tabindex="1"
                autocomplete="name"
                name="name"
                placeholder="Full name"
                :error="errors.name"
            />

            <AtomInput
                id="email"
                type="email"
                label="Email address"
                required
                :tabindex="2"
                autocomplete="email"
                name="email"
                placeholder="email@example.com"
                :error="errors.email"
            />

            <p class="rounded-lg border border-gray-200 bg-[color:var(--color-light-gray)] px-3 py-2 text-sm text-muted-foreground">
                A temporary password will be sent by email. At first login you will set a secure password.
            </p>

            <Button
                type="submit"
                class="mt-2 w-full"
                tabindex="3"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                Create account
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Already have an account?
            <TextLink
                :href="login()"
                class="underline underline-offset-4"
                :tabindex="4"
                >Log in</TextLink
            >
        </div>
    </Form>
</template>

