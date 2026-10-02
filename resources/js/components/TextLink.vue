<script setup lang="ts">
import { RouterLink } from 'vue-router';
import { toUrl } from '@/lib/utils';
import type { Href } from '@/types';

type Props = {
    href: Href;
    tabindex?: number;
};

defineProps<Props>();
</script>

<template>
    <RouterLink
        :to="toUrl(href)"
        :tabindex="tabindex"
        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
    >
        <slot />
    </RouterLink>
</template>
