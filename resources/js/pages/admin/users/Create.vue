<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Admin users',
                href: '/admin/users/create',
            },
        ],
    },
});
</script>

<template>
    <Head title="Create user" />

    <div class="mx-auto max-w-2xl space-y-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold">Create user</h1>
            <p class="text-sm text-muted-foreground">
                Admin-only flow to create employee or admin accounts.
            </p>
        </div>

        <Form action="/admin/users" method="post" v-slot="{ errors, processing }" class="space-y-5 rounded-lg border p-5">
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" name="name" required maxlength="255" placeholder="Full name" />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input id="email" type="email" name="email" required placeholder="user@example.com" />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="role">Role</Label>
                <select
                    id="role"
                    name="role"
                    required
                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                >
                    <option value="employee">Employee</option>
                    <option value="admin">Admin</option>
                </select>
                <InputError :message="errors.role" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <Input id="password" type="password" name="password" required minlength="8" />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirm password</Label>
                <Input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button type="submit" :disabled="processing">Create user</Button>
        </Form>
    </div>
</template>
