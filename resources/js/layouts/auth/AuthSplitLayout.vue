<script setup lang="ts">
defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="grid min-h-svh grid-cols-6 gap-x-2 bg-primary">
        <div
            class="col-span-6 flex items-center justify-center px-6 py-8 md:order-2 md:col-span-3 md:px-10"
        >
            <img
                src="/images/logo-big-light.png"
                alt="Internal Academy"
                class="w-full max-w-md object-contain"
            />
        </div>

        <div class="col-span-6 flex items-center justify-center px-6 py-8 md:col-span-3 md:px-10">
            <div
                class="w-full max-w-md rounded-lg bg-white p-6 shadow-sm sm:p-8"
            >
                <div class="mb-6 space-y-1 text-left">
                    <h1 class="text-xl font-semibold tracking-tight" v-if="title">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-muted-foreground" v-if="description">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
