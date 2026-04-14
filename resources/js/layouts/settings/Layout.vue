<script setup lang="ts">
import Heading from '@/components/Heading.vue';
</script>

<template>
    <div class="page-shell py-6">
        <Heading
            title="Settings"
            description="Manage your profile information and account"
        />

        <div class="md:max-w-3xl">
            <section class="space-y-6">
                <slot />
            </section>
        </div>
    </div>
</template>
