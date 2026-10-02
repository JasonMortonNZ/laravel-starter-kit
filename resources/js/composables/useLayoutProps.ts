import { reactive } from 'vue';
import type { LayoutProps } from '@/types';

const state = reactive<LayoutProps>({
    title: '',
    description: '',
    breadcrumbs: [],
});

/**
 * Merge values into the props consumed by the active layout.
 */
export function setLayoutProps(props: Partial<LayoutProps>): void {
    Object.assign(state, props);
}

/**
 * Replace the layout props, typically when a new route becomes active.
 */
export function resetLayoutProps(props: Partial<LayoutProps> = {}): void {
    state.title = '';
    state.description = '';
    state.breadcrumbs = [];

    Object.assign(state, props);
}

export function useLayoutProps(): LayoutProps {
    return state;
}
