import { onMounted, ref, shallowRef, watch } from 'vue';
import type { Ref, ShallowRef, WatchSource } from 'vue';

export type UsePageDataOptions = {
    /** Reload when any of these sources change. */
    watch?: WatchSource | WatchSource[];
    /** Only load while this returns true (e.g. the route is still active). */
    when?: () => boolean;
};

export type UsePageDataReturn<T> = {
    data: ShallowRef<T | null>;
    loading: Ref<boolean>;
    error: Ref<unknown>;
    reload: () => Promise<void>;
};

/**
 * Load a page's data from the API on mount and whenever the watched sources
 * change, replacing the props Inertia used to pass from the controller.
 */
export function usePageData<T>(
    loader: () => Promise<T>,
    options: UsePageDataOptions = {},
): UsePageDataReturn<T> {
    const data = shallowRef<T | null>(null);
    const loading = ref(true);
    const error = ref<unknown>(null);

    let version = 0;

    async function reload(): Promise<void> {
        if (options.when && !options.when()) {
            return;
        }

        const current = ++version;

        loading.value = data.value === null;

        try {
            const result = await loader();

            if (current === version) {
                data.value = result;
                error.value = null;
            }
        } catch (caught) {
            if (current === version) {
                error.value = caught;
            }
        } finally {
            if (current === version) {
                loading.value = false;
            }
        }
    }

    onMounted(reload);

    if (options.watch) {
        watch(options.watch, () => {
            data.value = null;

            void reload();
        });
    }

    return { data, loading, error, reload };
}
