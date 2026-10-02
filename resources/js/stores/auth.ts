import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import type { RouteLocationRaw } from 'vue-router';
import { http } from '@/lib/http';
import { logout as logoutRoute } from '@/routes';
import { bootstrap } from '@/routes/api';
import type { BootstrapData, Features, Team, User } from '@/types';

const defaultFeatures: Features = {
    canRegister: true,
    canResetPassword: true,
    canManageTwoFactor: false,
    requiresTwoFactorConfirmation: false,
    mustVerifyEmail: true,
};

/**
 * Shared application state that Inertia used to provide as page props:
 * the authenticated user, their teams, feature flags, and the app name.
 */
export const useAuthStore = defineStore('auth', () => {
    const name = ref<string>(import.meta.env.VITE_APP_NAME || 'Laravel');
    const user = ref<User | null>(null);
    const currentTeam = ref<Team | null>(null);
    const teams = ref<Team[]>([]);
    const features = ref<Features>({ ...defaultFeatures });
    const passwordRules = ref('');
    const loaded = ref(false);
    const twoFactorPending = ref(false);

    let pending: Promise<void> | null = null;

    const isAuthenticated = computed(() => user.value !== null);
    const isVerified = computed(() => user.value?.email_verified_at != null);
    const dashboardRoute = computed<RouteLocationRaw>(() =>
        currentTeam.value
            ? { name: 'dashboard', params: { team: currentTeam.value.slug } }
            : { name: 'home' },
    );

    function setUser(value: User | null): void {
        user.value = value;
    }

    function setTeams(value: Team[]): void {
        teams.value = value;
    }

    function setCurrentTeam(value: Team | null): void {
        currentTeam.value = value;
        teams.value = teams.value.map((team) => ({
            ...team,
            isCurrent: team.id === value?.id,
        }));
    }

    function apply(data: BootstrapData): void {
        name.value = data.name;
        user.value = data.auth.user;
        teams.value = data.teams;
        currentTeam.value = data.currentTeam;
        features.value = data.features;
        passwordRules.value = data.passwordRules;
        loaded.value = true;
    }

    async function fetch(): Promise<void> {
        const { data } = await http.get<BootstrapData>(bootstrap().url);

        apply(data);
    }

    function ensureLoaded(): Promise<void> {
        if (loaded.value) {
            return Promise.resolve();
        }

        pending ??= fetch().finally(() => {
            pending = null;
        });

        return pending;
    }

    function reset(): void {
        user.value = null;
        currentTeam.value = null;
        teams.value = [];
        twoFactorPending.value = false;
    }

    async function logout(): Promise<void> {
        await http.request(logoutRoute());

        reset();
    }

    return {
        name,
        user,
        currentTeam,
        teams,
        features,
        passwordRules,
        loaded,
        twoFactorPending,
        isAuthenticated,
        isVerified,
        dashboardRoute,
        setUser,
        setTeams,
        setCurrentTeam,
        apply,
        fetch,
        ensureLoaded,
        reset,
        logout,
    };
});
