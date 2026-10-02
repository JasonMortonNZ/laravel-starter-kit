import { createRouter, createWebHistory } from 'vue-router';
import type { RouteRecordRaw } from 'vue-router';
import { registerGuards } from '@/router/guards';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import { index as teamsIndex } from '@/routes/teams';

/**
 * Route names match the Laravel route names so Wayfinder URLs, server-side
 * redirects, and client-side navigation all agree.
 */
const routes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'home',
        component: () => import('@/pages/Welcome.vue'),
        meta: { title: 'Welcome' },
    },
    {
        path: '/',
        component: () => import('@/layouts/AuthLayout.vue'),
        children: [
            {
                path: 'login',
                name: 'login',
                component: () => import('@/pages/auth/Login.vue'),
                meta: {
                    guest: true,
                    title: 'Log in',
                    layout: {
                        title: 'Log in to your account',
                        description:
                            'Enter your email and password below to log in',
                    },
                },
            },
            {
                path: 'register',
                name: 'register',
                component: () => import('@/pages/auth/Register.vue'),
                meta: {
                    guest: true,
                    title: 'Register',
                    layout: {
                        title: 'Create an account',
                        description:
                            'Enter your details below to create your account',
                    },
                },
            },
            {
                path: 'forgot-password',
                name: 'password.request',
                component: () => import('@/pages/auth/ForgotPassword.vue'),
                meta: {
                    guest: true,
                    title: 'Forgot password',
                    layout: {
                        title: 'Forgot password',
                        description:
                            'Enter your email to receive a password reset link',
                    },
                },
            },
            {
                path: 'reset-password/:token',
                name: 'password.reset',
                component: () => import('@/pages/auth/ResetPassword.vue'),
                props: (route) => ({
                    token: route.params.token,
                    email:
                        typeof route.query.email === 'string'
                            ? route.query.email
                            : '',
                }),
                meta: {
                    guest: true,
                    title: 'Reset password',
                    layout: {
                        title: 'Reset password',
                        description: 'Please enter your new password below',
                    },
                },
            },
            {
                path: 'two-factor-challenge',
                name: 'two-factor.login',
                component: () => import('@/pages/auth/TwoFactorChallenge.vue'),
                meta: {
                    guest: true,
                    twoFactor: true,
                    title: 'Two-factor authentication',
                },
            },
            {
                path: 'email/verify',
                name: 'verification.notice',
                component: () => import('@/pages/auth/VerifyEmail.vue'),
                meta: {
                    auth: true,
                    title: 'Email verification',
                    layout: {
                        title: 'Email verification',
                        description:
                            'Please verify your email address by clicking on the link we just emailed to you.',
                    },
                },
            },
            {
                path: 'user/confirm-password',
                name: 'password.confirm',
                component: () => import('@/pages/auth/ConfirmPassword.vue'),
                meta: {
                    auth: true,
                    title: 'Confirm password',
                    layout: {
                        title: 'Confirm password',
                        description:
                            'This is a secure area of the application. Please confirm your password before continuing.',
                    },
                },
            },
        ],
    },
    {
        path: '/',
        component: () => import('@/layouts/AppLayout.vue'),
        meta: { auth: true },
        children: [
            {
                path: ':team/dashboard',
                name: 'dashboard',
                component: () => import('@/pages/Dashboard.vue'),
                meta: { verified: true, team: true, title: 'Dashboard' },
            },
            {
                path: 'settings',
                component: () => import('@/layouts/settings/Layout.vue'),
                children: [
                    {
                        path: '',
                        redirect: { name: 'profile.edit' },
                    },
                    {
                        path: 'profile',
                        name: 'profile.edit',
                        component: () => import('@/pages/settings/Profile.vue'),
                        meta: {
                            title: 'Profile settings',
                            layout: {
                                breadcrumbs: [
                                    {
                                        title: 'Profile settings',
                                        href: editProfile(),
                                    },
                                ],
                            },
                        },
                    },
                    {
                        path: 'security',
                        name: 'security.edit',
                        component: () =>
                            import('@/pages/settings/Security.vue'),
                        meta: {
                            verified: true,
                            title: 'Security settings',
                            layout: {
                                breadcrumbs: [
                                    {
                                        title: 'Security settings',
                                        href: editSecurity(),
                                    },
                                ],
                            },
                        },
                    },
                    {
                        path: 'appearance',
                        name: 'appearance.edit',
                        component: () =>
                            import('@/pages/settings/Appearance.vue'),
                        meta: {
                            verified: true,
                            title: 'Appearance settings',
                            layout: {
                                breadcrumbs: [
                                    {
                                        title: 'Appearance settings',
                                        href: editAppearance(),
                                    },
                                ],
                            },
                        },
                    },
                    {
                        path: 'teams',
                        name: 'teams.index',
                        component: () => import('@/pages/teams/Index.vue'),
                        meta: {
                            verified: true,
                            title: 'Teams',
                            layout: {
                                breadcrumbs: [
                                    { title: 'Teams', href: teamsIndex() },
                                ],
                            },
                        },
                    },
                    {
                        path: 'teams/:team',
                        name: 'teams.edit',
                        component: () => import('@/pages/teams/Edit.vue'),
                        meta: { verified: true },
                    },
                ],
            },
        ],
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/pages/NotFound.vue'),
        meta: { title: 'Not found' },
    },
];

export const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: (to, from, savedPosition) =>
        savedPosition ?? (to.path === from.path ? undefined : { top: 0 }),
});

registerGuards(router);
