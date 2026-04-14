<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { AtomButton as Button, AtomTabs } from '@/components/Atoms';
import DataTable from '@/components/DataTable/index.vue';

type UserRow = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    socials?: Record<string, string> | null;
    avatar_url?: string | null;
    role: 'admin' | 'employee';
    email_verified_at: string | null;
    created_at: string | null;
};

const props = defineProps<{
    admins: UserRow[];
    employees: UserRow[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Users',
                href: '/admin/users',
            },
        ],
    },
});

const activeTab = ref<'admins' | 'employees'>('admins');

const tabs = computed(() => [
    {
        key: 'admins',
        label: 'Admins',
        count: props.admins.length,
    },
    {
        key: 'employees',
        label: 'Employee',
        count: props.employees.length,
    },
]);

const rows = computed(() => {
    const baseRows = activeTab.value === 'admins' ? props.admins : props.employees;

    return baseRows.map((user) => ({
        ...user,
        verified_status: user.email_verified_at ? 'verified' : 'pending',
        socials_text: user.socials ? Object.values(user.socials).join(', ') : '-',
        search_text: `${user.name} ${user.email} ${user.phone ?? ''}`.toLowerCase(),
    }));
});

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'phone', label: 'Phone', sortable: true },
    { key: 'socials_text', label: 'Socials', sortable: false },
    {
        key: 'verified_status',
        label: 'Verification',
        sortable: true,
        format: (row: Record<string, unknown>) => (row.verified_status === 'verified' ? 'Verified' : 'Pending'),
    },
    {
        key: 'created_at',
        label: 'Created',
        sortable: true,
        format: (row: Record<string, unknown>) => {
            const value = row.created_at as string | null;
            return value
                ? new Date(value).toLocaleDateString('it-IT', { dateStyle: 'medium' })
                : '-';
        },
    },
    {
        key: 'actions',
        label: 'Actions',
        slot: 'actions',
    },
];

const filtersConfig: Array<{
    key: string;
    label: string;
    type: 'text' | 'select';
    placeholder?: string;
    columnKey?: string;
    options?: Array<{ label: string; value: string }>;
}> = [
    {
        key: 'search',
        label: 'Search',
        type: 'text',
        placeholder: 'Search by name or email',
        columnKey: 'search_text',
    },
    {
        key: 'verification',
        label: 'Verification',
        type: 'select',
        columnKey: 'verified_status',
        options: [
            { label: 'Verified', value: 'verified' },
            { label: 'Pending', value: 'pending' },
        ],
    },
];

const deleteUser = (id: number): void => {
    router.delete(`/admin/users/${id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Users" />

    <div class="page-shell py-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold">Users</h1>
                <p class="text-sm text-muted-foreground">Browse admin and employee accounts with tab-based views and filters.</p>
            </div>

            <Button as-child>
                <Link href="/admin/users/create">Create user</Link>
            </Button>
        </div>

        <AtomTabs v-model="activeTab" :tabs="tabs" />

        <DataTable
            :rows="rows"
            :columns="columns"
            :filters-config="filtersConfig"
            :read-only="true"
            :table-key="`admin-users-${activeTab}`"
        >
            <template #actions="{ row }">
                <div class="flex justify-end gap-2">
                    <Button as-child size="sm" variant="outline">
                        <Link :href="`/admin/users/${row.id}`">View</Link>
                    </Button>
                    <Button as-child size="sm" variant="outline">
                        <Link :href="`/admin/users/${row.id}/edit`">Edit</Link>
                    </Button>
                    <Button size="sm" variant="destructive" @click="deleteUser(row.id as number)">
                        Delete
                    </Button>
                </div>
            </template>
        </DataTable>
    </div>
</template>
