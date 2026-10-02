<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import TeamInvitationController from '@/actions/App/Http/Controllers/Teams/TeamInvitationController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { request } from '@/lib/http';
import { useAuthStore } from '@/stores/auth';
import type { DashboardInvitation } from '@/types';

type Props = {
    invitations: DashboardInvitation[];
};

const props = defineProps<Props>();
const emit = defineEmits<{
    changed: [];
}>();

const auth = useAuthStore();
const router = useRouter();

const open = ref(true);
const processingCode = ref<string | null>(null);

const acceptInvitation = async (invitation: DashboardInvitation) => {
    processingCode.value = invitation.code;

    try {
        await request(TeamInvitationController.accept(invitation));

        await auth.fetch();

        open.value = false;

        await router.push(auth.dashboardRoute);

        emit('changed');
    } catch {
        // Errors are surfaced by the HTTP interceptors.
    } finally {
        processingCode.value = null;
    }
};

const declineInvitation = async (invitation: DashboardInvitation) => {
    processingCode.value = invitation.code;

    try {
        await request(TeamInvitationController.decline(invitation));

        if (props.invitations.length === 1) {
            open.value = false;
        }

        emit('changed');
    } catch {
        // Errors are surfaced by the HTTP interceptors.
    } finally {
        processingCode.value = null;
    }
};
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent data-test="pending-invitations-modal">
            <DialogHeader>
                <DialogTitle>Pending team invitations</DialogTitle>
                <DialogDescription>
                    Accept or decline the teams you have been invited to join.
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-4">
                <div
                    v-for="invitation in props.invitations"
                    :key="invitation.code"
                    data-test="pending-invitation-row"
                    class="rounded-lg border p-4"
                >
                    <div class="space-y-1">
                        <p class="font-medium">{{ invitation.team.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ invitation.inviter_name }} invited you to join
                            this team.
                        </p>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <Button
                            variant="secondary"
                            data-test="pending-invitation-decline"
                            :disabled="processingCode === invitation.code"
                            @click="declineInvitation(invitation)"
                        >
                            Decline
                        </Button>

                        <Button
                            data-test="pending-invitation-accept"
                            :disabled="processingCode === invitation.code"
                            @click="acceptInvitation(invitation)"
                        >
                            Accept
                        </Button>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
