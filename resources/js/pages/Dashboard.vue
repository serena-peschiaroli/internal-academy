<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { CalendarCheck2, Settings2, Users, UserCheck2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { AtomButton as Button } from '@/components/Atoms';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const page = usePage();
const roleKey = computed(() => page.props.auth.user.role?.key ?? 'employee');
const isAdmin = computed(() => roleKey.value === 'admin');

type AdminWorkshopStats = {
    workshops_count: number;
    confirmed_registrations_count: number;
    waitlisted_registrations_count: number;
    most_popular_workshop: {
        id: number;
        title: string;
        starts_at: string;
        confirmed_count: number;
    } | null;
    generated_at: string;
};

// Fallback poll interval used only when the WebSocket connection is unavailable.
const FALLBACK_POLL_INTERVAL_MS = 15000;

const adminStats = ref<AdminWorkshopStats | null>(null);
const loadingAdminStats = ref(false);
const adminStatsError = ref<string | null>(null);
const isSocketConnected = ref(false);
let fallbackPollId: number | null = null;

const loadAdminStats = async (): Promise<void> => {
    if (!isAdmin.value || loadingAdminStats.value) {
        return;
    }

    loadingAdminStats.value = true;

    try {
        const response = await fetch('/admin/stats/workshops', {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch admin stats.');
        }

        adminStats.value = (await response.json()) as AdminWorkshopStats;
        adminStatsError.value = null;
    } catch {
        adminStatsError.value = 'Unable to load dashboard stats.';
    } finally {
        loadingAdminStats.value = false;
    }
};

const startFallbackPolling = (): void => {
    if (fallbackPollId !== null) return;
    fallbackPollId = window.setInterval(() => void loadAdminStats(), FALLBACK_POLL_INTERVAL_MS);
};

const stopFallbackPolling = (): void => {
    if (fallbackPollId !== null) {
        window.clearInterval(fallbackPollId);
        fallbackPollId = null;
    }
};

const fmt = (value: string): string =>
    new Date(value).toLocaleString('it-IT', {
        dateStyle: 'short',
        timeStyle: 'short',
    });

const lastUpdatedLabel = computed(() => {
    if (!adminStats.value?.generated_at) {
        return null;
    }

    return fmt(adminStats.value.generated_at);
});

onMounted(() => {
    if (!isAdmin.value) {
        return;
    }

    // Initial fetch — populates the dashboard before the first push arrives.
    void loadAdminStats();

    // Subscribe to the private admin channel via Reverb WebSocket.
    const channel = window.Echo.private('admin.workshop-stats');

    channel
        .listen('.stats.updated', (event: { stats: AdminWorkshopStats }) => {
            adminStats.value = event.stats;
            adminStatsError.value = null;
        })
        .subscribed(() => {
            isSocketConnected.value = true;
            stopFallbackPolling();
        })
        .error(() => {
            isSocketConnected.value = false;
            startFallbackPolling();
        });

    // If the connection drops later, switch to polling until it recovers.
    window.Echo.connector.pusher.connection.bind('disconnected', () => {
        isSocketConnected.value = false;
        startFallbackPolling();
    });

    window.Echo.connector.pusher.connection.bind('connected', () => {
        isSocketConnected.value = true;
        stopFallbackPolling();
    });
});

onBeforeUnmount(() => {
    window.Echo.leave('admin.workshop-stats');
    stopFallbackPolling();
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="page-shell py-6">
        <div class="grid gap-6 md:grid-cols-3">
            <Card v-if="isAdmin">
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Admin dashboard</CardTitle>
                    <Users class="size-5 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div v-if="adminStatsError" class="text-sm text-destructive">
                        {{ adminStatsError }}
                        <div class="mt-3">
                            <Button size="sm" variant="outline" @click="loadAdminStats">
                                Retry
                            </Button>
                        </div>
                    </div>
                    <template v-else-if="adminStats">
                        <CardDescription>
                            Confirmed registrations: <strong>{{ adminStats.confirmed_registrations_count }}</strong>
                        </CardDescription>
                        <CardDescription>
                            Waitlisted users: <strong>{{ adminStats.waitlisted_registrations_count }}</strong>
                        </CardDescription>
                        <CardDescription>
                            Upcoming workshops: <strong>{{ adminStats.workshops_count }}</strong>
                        </CardDescription>
                        <CardDescription v-if="lastUpdatedLabel">
                            Last updated: <strong>{{ lastUpdatedLabel }}</strong>
                        </CardDescription>
                    </template>
                    <CardDescription v-else>
                        {{ loadingAdminStats ? 'Loading live stats...' : 'No stats available yet.' }}
                    </CardDescription>
                </CardContent>
            </Card>

            <Card v-else>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Employee dashboard</CardTitle>
                    <UserCheck2 class="size-5 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <CardDescription>
                        Discover upcoming workshops and manage your registrations.
                    </CardDescription>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle v-if="isAdmin">Workshop management</CardTitle>
                    <CardTitle v-else>Your next workshops</CardTitle>
                    <CalendarCheck2 class="size-5 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <template v-if="isAdmin">
                        <CardDescription v-if="adminStats?.most_popular_workshop">
                            Most popular workshop:
                            <strong>{{ adminStats.most_popular_workshop.title }}</strong>
                            ({{ adminStats.most_popular_workshop.confirmed_count }} confirmed)
                        </CardDescription>
                        <CardDescription v-if="adminStats?.most_popular_workshop">
                            Starts: {{ fmt(adminStats.most_popular_workshop.starts_at) }}
                        </CardDescription>
                        <CardDescription v-else>
                            No upcoming workshop data available.
                        </CardDescription>
                    </template>
                    <CardDescription v-else>
                        Sign up quickly and keep your schedule under control.
                    </CardDescription>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle v-if="isAdmin">Admin tools</CardTitle>
                    <CardTitle v-else>Account and settings</CardTitle>
                    <Settings2 class="size-5 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <template v-if="isAdmin">
                        <CardDescription v-if="isSocketConnected">
                            Live updates via WebSocket (Reverb).
                        </CardDescription>
                        <CardDescription v-else>
                            WebSocket unavailable — polling every {{ FALLBACK_POLL_INTERVAL_MS / 1000 }}s.
                        </CardDescription>
                    </template>
                    <CardDescription v-else>
                        Update profile and security preferences.
                    </CardDescription>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
