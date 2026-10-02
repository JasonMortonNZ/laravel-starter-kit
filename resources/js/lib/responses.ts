import type { AuthResponse } from '@/types';

export const isRecord = (value: unknown): value is Record<string, unknown> =>
    typeof value === 'object' && value !== null && !Array.isArray(value);

/**
 * Read the `team.slug` from a JSON response, if the server sent one.
 */
export function readTeamSlug(data: unknown): string | null {
    return isRecord(data) &&
        isRecord(data.team) &&
        typeof data.team.slug === 'string'
        ? data.team.slug
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
    };
}
