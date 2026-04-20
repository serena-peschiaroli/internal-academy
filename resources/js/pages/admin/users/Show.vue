<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Facebook, Globe, Instagram, Linkedin, MessageCircleMore } from 'lucide-vue-next';
import { computed } from 'vue';
import { AtomButton as Button, AtomChip } from '@/components/Atoms';
import { getInitials } from '@/composables/useInitials';

type UserDetail = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    socials?: Record<string, string> | null;
    avatar_url?: string | null;
    role: 'admin' | 'employee';
    email_verified_at: string | null;
    created_at: string | null;
    updated_at: string | null;
};

const props = defineProps<{
    user: UserDetail;
}>();


const formatDate = (value: string | null): string =>
    value
        ? new Date(value).toLocaleString('it-IT', {
            dateStyle: 'medium',
            timeStyle: 'short',
        })
        : '-';

const allSocialRows = computed(() => [
    {
        key: 'reddit',
        label: 'Reddit',
        value: props.user.socials?.reddit ?? '',
        icon: MessageCircleMore,
    },
    {
        key: 'linkedin',
        label: 'LinkedIn',
        value: props.user.socials?.linkedin ?? '',
        icon: Linkedin,
    },
    {
        key: 'facebook',
        label: 'Facebook',
        value: props.user.socials?.facebook ?? '',
        icon: Facebook,
    },
    {
        key: 'instagram',
        label: 'Instagram',
        value: props.user.socials?.instagram ?? '',
        icon: Instagram,
    },
    {
        key: 'website',
        label: 'Website',
        value: props.user.socials?.website ?? '',
        icon: Globe,
    },
]);

const socialRows = computed(() => allSocialRows.value.filter(s => s.value));
</script>

<template>
    <Head :title="`User ${props.user.name}`" />

    <div class="page-shell py-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold">User details</h1>
                <p class="text-sm text-muted-foreground">Read-only profile information for admin management.</p>
            </div>
            <Button as-child variant="outline">
                <Link :href="`/admin/users/${props.user.id}/edit`">Edit user</Link>
            </Button>
        </div>

        <div class="section-card space-y-5">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Avatar</p>
                    <div class="mt-2">
                        <img
                            v-if="props.user.avatar_url"
                            :src="props.user.avatar_url"
                            alt="User avatar"
                            class="h-16 w-16 rounded-full border border-gray-200 object-cover"
                        />
                        <div
                            v-else
                            class="flex h-16 w-16 items-center justify-center rounded-full bg-muted text-lg font-semibold text-muted-foreground"
                            aria-hidden="true"
                        >
                            {{ getInitials(props.user.name) }}
                        </div>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Name</p>
                    <p class="mt-1 text-base font-medium">{{ props.user.name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Email</p>
                    <p class="mt-1 text-base font-medium">{{ props.user.email }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Phone</p>
                    <p class="mt-1 text-base font-medium">{{ props.user.phone || '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Social profiles</p>
                    <div class="mt-2 space-y-2">
                        <div
                            v-for="social in socialRows"
                            :key="social.key"
                            class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2"
                        >
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ social.label }}</p>
                                <p class="mt-0.5 text-sm font-medium text-foreground break-all">{{ social.value }}</p>
                            </div>
                            <a
                                :href="social.value"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-200 text-muted-foreground transition-colors hover:text-foreground"
                                :aria-label="`Open ${social.label}`"
                            >
                                <component :is="social.icon" class="h-4 w-4" />
                            </a>
                        </div>
                        <p v-if="socialRows.length === 0" class="text-sm text-muted-foreground">
                            No social profiles provided.
                        </p>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Role</p>
                    <div class="mt-1">
                        <AtomChip :text="props.user.role === 'admin' ? 'Admin' : 'Employee'" />
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Email verification</p>
                    <p class="mt-1 text-base font-medium">{{ props.user.email_verified_at ? 'Verified' : 'Pending' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Created at</p>
                    <p class="mt-1 text-base font-medium">{{ formatDate(props.user.created_at) }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Last update</p>
                    <p class="mt-1 text-base font-medium">{{ formatDate(props.user.updated_at) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
