import { AxiosError } from 'axios';
import type { InternalAxiosRequestConfig } from 'axios';
import { toast } from 'vue-sonner';
import { http } from '@/lib/http';
import { isRecord } from '@/lib/responses';
import { router } from '@/router';
import { bootstrap } from '@/routes/api';
import { useAuthStore } from '@/stores/auth';
import type { FlashToast, Team, User } from '@/types';

type SharedPayload = {
    toast?: FlashToast;
    currentTeam?: Team | null;
    user?: User;
    teams?: Team[];
};

type RetriableConfig = InternalAxiosRequestConfig & { retried?: boolean };

const UNVERIFIED_MESSAGE = 'Your email address is not verified.';

/**
 * Keep the auth store in sync with any shared state the server includes in a
 * response (current team, user, teams) and surface toast notifications.
 */
function syncSharedState(payload: unknown): void {
    if (!isRecord(payload)) {
        return;
    }

    const data = payload as SharedPayload;
    const auth = useAuthStore();

    if ('currentTeam' in data) {
        auth.setCurrentTeam(data.currentTeam ?? null);
    }

    if (Array.isArray(data.teams)) {
        auth.setTeams(data.teams);
    }

    if (isRecord(data.user)) {
        auth.setUser(data.user as User);
    }

    if (data.toast) {
        toast[data.toast.type](data.toast.message);
    }
}

function messageFrom(error: AxiosError): string {
    const data = error.response?.data;

    if (isRecord(data) && typeof data.message === 'string' && data.message) {
        return data.message;
    }

    return 'Something went wrong. Please try again.';
}

function hasValidationErrors(error: AxiosError): boolean {
    const data = error.response?.data;

    return isRecord(data) && isRecord(data.errors);
}

export function installHttpInterceptors(): void {
    http.interceptors.response.use(
        (response) => {
            syncSharedState(response.data);

            return response;
        },
        async (error: unknown) => {
            if (!(error instanceof AxiosError)) {
                throw error;
            }

            const config = error.config as RetriableConfig | undefined;
            const current = router.currentRoute.value;

            if (!error.response) {
                if (!config?.silent) {
                    toast.error(
                        'Unable to reach the server. Check your connection and try again.',
                    );
                }

                throw error;
            }

            switch (error.response.status) {
                case 401: {
                    useAuthStore().reset();

                    if (!current.matched.some((record) => record.meta.guest)) {
                        void router.push({
                            name: 'login',
                            query: { redirect: current.fullPath },
                        });
                    }

                    break;
                }

                case 419: {
                    if (config && !config.retried) {
                        config.retried = true;

                        await http.get(bootstrap().url, { silent: true });

                        return http.request(config);
                    }

                    window.location.reload();

                    break;
                }

                case 422:
                    break;

                case 423: {
                    void router.push({
                        name: 'password.confirm',
                        query: { redirect: current.fullPath },
                    });

                    break;
                }

                case 403: {
                    if (messageFrom(error) === UNVERIFIED_MESSAGE) {
                        void router.push({ name: 'verification.notice' });
                    } else if (!config?.silent) {
                        toast.error(messageFrom(error));
                    }

                    break;
                }

                case 429: {
                    if (!hasValidationErrors(error) && !config?.silent) {
                        toast.error(
                            'Too many attempts. Please try again later.',
                        );
                    }

                    break;
                }

                default: {
                    if (!config?.silent) {
                        toast.error(messageFrom(error));
                    }
                }
            }

            throw error;
        },
    );
}
