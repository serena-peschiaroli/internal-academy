<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Workshop = {
    id?: number;
    title: string;
    description: string;
    starts_at: string;
    ends_at: string;
    capacity: number;
};

const props = withDefaults(
    defineProps<{
        action: string;
        method: 'post' | 'patch';
        submitLabel: string;
        workshop?: Workshop | null;
    }>(),
    {
        workshop: null,
    },
);

const toInputDateTime = (value?: string): string => {
    if (!value) {
        return '';
    }

    return value.replace(' ', 'T').slice(0, 16);
};
</script>

<template>
    <Form
        :action="action"
        :method="method"
        v-slot="{ errors, processing }"
        class="space-y-6"
    >
        <div class="grid gap-2">
            <Label for="title">Title</Label>
            <Input
                id="title"
                name="title"
                required
                maxlength="255"
                :default-value="workshop?.title"
                placeholder="Workshop title"
            />
            <InputError :message="errors.title" />
        </div>

        <div class="grid gap-2">
            <Label for="description">Description</Label>
            <textarea
                id="description"
                name="description"
                required
                rows="4"
                class="min-h-[120px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                :default-value="workshop?.description"
                placeholder="Describe the workshop content"
            />
            <InputError :message="errors.description" />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <Label for="starts_at">Starts at</Label>
                <Input
                    id="starts_at"
                    type="datetime-local"
                    name="starts_at"
                    required
                    :default-value="toInputDateTime(workshop?.starts_at)"
                />
                <InputError :message="errors.starts_at" />
            </div>

            <div class="grid gap-2">
                <Label for="ends_at">Ends at</Label>
                <Input
                    id="ends_at"
                    type="datetime-local"
                    name="ends_at"
                    required
                    :default-value="toInputDateTime(workshop?.ends_at)"
                />
                <InputError :message="errors.ends_at" />
            </div>
        </div>

        <div class="grid gap-2 md:max-w-xs">
            <Label for="capacity">Capacity</Label>
            <Input
                id="capacity"
                type="number"
                name="capacity"
                min="1"
                required
                :default-value="workshop?.capacity ?? 1"
            />
            <InputError :message="errors.capacity" />
        </div>

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="processing">{{ submitLabel }}</Button>
            <Button as-child variant="outline">
                <Link href="/admin/workshops">Cancel</Link>
            </Button>
        </div>
    </Form>
</template>
