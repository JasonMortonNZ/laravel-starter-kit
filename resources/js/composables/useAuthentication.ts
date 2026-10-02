import { useRoute, useRouter } from 'vue-router';
import { toClientPath } from '@/lib/navigation';
import { toAuthResponse } from '@/lib/responses';
import { useAuthStore } from '@/stores/auth';

export type UseAuthenticationReturn = {
    completeAuthentication: (response: unknown) => Promise<void>;
    logout: () => Promise<void>;
};

/**
 * Finish a login, registration, or two-factor challenge: refresh the shared
 * state and navigate to the intended page.
 */
export function useAuthentication(): UseAuthenticationReturn {
    const auth = useAuthStore();
    const route = useRoute();
    const router = useRouter();

    async function completeAuthentication(response: unknown): Promise<void> {
        const data = toAuthResponse(response);

        if (data.two_factor) {
            auth.twoFactorPending = true;

            await router.push({ name: 'two-factor.login', query: route.query });

            return;
        }

        auth.twoFactorPending = false;

        await auth.fetch();

        const intended =
            typeof route.query.redirect === 'string'
                ? route.query.redirect
                : null;

        await router.push(
            intended ??
                (data.redirect
                    ? toClientPath(data.redirect)
                    : auth.dashboardRoute),
        );
    }

    async function logout(): Promise<void> {
        await auth.logout();

        await router.push({ name: 'home' });
    }

    return { completeAuthentication, logout };
}
