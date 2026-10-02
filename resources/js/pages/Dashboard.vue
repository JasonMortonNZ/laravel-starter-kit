<script setup lang="ts">
import { computed, watchEffect } from 'vue';
import { useRoute } from 'vue-router';
import PendingInvitationsModal from '@/components/PendingInvitationsModal.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { setLayoutProps } from '@/composables/useLayoutProps';
import { usePageData } from '@/composables/usePageData';
import { http } from '@/lib/http';
import { dashboard } from '@/routes';
import { dashboard as dashboardData } from '@/routes/api';
import { useAuthStore } from '@/stores/auth';
import type { DashboardData } from '@/types';

const auth = useAuthStore();
const route = useRoute();

const teamSlug = computed(() =>
    typeof route.params.team === 'string' ? route.params.team : null,
);

const { data, reload } = usePageData(
    async () =>
        (
            await http.get<DashboardData>(
                dashboardData(String(teamSlug.value)).url,
            )
        ).data,
    {
        watch: teamSlug,
        when: () => teamSlug.value !== null,
    },
);

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: auth.currentTeam ? dashboard(auth.currentTeam.slug) : '/',
            },
        ],
    });
});
</script>

<template>
    <PendingInvitationsModal
        v-if="data && data.pendingInvitations.length > 0"
        :invitations="data.pendingInvitations"
        @changed="reload"
    />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
        <div
            class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
        >
            <PlaceholderPattern />
        </div>
    </div>
</template>
