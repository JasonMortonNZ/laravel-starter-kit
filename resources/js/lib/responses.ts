import type { AuthResponse } from '@/types';

export const isRecord = (value: unknown): value is Record<string, unknown> =>
    typeof value === 'object' && value !== null && !Array.isArray(value);

/**
 * Read the `redirect` path from a JSON response, if the server sent one.
 */
export function readRedirect(data: unknown): string | null {
    return isRecord(data) && typeof data.redirect === 'string'
        ? data.redirect
        : null;
}

/**
 * Read the `message` from a JSON response, if the server sent one.
 */
export function readMessage(data: unknown): string | null {
    return isRecord(data) && typeof data.message === 'string'
        ? data.message
        : null;
}

/**
 * Normalise the JSON Fortify returns from login, registration and the
 * two-factor challenge.
 */
export function toAuthResponse(data: unknown): AuthResponse {
    const record = isRecord(data) ? data : {};

    return {
        two_factor: record.two_factor === true,
        redirect:
            typeof record.redirect === 'string' ? record.redirect : undefined,
    };
}
