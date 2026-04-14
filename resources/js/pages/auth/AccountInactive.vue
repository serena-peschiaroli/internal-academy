<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

const props = withDefaults(
    defineProps<{
        message?: string;
    }>(),
    {
        message: 'Your account is inactive. Please contact support.',
    },
);

defineOptions({
    layout: {
        title: 'Account inactive',
        description: 'Your account is currently inactive',
    },
});
</script>

<template>
    <Head title="Account inactive" />

    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <div class="space-y-6">
            <h2 class="text-xl font-semibold leading-8 text-gray-900">Account inactive</h2>
            <p class="mt-2 text-sm text-gray-700">{{ props.message }}</p>

            <div class="mt-6">
                <Link
                    href="/login"
                    class="inline-flex items-center rounded-lg bg-[color:var(--color-dark-button)] px-4 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-[color:var(--color-dark-button-hov)]"
                >
                    Back to login
                </Link>
            </div>
        </div>
    </div>
</template>
