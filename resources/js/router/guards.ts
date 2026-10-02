import type { Router } from 'vue-router';
import { resetLayoutProps } from '@/composables/useLayoutProps';
import { setPageTitle } from '@/composables/usePageTitle';
import { useAuthStore } from '@/stores/auth';

/**
 * Mirror the server-side middleware (guest, auth, verified, team membership)
 * so navigation is decided client-side without a round trip.
 */
export function registerGuards(router: Router): void {
    router.beforeEach(async (to) => {
        const auth = useAuthStore();

        await auth.ensureLoaded();

        const requires = (key: 'auth' | 'guest' | 'verified') =>
            to.matched.some((record) => record.meta[key]);

        if (to.meta.twoFactor) {
            return auth.twoFactorPending ? true : { name: 'login' };
        }

        if (requires('guest') && auth.isAuthenticated) {
            return auth.dashboardRoute;
        }

        if (requires('auth') && !auth.isAuthenticated) {
            return { name: 'login', query: { redirect: to.fullPath } };
        }

        const needsVerification =
            auth.features.must_verify_email && !auth.isVerified;

        if (requires('verified') && needsVerification) {
            return { name: 'verification.notice' };
        }

        if (to.name === 'verification.notice' && !needsVerification) {
            return auth.dashboardRoute;
        }

        if (to.meta.team) {
            const slug = to.params.team;

            if (!auth.teams.some((team) => team.slug === slug)) {
                return auth.currentTeam
                    ? auth.dashboardRoute
                    : { name: 'home' };
            }
        }

        return true;
    });

    router.afterEach((to) => {
        resetLayoutProps(to.meta.layout);
        setPageTitle(to.meta.title);
    });
}
