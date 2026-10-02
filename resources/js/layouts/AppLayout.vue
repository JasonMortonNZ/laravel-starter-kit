<script setup lang="ts">
import { RouterView } from 'vue-router';
import { useLayoutProps } from '@/composables/useLayoutProps';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';

const layout = useLayoutProps();
</script>

<template>
    <AppLayout :breadcrumbs="layout.breadcrumbs">
        <RouterView />
    </AppLayout>
</template>
