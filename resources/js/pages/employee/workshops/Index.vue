<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { AtomButton as Button } from '@/components/Atoms';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

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

type CancelTarget = {
    workshopId: number;
    workshopTitle: string;
    action: 'cancel' | 'leave-waitlist';
};

const cancelTarget = ref<CancelTarget | null>(null);
const cancelling = ref(false);

const confirmCancel = () => {
    if (!cancelTarget.value) {
        return;
    }

    cancelling.value = true;
    router.delete(`/workshops/${cancelTarget.value.workshopId}/registrations`, {
        preserveScroll: true,
        onFinish: () => {
            cancelling.value = false;
            cancelTarget.value = null;
        },
    });
};
</script>

<template>
    <Head title="Future workshops" />

    <div class="page-shell py-6">
        <div class="section-card space-y-2">
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

        <div class="data-table-wrapper">
            <table class="data-table text-left">
                <thead>
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
                            <span
                                :class="{
                                    'font-semibold text-destructive': workshop.available_seats === 0,
                                    'font-semibold text-amber-600': workshop.available_seats > 0 && workshop.available_seats <= 3,
                                }"
                            >
                                {{ workshop.available_seats }} / {{ workshop.capacity }}
                            </span>
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
                                <Button
                                    v-if="workshop.registration_status === 'confirmed'"
                                    size="sm"
                                    variant="outline"
                                    @click="cancelTarget = { workshopId: workshop.id, workshopTitle: workshop.title, action: 'cancel' }"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    v-else-if="workshop.registration_status === 'waitlisted'"
                                    size="sm"
                                    variant="outline"
                                    @click="cancelTarget = { workshopId: workshop.id, workshopTitle: workshop.title, action: 'leave-waitlist' }"
                                >
                                    Leave waitlist
                                </Button>
                                <Form
                                    v-else
                                    :action="`/workshops/${workshop.id}/registrations`"
                                    method="post"
                                >
                                    <Button size="sm">
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
            <template v-for="link in workshops.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    class="rounded-md border px-3 py-1 text-sm"
                    :class="{ 'bg-primary text-primary-foreground': link.active }"
                >
                    <span v-html="link.label" />
                </Link>
                <span v-else class="pointer-events-none rounded-md border px-3 py-1 text-sm opacity-40">
                    <span v-html="link.label" />
                </span>
            </template>
        </div>

        <Dialog :open="Boolean(cancelTarget)" @update:open="(open) => !open && (cancelTarget = null)">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ cancelTarget?.action === 'leave-waitlist' ? 'Leave waitlist' : 'Cancel registration' }}
                    </DialogTitle>
                    <DialogDescription>
                        <template v-if="cancelTarget?.action === 'leave-waitlist'">
                            You will lose your waitlist position for
                            <strong>"{{ cancelTarget.workshopTitle }}"</strong>.
                            You can re-register later, but you will be placed at the end of the queue.
                        </template>
                        <template v-else>
                            Your confirmed spot in
                            <strong>"{{ cancelTarget?.workshopTitle }}"</strong>
                            will be released and given to the next person on the waitlist.
                        </template>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="outline">Keep my spot</Button>
                    </DialogClose>
                    <Button
                        variant="destructive"
                        :disabled="cancelling"
                        @click="confirmCancel"
                    >
                        {{ cancelling ? 'Cancelling…' : (cancelTarget?.action === 'leave-waitlist' ? 'Leave waitlist' : 'Cancel registration') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

