<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { readCookie } from '@/lib/utils';
import type { AppVariant } from '@/types';

type Props = {
    variant?: AppVariant;
};

withDefaults(defineProps<Props>(), {
    variant: 'sidebar',
});

const sidebarState = readCookie('sidebar_state');
const isOpen = sidebarState === null || sidebarState === 'true';
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <slot />
    </SidebarProvider>
</template>
