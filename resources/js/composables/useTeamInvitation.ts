import { computed, ref, watch } from 'vue';
import type { ComputedRef, Ref } from 'vue';
import { useRoute } from 'vue-router';
import { http } from '@/lib/http';
import { invitation } from '@/routes/api/auth';
import type { TeamInvitationContext } from '@/types';

export type UseTeamInvitationReturn = {
    invitationCode: ComputedRef<string | null>;
    teamInvitation: Ref<TeamInvitationContext | null>;
};

/**
 * Resolve the `?invitation=` code on the auth pages into the invited team.
 */
export function useTeamInvitation(): UseTeamInvitationReturn {
    const route = useRoute();
    const teamInvitation = ref<TeamInvitationContext | null>(null);

    const invitationCode = computed(() =>
        typeof route.query.invitation === 'string'
            ? route.query.invitation
            : null,
    );

    watch(
        invitationCode,
        async (code) => {
            if (!code) {
                teamInvitation.value = null;

                return;
            }

            try {
                const { data } = await http.get<TeamInvitationContext>(
                    invitation({ query: { code } }).url,
                    { silent: true },
                );

                teamInvitation.value = data;
            } catch {
                teamInvitation.value = null;
            }
        },
        { immediate: true },
    );

    return { invitationCode, teamInvitation };
}
