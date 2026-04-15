<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import { AtomInput, AtomTextarea } from '@/components/Atoms';
import { AtomButton as Button } from '@/components/Atoms';

type Workshop = {
    id?: number;
    title: string;
    description: string;
    starts_at: string;
    ends_at: string;
    capacity: number;
};

withDefaults(
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
        class="section-card space-y-6"
    >
        <AtomInput
            id="title"
            name="title"
            label="Title"
            required
            maxlength="255"
            :default-value="workshop?.title"
            placeholder="Workshop title"
            :error="errors.title"
        />

        <AtomTextarea
            id="description"
            name="description"
            label="Description"
            required
            :rows="4"
            :default-value="workshop?.description"
            placeholder="Describe the workshop content"
            :error="errors.description"
        />

        <div class="grid gap-4 md:grid-cols-2">
            <AtomInput
                id="starts_at"
                type="datetime-local"
                name="starts_at"
                label="Starts at"
                required
                :default-value="toInputDateTime(workshop?.starts_at)"
                :error="errors.starts_at"
            />

            <AtomInput
                id="ends_at"
                type="datetime-local"
                name="ends_at"
                label="Ends at"
                required
                :default-value="toInputDateTime(workshop?.ends_at)"
                :error="errors.ends_at"
            />
        </div>

        <AtomInput
            id="capacity"
            type="number"
            name="capacity"
            label="Capacity"
            min="1"
            required
            class="md:max-w-xs"
            :default-value="workshop?.capacity ?? 1"
            :error="errors.capacity"
        />

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="processing">{{ submitLabel }}</Button>
            <Button as-child variant="outline">
                <Link href="/admin/workshops">Cancel</Link>
            </Button>
        </div>
    </Form>
</template>

