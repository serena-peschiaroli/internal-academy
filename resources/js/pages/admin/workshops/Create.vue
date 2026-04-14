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

    <div class="page-shell py-6">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold">Create workshop</h1>
            <p class="text-sm text-muted-foreground">
                Add a new internal academy workshop with date, duration and seat capacity.
            </p>
        </div>
        <WorkshopForm
            action="/admin/workshops"
            method="post"
            submit-label="Create workshop"
        />
    </div>
</template>
