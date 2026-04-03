<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type WorkshopItem = {
    id: number;
    title: string;
    description: string;
    starts_at: string;
    ends_at: string;
    capacity: number;
    creator?: {
        id: number;
        name: string;
    };
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedWorkshops = {
    data: WorkshopItem[];
    links: PaginationLink[];
};

const props = defineProps<{
    workshops: PaginatedWorkshops;
    filters: {
        q: string;
        future_only: boolean;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Admin workshops',
                href: '/admin/workshops',
            },
        ],
    },
});

const filterForm = useForm({
    q: props.filters.q ?? '',
    future_only: props.filters.future_only ?? true,
});

const applyFilters = () => {
    filterForm.get('/admin/workshops', {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    filterForm.q = '';
    filterForm.future_only = true;
    applyFilters();
};

const destroyWorkshop = (id: number) => {
    router.delete(`/admin/workshops/${id}`, {
        preserveScroll: true,
    });
};

const fmt = (value: string): string =>
    new Date(value).toLocaleString('it-IT', {
        dateStyle: 'short',
        timeStyle: 'short',
    });
</script>

<template>
    <Head title="Admin workshops" />

    <div class="space-y-6 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold">Workshop Management</h1>
            <Button as-child>
                <Link href="/admin/workshops/create">
                    <Plus class="mr-2 size-4" />
                    New workshop
                </Link>
            </Button>
        </div>

        <form class="grid gap-3 rounded-lg border p-4 md:grid-cols-[1fr_auto_auto]" @submit.prevent="applyFilters">
            <Input
                v-model="filterForm.q"
                type="text"
                placeholder="Search by title or description"
            />

            <label class="flex items-center gap-2 rounded-md border px-3 py-2 text-sm">
                <input
                    v-model="filterForm.future_only"
                    type="checkbox"
                    class="size-4"
                />
                Future only
            </label>

            <div class="flex gap-2">
                <Button type="submit">Apply</Button>
                <Button type="button" variant="outline" @click="resetFilters">Reset</Button>
            </div>
        </form>

        <div class="overflow-hidden rounded-lg border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">When</th>
                        <th class="px-4 py-3">Capacity</th>
                        <th class="px-4 py-3">Creator</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="workshop in workshops.data" :key="workshop.id" class="border-t">
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ workshop.title }}</div>
                            <div class="text-muted-foreground line-clamp-1">{{ workshop.description }}</div>
                        </td>
                        <td class="px-4 py-3">{{ fmt(workshop.starts_at) }}</td>
                        <td class="px-4 py-3">{{ workshop.capacity }}</td>
                        <td class="px-4 py-3">{{ workshop.creator?.name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <Button as-child size="sm" variant="outline">
                                    <Link :href="`/admin/workshops/${workshop.id}/edit`">Edit</Link>
                                </Button>
                                <Button size="sm" variant="destructive" @click="destroyWorkshop(workshop.id)">
                                    Delete
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="workshops.data.length === 0">
                        <td colspan="5" class="px-4 py-10 text-center text-muted-foreground">
                            No workshops found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-2">
            <Link
                v-for="link in workshops.links"
                :key="link.label"
                :href="link.url ?? ''"
                preserve-scroll
                class="rounded-md border px-3 py-1 text-sm"
                :class="{
                    'pointer-events-none opacity-40': !link.url,
                    'bg-primary text-primary-foreground': link.active,
                }"
                v-html="link.label"
            />
        </div>
    </div>
</template>
