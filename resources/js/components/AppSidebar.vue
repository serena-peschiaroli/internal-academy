<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, CalendarRange, FolderGit2, LayoutGrid, UserCircle2, UsersRound } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { edit as profile } from '@/routes/profile';
import type { NavItem } from '@/types';

const page = usePage();
const roleKey = computed(() => page.props.auth.user.role?.key ?? 'employee');

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (roleKey.value === 'admin') {
        items.push({
            title: 'Workshops',
            href: '/admin/workshops',
            icon: CalendarRange,
        });
        items.push({
            title: 'Create User',
            href: '/admin/users/create',
            icon: UsersRound,
        });

        return items;
    }

    items.push({
        title: 'Workshops',
        href: '/workshops',
        icon: CalendarRange,
    });

    items.push({
        title: 'My Profile',
        href: profile(),
        icon: UserCircle2,
    });

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
