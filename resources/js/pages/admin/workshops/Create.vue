<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WorkshopForm from '@/components/admin/workshops/WorkshopForm.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Admin workshops',
                href: '/admin/workshops',
            },
            {
                title: 'Create workshop',
                href: '/admin/workshops/create',
            },
        ],
    },
});
</script>

<template>
    <Head title="Create workshop" />

    <div class="mx-auto max-w-3xl space-y-6 p-4">
        <h1 class="text-2xl font-semibold">Create workshop</h1>
        <WorkshopForm
            action="/admin/workshops"
            method="post"
            submit-label="Create workshop"
        />
    </div>
</template>
