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
import { destroy as destroyMember } from '@/routes/teams/members';
import type { Team, TeamMember } from '@/types';

type Props = {
    team: Team;
    member: TeamMember | null;
    open: boolean;
};

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const processing = ref(false);

const removeMember = async () => {
    if (!props.member) {
        return;
    }

    processing.value = true;

    try {
        await request(destroyMember([props.team.slug, props.member.id]));

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
                <DialogTitle>Remove team member</DialogTitle>
                <DialogDescription>
                    Are you sure you want to remove
                    <strong>{{ props.member?.name }}</strong> from this team?
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button variant="secondary"> Cancel </Button>
                </DialogClose>

                <Button
                    data-test="remove-member-confirm"
                    variant="destructive"
                    :disabled="processing"
                    @click="removeMember"
                >
                    Remove member
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
