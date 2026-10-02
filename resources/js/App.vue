<script setup lang="ts">
import { RouterView } from 'vue-router';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="contents">
        <RouterView />
        <Toaster />
    </div>
</template>
