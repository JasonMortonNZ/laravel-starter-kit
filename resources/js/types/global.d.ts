import type { Directive } from 'vue';
import type { LayoutProps } from '@/types/navigation';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module 'vue-router' {
    interface RouteMeta {
        /** The route is only available to authenticated users. */
        auth?: boolean;
        /** The route is only available to guests. */
        guest?: boolean;
        /** The route requires a verified email address. */
        verified?: boolean;
        /** The route is only reachable while a two-factor login is pending. */
        twoFactor?: boolean;
        /** The `team` param must be one of the user's teams. */
        team?: boolean;
        /** The document title for the route. */
        title?: string;
        /** Default layout props (title, description, breadcrumbs) for the route. */
        layout?: Partial<LayoutProps>;
    }
}

declare module 'axios' {
    export interface AxiosRequestConfig {
        /** Suppress the generic error toast for this request. */
        silent?: boolean;
    }
}

declare module 'vue' {
    interface GlobalDirectives {
        vFocus: Directive<HTMLElement, boolean | undefined>;
    }
}
