<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { AtomInput, AtomSelect } from '@/components/Atoms';
import { AtomButton as Button } from '@/components/Atoms';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Users',
                href: '/admin/users',
            },
            {
                title: 'Create',
                href: '/admin/users/create',
            },
        ],
    },
});
</script>

<template>
    <Head title="Create user" />

    <div class="page-shell py-6">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold">Create user</h1>
            <p class="text-sm text-muted-foreground">
                Admin-only flow to create employee or admin accounts. A temporary password will be sent by email.
            </p>
        </div>

        <Form action="/admin/users" method="post" enctype="multipart/form-data" v-slot="{ errors, processing }" class="section-card space-y-5">
            <AtomInput id="name" name="name" label="Name" required maxlength="255" placeholder="Full name" :error="errors.name" />

            <AtomInput id="email" type="email" name="email" label="Email" required placeholder="user@example.com" :error="errors.email" />

            <AtomInput id="phone" name="phone" label="Phone" placeholder="+39 333 1234567" :error="errors.phone" />

            <div class="grid gap-4 md:grid-cols-2">
                <AtomInput id="social_reddit" name="socials[reddit]" type="url" label="Reddit" :error="errors['socials.reddit']" />
                <AtomInput id="social_linkedin" name="socials[linkedin]" type="url" label="LinkedIn" :error="errors['socials.linkedin']" />
                <AtomInput id="social_facebook" name="socials[facebook]" type="url" label="Facebook" :error="errors['socials.facebook']" />
                <AtomInput id="social_instagram" name="socials[instagram]" type="url" label="Instagram" :error="errors['socials.instagram']" />
            </div>

            <AtomInput id="social_website" name="socials[website]" type="url" label="Website" :error="errors['socials.website']" />

            <div class="space-y-2">
                <label for="avatar" class="text-sm font-medium">Avatar</label>
                <input
                    id="avatar"
                    name="avatar"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="block w-full text-sm file:mr-3 file:rounded-lg file:border file:border-gray-300 file:bg-white file:px-3 file:py-2 file:text-sm"
                />
                <p v-if="errors.avatar" class="text-sm text-destructive">{{ errors.avatar }}</p>
            </div>

            <AtomSelect
                id="role"
                name="role"
                label="Role"
                required
                default-value="employee"
                :options="[
                    { value: 'employee', label: 'Employee' },
                    { value: 'admin', label: 'Admin' },
                ]"
                :error="errors.role"
            />

            <Button type="submit" :disabled="processing">Create user</Button>
        </Form>
    </div>
</template>

