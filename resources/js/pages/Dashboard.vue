<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { CalendarCheck2, Settings2, Users, UserCheck2 } from 'lucide-vue-next';
import { computed } from 'vue';
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
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="grid gap-4 md:grid-cols-3">
            <Card v-if="isAdmin">
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Admin dashboard</CardTitle>
                    <Users class="size-5 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <CardDescription>
                        Manage workshops, participants and academy operations.
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
                    <CardDescription v-if="isAdmin">
                        Create, update and monitor workshop capacity.
                    </CardDescription>
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
                    <CardDescription v-if="isAdmin">
                        Access security and advanced administration options.
                    </CardDescription>
                    <CardDescription v-else>
                        Update profile and security preferences.
                    </CardDescription>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
