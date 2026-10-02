<script setup lang="ts">
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import AppForm from '@/components/AppForm.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Skeleton } from '@/components/ui/skeleton';
import ManageTwoFactor from '@/components/ManageTwoFactor.vue';
import { usePageData } from '@/composables/usePageData';
import { http } from '@/lib/http';
import { security } from '@/routes/api/settings';
import type { SecuritySettings } from '@/types';

const { data: settings, reload } = usePageData(
    async () => (await http.get<SecuritySettings>(security().url)).data,
);
</script>

<template>
    <h1 class="sr-only">Security settings</h1>

    <div v-if="!settings" class="space-y-6">
        <Skeleton class="h-6 w-40" />
        <Skeleton class="h-9 w-full" />
        <Skeleton class="h-9 w-full" />
        <Skeleton class="h-9 w-full" />
    </div>

    <template v-else>
        <div class="space-y-6">
            <Heading
                variant="small"
                title="Update password"
                description="Ensure your account is using a long, random password to stay secure"
            />

            <AppForm
                :route="SecurityController.update()"
                reset-on-success
                :reset-on-error="[
                    'password',
                    'password_confirmation',
                    'current_password',
                ]"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="current_password">Current password</Label>
                    <PasswordInput
                        id="current_password"
                        name="current_password"
                        class="mt-1 block w-full"
                        autocomplete="current-password"
                        placeholder="Current password"
                    />
                    <InputError :message="errors.current_password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">New password</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        class="mt-1 block w-full"
                        autocomplete="new-password"
                        placeholder="New password"
                        :passwordrules="settings.passwordRules"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        class="mt-1 block w-full"
                        autocomplete="new-password"
                        placeholder="Confirm password"
                        :passwordrules="settings.passwordRules"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <div class="flex items-center gap-4">
                    <Button
                        :disabled="processing"
                        data-test="update-password-button"
                    >
                        Save
                    </Button>
                </div>
            </AppForm>
        </div>

        <ManageTwoFactor
            :canManageTwoFactor="settings.canManageTwoFactor"
            :requiresConfirmation="settings.requiresConfirmation"
            :twoFactorEnabled="settings.twoFactorEnabled"
            @changed="reload"
        />
    </template>
</template>
