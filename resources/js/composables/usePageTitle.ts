import { toValue, watchEffect } from 'vue';
import type { MaybeRefOrGetter } from 'vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

export function setPageTitle(title?: string | null): void {
    if (typeof document === 'undefined') {
        return;
    }

    document.title = title ? `${title} - ${appName}` : appName;
}

/**
 * Keep the document title in sync with a reactive page title.
 */
export function usePageTitle(
    title: MaybeRefOrGetter<string | null | undefined>,
): void {
    watchEffect(() => setPageTitle(toValue(title)));
}
