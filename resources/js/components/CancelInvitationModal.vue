<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { request } from '@/lib/http';
import { destroy as destroyInvitation } from '@/routes/teams/invitations';
import type { Team, TeamInvitation } from '@/types';

type Props = {
    team: Team;
    invitation: TeamInvitation | null;
    open: boolean;
};

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const processing = ref(false);

const cancelInvitation = async () => {
    if (!props.invitation) {
        return;
    }

    processing.value = true;

    try {
        await request(
            destroyInvitation([props.team.slug, props.invitation.code]),
        );

        emit('update:open', false);
        emit('success');
    } catch {
        // Errors are surfaced by the HTTP interceptors.
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Cancel invitation</DialogTitle>
                <DialogDescription>
                    Are you sure you want to cancel the invitation for
                    <strong>{{ props.invitation?.email }}</strong
                    >?
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button variant="secondary"> Keep invitation </Button>
                </DialogClose>

                <Button
                    data-test="cancel-invitation-confirm"
                    variant="destructive"
                    :disabled="processing"
                    @click="cancelInvitation"
                >
                    Cancel invitation
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
