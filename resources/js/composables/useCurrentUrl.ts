import type { ComputedRef, DeepReadonly } from 'vue';
import { computed, readonly } from 'vue';
import { useRoute } from 'vue-router';
import { toUrl } from '@/lib/utils';
import type { Href } from '@/types';

export type UseCurrentUrlReturn = {
    currentUrl: DeepReadonly<ComputedRef<string>>;
    isCurrentUrl: (
        urlToCheck: Href,
        currentUrl?: string,
        startsWith?: boolean,
    ) => boolean;
    isCurrentOrParentUrl: (urlToCheck: Href, currentUrl?: string) => boolean;
    whenCurrentUrl: <T, F = null>(
        urlToCheck: Href,
        ifTrue: T,
        ifFalse?: F,
    ) => T | F;
};

export function useCurrentUrl(): UseCurrentUrlReturn {
    const route = useRoute();
    const currentUrlReactive = computed(() => route.path);

    function isCurrentUrl(
        urlToCheck: Href,
        currentUrl?: string,
        startsWith: boolean = false,
    ) {
        const urlToCompare = currentUrl ?? currentUrlReactive.value;
        const urlString = toUrl(urlToCheck);

        const comparePath = (path: string): boolean =>
            startsWith ? urlToCompare.startsWith(path) : path === urlToCompare;

        if (!urlString.startsWith('http')) {
            return comparePath(urlString);
        }

        try {
            const absoluteUrl = new URL(urlString);

            return comparePath(absoluteUrl.pathname);
        } catch {
            return false;
        }
    }

    function isCurrentOrParentUrl(urlToCheck: Href, currentUrl?: string) {
        return isCurrentUrl(urlToCheck, currentUrl, true);
    }

    function whenCurrentUrl<T, F = null>(
        urlToCheck: Href,
        ifTrue: T,
        ifFalse: F = null as F,
    ): T | F {
        return isCurrentUrl(urlToCheck) ? ifTrue : ifFalse;
    }

    return {
        currentUrl: readonly(currentUrlReactive),
        isCurrentUrl,
        isCurrentOrParentUrl,
        whenCurrentUrl,
    };
}
