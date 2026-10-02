<script setup lang="ts">
import { ref } from 'vue';
import AppForm from '@/components/AppForm.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { useAuthentication } from '@/composables/useAuthentication';
import { send } from '@/routes/verification';

const { logout } = useAuthentication();
const linkSent = ref(false);
</script>

<template>
    <div
        v-if="linkSent"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        A new verification link has been sent to the email address you provided
        during registration.
    </div>

    <AppForm
        :route="send()"
        class="space-y-6 text-center"
        v-slot="{ processing }"
        @success="linkSent = true"
    >
        <Button :disabled="processing" variant="secondary">
            <Spinner v-if="processing" />
            Resend verification email
        </Button>

        <button
            type="button"
            class="mx-auto block text-sm text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
            data-test="logout-button"
            @click="logout"
        >
            Log out
        </button>
    </AppForm>
</template>
