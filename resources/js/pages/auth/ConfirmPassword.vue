<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router';
import AppForm from '@/components/AppForm.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/password/confirm';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();

const handleSuccess = async () => {
    const intended =
        typeof route.query.redirect === 'string' ? route.query.redirect : null;

    await router.push(intended ?? auth.dashboardRoute);
};
</script>

<template>
    <AppForm
        :route="store()"
        reset-on-success
        v-slot="{ errors, processing }"
        @success="handleSuccess"
    >
        <div class="space-y-6">
            <div class="grid gap-2">
                <Label htmlFor="password">Password</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                    autofocus
                />

                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center">
                <Button
                    class="w-full"
                    :disabled="processing"
                    data-test="confirm-password-button"
                >
                    <Spinner v-if="processing" />
                    Confirm password
                </Button>
            </div>
        </div>
    </AppForm>
</template>
