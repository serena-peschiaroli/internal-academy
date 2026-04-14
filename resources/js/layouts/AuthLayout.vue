<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthSimpleLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage();
const layoutComponent = computed(() =>
    page.component === 'auth/Login' ||
    page.component === 'auth/Register' ||
    page.component === 'auth/ForgotPassword' ||
    page.component === 'auth/AccountInactive' ||
    page.component === 'auth/SecurePassword'
        ? AuthSplitLayout
        : AuthSimpleLayout,
);
</script>

<template>
    <div class="px-4 md:px-2">
    <component :is="layoutComponent" :title="title" :description="description">
        <slot />
    </component>
    </div>
</template>
