import type { LucideIcon } from '@lucide/vue';

export type Href = string | { url: string };

export type BreadcrumbItem = {
    title: string;
    href: Href;
};

export type NavItem = {
    title: string;
    href: Href;
    icon?: LucideIcon;
    isActive?: boolean;
};

export type LayoutProps = {
    title: string;
    description: string;
    breadcrumbs: BreadcrumbItem[];
};
