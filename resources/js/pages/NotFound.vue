<script setup lang="ts">
import { RouterLink } from 'vue-router';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 text-center"
    >
        <div class="space-y-2">
            <p class="text-sm font-medium text-muted-foreground">404</p>
            <h1 class="text-2xl font-semibold">Page not found</h1>
            <p class="text-sm text-muted-foreground">
                The page you are looking for does not exist or has moved.
            </p>
        </div>

        <Button as-child>
            <RouterLink :to="home().url">Go home</RouterLink>
        </Button>
    </div>
</template>
