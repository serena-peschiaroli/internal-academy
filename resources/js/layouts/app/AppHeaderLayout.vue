<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
</script>

<template>
    <AppShell variant="header">
        <AppContent variant="header">
            <slot />
        </AppContent>
    </AppShell>
</template>
