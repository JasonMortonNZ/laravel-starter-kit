<script setup lang="ts">
import { RouterView } from 'vue-router';
import { useLayoutProps } from '@/composables/useLayoutProps';
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

const layout = useLayoutProps();
</script>

<template>
    <AuthLayout :title="layout.title" :description="layout.description">
        <RouterView />
    </AuthLayout>
</template>
