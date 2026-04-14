<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ChevronRight, Menu, X } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { getInitials } from '@/composables/useInitials';
import { toUrl } from '@/lib/utils';
import { sidebarItemsForRole } from '@/navigation/sidebar';
import type { BreadcrumbItem } from '@/types';

const props = withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    { breadcrumbs: () => [] },
);

const page = usePage();
const user = computed(() => page.props.auth.user);
const roleKey = computed(() => page.props.auth.user.role?.key ?? 'employee');

const currentPath = computed(() => {
    try {
        return new URL(page.url, window.location.origin).pathname;
    } catch {
        return page.url;
    }
});
const isActive = (path: string): boolean => currentPath.value === path;

const mobileSidebarOpen = ref(false);
const desktopSidebarOpen = ref(true);

const navItems = computed(() => sidebarItemsForRole(roleKey.value));
const navHref = (href: unknown): string => toUrl(href as string);

const toggleDesktopSidebar = (): void => {
    desktopSidebarOpen.value = !desktopSidebarOpen.value;
    localStorage.setItem('sidebar-expanded', String(desktopSidebarOpen.value));
};

onMounted(() => {
    desktopSidebarOpen.value = localStorage.getItem('sidebar-expanded') !== 'false';
});

const logout = (): void => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-[color:#72a481]">
        <div class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
            <Transition
                enter-active-class="transition-opacity ease-linear duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity ease-linear duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="mobileSidebarOpen"
                    class="fixed inset-0 bg-black/60"
                    @click="mobileSidebarOpen = false"
                />
            </Transition>

            <Transition
                enter-active-class="transition ease-in-out duration-300 transform"
                enter-from-class="-translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition ease-in-out duration-300 transform"
                leave-from-class="translate-x-0"
                leave-to-class="-translate-x-full"
            >
                <div v-if="mobileSidebarOpen" class="fixed inset-0 flex">
                    <div class="relative mr-16 flex w-full max-w-xs flex-1">
                        <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                            <button type="button" class="-m-2.5 p-2.5 text-white" @click="mobileSidebarOpen = false">
                                <span class="sr-only">Close sidebar</span>
                                <X class="size-6" />
                            </button>
                        </div>

                        <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-[color:#72a481] px-6 pb-4 text-white">
                            <nav class="flex flex-1 flex-col">
                                <ul role="list" class="-mx-2 space-y-1">
                                    <li v-for="item in navItems" :key="item.title">
                                        <Link
                                            :href="item.href"
                                            class="group flex items-center gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-white/90 hover:bg-white/15 hover:text-white"
                                            :class="{ '!bg-white/20 !text-white': isActive(navHref(item.href)) }"
                                            @click="mobileSidebarOpen = false"
                                        >
                                            <component :is="item.icon" class="size-5 shrink-0" />
                                            {{ item.title }}
                                        </Link>
                                    </li>
                                </ul>
                            </nav>
                            <div class="mt-auto border-t border-white/20 pt-4">
                                <Link href="/dashboard" class="flex items-center justify-center">
                                    <img src="/images/logo-small-light.png" alt="Logo" class="h-12 w-auto object-contain" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <Transition
            enter-active-class="transition-all ease-in-out duration-300"
            leave-active-class="transition-all ease-in-out duration-300"
        >
            <aside
                v-if="desktopSidebarOpen"
                class="hidden lg:fixed lg:inset-y-0 lg:top-16 lg:z-30 lg:flex lg:w-72 lg:flex-col"
            >
                <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-[color:#72a481] px-6 pb-4 text-white">
                    <nav class="flex flex-1 flex-col">
                        <ul role="list" class="-mx-2 space-y-1">
                            <li v-for="item in navItems" :key="item.title">
                                <Link
                                    :href="item.href"
                                    class="group flex items-center gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-white/90 hover:bg-white/15 hover:text-white"
                                    :class="{ '!bg-white/20 !text-white': isActive(navHref(item.href)) }"
                                >
                                    <component :is="item.icon" class="size-5 shrink-0" />
                                    {{ item.title }}
                                </Link>
                            </li>
                        </ul>
                    </nav>

                    <div class="mt-auto border-t border-white/20 pt-4">
                        <Link href="/dashboard" class="flex items-center justify-center">
                            <img src="/images/logo-small-light.png" alt="Logo" class="h-12 w-auto object-contain" />
                        </Link>
                    </div>
                </div>
            </aside>
        </Transition>

        <div
            class="transition-[padding] duration-300 ease-in-out"
            :class="{ 'lg:pl-72': desktopSidebarOpen }"
        >
            <header class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-white/20 bg-[color:#72a481] px-4 text-white shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button
                    type="button"
                    class="-m-2.5 p-2.5 text-white lg:hidden"
                    @click="mobileSidebarOpen = true"
                >
                    <span class="sr-only">Open sidebar</span>
                    <Menu class="size-6" />
                </button>

                <button
                    type="button"
                    class="-m-2.5 hidden p-2.5 text-white lg:block"
                    @click="toggleDesktopSidebar"
                >
                    <span class="sr-only">Toggle sidebar</span>
                    <Menu class="size-6" />
                </button>

                <div class="h-6 w-px bg-white/25 lg:hidden" aria-hidden="true" />

                <div class="flex min-w-0 flex-1 items-center gap-x-2 text-sm text-white/80">
                    <template v-if="props.breadcrumbs.length">
                        <template v-for="(crumb, i) in props.breadcrumbs" :key="i">
                            <Link
                                v-if="crumb.href"
                                :href="crumb.href"
                                class="truncate hover:text-white"
                            >{{ crumb.title }}</Link>
                            <span v-else class="truncate text-white">{{ crumb.title }}</span>
                            <ChevronRight v-if="i < props.breadcrumbs.length - 1" class="size-3.5 shrink-0 text-white/60" />
                        </template>
                    </template>
                </div>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <button
                            type="button"
                            class="-m-1.5 flex items-center rounded-lg border border-white/30 bg-white/10 p-1.5 pr-3 text-white hover:bg-white/20"
                        >
                            <span class="sr-only">Open user menu</span>
                            <Avatar class="size-8 overflow-hidden rounded-full">
                                <AvatarImage
                                    v-if="user.avatar"
                                    :src="user.avatar"
                                    :alt="user.name"
                                />
                                <AvatarFallback class="bg-white/20 text-white">
                                    {{ getInitials(user?.name) }}
                                </AvatarFallback>
                            </Avatar>
                            <span class="ml-2 hidden max-w-36 truncate text-sm font-medium sm:block">
                                {{ user?.name }}
                            </span>
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56 rounded-lg">
                        <UserMenuContent :user="user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </header>

            <main class="min-h-[calc(100svh-4rem)] bg-[color:var(--color-light-gray-bg)] py-10">
                <div class="px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
