/**
 * Convert a redirect target returned by the server into a path the client
 * router can navigate to. Absolute URLs on another origin are ignored.
 */
export function toClientPath(target: string): string {
    if (!/^https?:\/\//.test(target)) {
        return target;
    }

    try {
        const url = new URL(target);

        if (url.origin !== window.location.origin) {
            return '/';
        }

        return `${url.pathname}${url.search}${url.hash}`;
    } catch {
        return '/';
    }
}
