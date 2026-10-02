<script setup lang="ts">
import { onUnmounted } from 'vue';
import AppForm from '@/components/AppForm.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TeamInvitationAlert from '@/components/TeamInvitationAlert.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useAuthentication } from '@/composables/useAuthentication';
import { useTeamInvitation } from '@/composables/useTeamInvitation';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { useAuthStore } from '@/stores/auth';
import { useFlashStore } from '@/stores/flash';

const auth = useAuthStore();
const flash = useFlashStore();
const { completeAuthentication } = useAuthentication();
const { teamInvitation } = useTeamInvitation();

const status = flash.status;
const canResetPassword = auth.features.canResetPassword;

onUnmounted(() => flash.consume());

const handleSuccess = (data: unknown) => completeAuthentication(data);
</script>

<template>
    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <TeamInvitationAlert
        v-if="teamInvitation"
        :invitation="teamInvitation"
        action="Log in"
    />

    <AppForm
        :route="store()"
        :reset-on-success="['password']"
        :follow-redirect="false"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
        @success="handleSuccess"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    v-focus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Password</Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-sm"
                        :tabindex="5"
                    >
                        Forgot password?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Password"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Remember me</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Log in
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Don't have an account?
            <TextLink
                :href="
                    register({
                        query: {
                            invitation: teamInvitation?.code,
                        },
                    })
                "
                :tabindex="5"
                data-test="register-link"
            >
                Sign up
            </TextLink>
        </div>
    </AppForm>
</template>
