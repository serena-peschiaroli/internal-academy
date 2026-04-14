<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookOpenText, CalendarRange, ShieldCheck, Users } from 'lucide-vue-next';
import { AtomButton as Button } from '@/components/Atoms';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-[color:var(--color-light-gray-bg)]">
        <header class="border-b border-gray-200 bg-white/95 backdrop-blur-sm">
            <div class="page-shell flex items-center justify-between py-4">
                <div>
                    <p class="text-base font-semibold tracking-tight">Internal Academy</p>
                    <p class="text-sm text-muted-foreground">Workshop management platform</p>
                </div>

                <nav class="flex items-center gap-2">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                    >
                        <Button>Dashboard</Button>
                    </Link>
                    <template v-else>
                        <Link :href="login()">
                            <Button variant="outline">Log in</Button>
                        </Link>
                        <Link v-if="canRegister" :href="register()">
                            <Button>Register</Button>
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="page-shell py-10">
            <section class="section-card overflow-hidden p-0">
                <div class="bg-[image:var(--background-image-dark-gradient)] p-8 text-white sm:p-10">
                    <p class="text-sm font-semibold uppercase tracking-wide text-white/90">
                        Enterprise learning operations
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">
                        Build and manage your internal academy.
                    </h1>
                    <p class="mt-3 max-w-3xl text-base text-white/90">
                        Organize technical and non-technical workshops, manage seat capacity,
                        and track registrations with role-based workflows.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                        >
                            <Button variant="light">Open dashboard</Button>
                        </Link>
                        <template v-else>
                            <Link :href="login()">
                                <Button variant="light">Log in</Button>
                            </Link>
                            <Link v-if="canRegister" :href="register()">
                                <Button variant="outline" class="border-white/70 bg-transparent text-white hover:bg-white/10">
                                    Create account
                                </Button>
                            </Link>
                        </template>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0">
                        <CardTitle class="text-base">Role-based access</CardTitle>
                        <Users class="size-5 text-[color:var(--color-main-darker)]" />
                    </CardHeader>
                    <CardContent>
                        <CardDescription>
                            Separate admin and employee experiences with dedicated permissions and navigation.
                        </CardDescription>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0">
                        <CardTitle class="text-base">Workshop lifecycle</CardTitle>
                        <CalendarRange class="size-5 text-[color:var(--color-main-darker)]" />
                    </CardHeader>
                    <CardContent>
                        <CardDescription>
                            Create, update and monitor workshop schedule, capacity and availability.
                        </CardDescription>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0">
                        <CardTitle class="text-base">Operational reliability</CardTitle>
                        <ShieldCheck class="size-5 text-[color:var(--color-main-darker)]" />
                    </CardHeader>
                    <CardContent>
                        <CardDescription>
                            Waiting list flow, overlap prevention and automated reminder command for participants.
                        </CardDescription>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0">
                        <CardTitle class="text-base">Live monitoring</CardTitle>
                        <BookOpenText class="size-5 text-[color:var(--color-main-darker)]" />
                    </CardHeader>
                    <CardContent>
                        <CardDescription>
                            Admin dashboard statistics refresh automatically with polling for near real-time updates.
                        </CardDescription>
                    </CardContent>
                </Card>
            </section>
        </main>
    </div>
</template>

