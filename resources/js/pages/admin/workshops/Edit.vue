<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WorkshopForm from '@/components/admin/workshops/WorkshopForm.vue';

type Workshop = {
    id: number;
    title: string;
    description: string;
    starts_at: string;
    ends_at: string;
    capacity: number;
};

const props = defineProps<{
    workshop: Workshop;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Admin workshops',
                href: '/admin/workshops',
            },
            {
                title: 'Edit workshop',
                href: '/admin/workshops',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Edit ${workshop.title}`" />

    <div class="page-shell py-6">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold">Edit workshop</h1>
            <p class="text-sm text-muted-foreground">
                Update workshop details and schedule information.
            </p>
        </div>
        <WorkshopForm
            :action="`/admin/workshops/${props.workshop.id}`"
            method="patch"
            submit-label="Save changes"
            :workshop="props.workshop"
        />
    </div>
</template>
