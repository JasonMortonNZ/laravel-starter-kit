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
import { leave as leaveTeamAction } from '@/routes/teams';
import type { Team } from '@/types';

type Props = {
    team: Team | null;
    open: boolean;
};

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const processing = ref(false);

const leaveTeam = async () => {
    if (!props.team) {
        return;
    }

    processing.value = true;

    try {
        await request(leaveTeamAction(props.team.slug));

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
                <DialogTitle>Leave team</DialogTitle>
                <DialogDescription>
                    Are you sure you want to leave
                    <strong>{{ props.team?.name }}</strong
                    >?
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button variant="secondary"> Cancel </Button>
                </DialogClose>

                <Button
                    data-test="leave-team-confirm"
                    variant="destructive"
                    :disabled="processing"
                    @click="leaveTeam"
                >
                    Leave team
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
