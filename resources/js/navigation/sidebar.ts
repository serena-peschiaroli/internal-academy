import { CalendarRange, LayoutGrid, UsersRound } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import type { Role } from '@/types/auth';
import type { NavItem } from '@/types/navigation';

type RoleKey = Role['key'];

const baseItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const roleNavigation: Record<RoleKey, NavItem[]> = {
    admin: [
        ...baseItems,
        {
            title: 'Workshops',
            href: '/admin/workshops',
            icon: CalendarRange,
        },
        {
            title: 'Users',
            href: '/admin/users',
            icon: UsersRound,
        },
    ],
    employee: [
        ...baseItems,
        {
            title: 'Workshops',
            href: '/workshops',
            icon: CalendarRange,
        },
    ],
};

export function sidebarItemsForRole(roleKey: RoleKey): NavItem[] {
    return roleNavigation[roleKey];
}
