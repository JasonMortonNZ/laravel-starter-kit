import axios from 'axios';
import type { AxiosRequestConfig, AxiosResponse } from 'axios';

export type HttpMethod =
    | 'get'
    | 'post'
    | 'put'
    | 'patch'
    | 'delete'
    | 'head'
    | 'options';

/**
 * The shape shared by every Wayfinder route definition.
 */
export type HttpRoute = {
    url: string;
    method: HttpMethod;
};

/**
 * Same-origin JSON client. The session cookie authenticates requests and the
 * XSRF-TOKEN cookie is echoed back as a header for CSRF protection.
 */
export const http = axios.create({
    baseURL: '/',
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

/**
 * Send a request to a Wayfinder route definition.
 */
export function request<T = unknown>(
    route: HttpRoute,
    data?: unknown,
    config: AxiosRequestConfig = {},
): Promise<AxiosResponse<T>> {
    return http.request<T>({
        url: route.url,
        method: route.method,
        data,
        ...config,
    });
}
