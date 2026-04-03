<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

type WorkshopItem = {
    id: number;
    title: string;
    description: string;
    starts_at: string;
    ends_at: string;
    capacity: number;
    confirmed_count: number;
    available_seats: number;
    registration_status: 'confirmed' | 'waitlisted' | null;
    user_waitlist_position: number | null;
    creator: {
        id: number;
        name: string;
    } | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

defineProps<{
    workshops: {
        data: WorkshopItem[];
        links: PaginationLink[];
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Future workshops',
                href: '/workshops',
            },
        ],
    },
});

const fmt = (value: string): string =>
    new Date(value).toLocaleString('it-IT', {
        dateStyle: 'short',
        timeStyle: 'short',
    });

const page = usePage();
</script>

<template>
    <Head title="Future workshops" />

    <div class="space-y-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold">Future workshops</h1>
            <p class="text-sm text-muted-foreground">
                Browse upcoming internal academy sessions.
            </p>
            <p
                v-if="page.props.errors.workshop"
                class="mt-2 text-sm font-medium text-destructive"
            >
                {{ page.props.errors.workshop }}
            </p>
        </div>

        <div class="overflow-hidden rounded-lg border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th class="px-4 py-3">Workshop</th>
                        <th class="px-4 py-3">Starts</th>
                        <th class="px-4 py-3">Seats</th>
                        <th class="px-4 py-3">Your status</th>
                        <th class="px-4 py-3">Creator</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="workshop in workshops.data" :key="workshop.id" class="border-t">
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ workshop.title }}</div>
                            <div class="line-clamp-1 text-muted-foreground">{{ workshop.description }}</div>
                        </td>
                        <td class="px-4 py-3">{{ fmt(workshop.starts_at) }}</td>
                        <td class="px-4 py-3">
                            {{ workshop.available_seats }} / {{ workshop.capacity }}
                        </td>
                        <td class="px-4 py-3">
                            <Badge v-if="workshop.registration_status === 'confirmed'">
                                Confirmed
                            </Badge>
                            <Badge v-else-if="workshop.registration_status === 'waitlisted'" variant="secondary">
                                Waitlisted
                                <span v-if="workshop.user_waitlist_position !== null" class="ml-1">
                                    #{{ workshop.user_waitlist_position }}
                                </span>
                            </Badge>
                            <span v-else class="text-muted-foreground">Not registered</span>
                        </td>
                        <td class="px-4 py-3">{{ workshop.creator?.name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end">
                                <Form
                                    v-if="workshop.registration_status === 'confirmed'"
                                    :action="`/workshops/${workshop.id}/registrations`"
                                    method="delete"
                                >
                                    <Button size="sm" variant="outline">Cancel</Button>
                                </Form>
                                <Form
                                    v-else-if="workshop.registration_status === 'waitlisted'"
                                    :action="`/workshops/${workshop.id}/registrations`"
                                    method="delete"
                                >
                                    <Button size="sm" variant="outline">Leave waitlist</Button>
                                </Form>
                                <Form
                                    v-else
                                    :action="`/workshops/${workshop.id}/registrations`"
                                    method="post"
                                >
                                    <Button
                                        size="sm"
                                    >
                                        {{ workshop.available_seats < 1 ? 'Join waitlist' : 'Register' }}
                                    </Button>
                                </Form>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="workshops.data.length === 0">
                        <td colspan="6" class="px-4 py-10 text-center text-muted-foreground">
                            No upcoming workshops available.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-2">
            <a
                v-for="link in workshops.links"
                :key="link.label"
                :href="link.url ?? '#'"
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
