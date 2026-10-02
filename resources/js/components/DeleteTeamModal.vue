<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppForm from '@/components/AppForm.vue';
import InputError from '@/components/InputError.vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { destroy } from '@/routes/teams';
import { useAuthStore } from '@/stores/auth';
import type { Team } from '@/types';

type Props = {
    team: Team;
    open: boolean;
};

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const auth = useAuthStore();
const router = useRouter();
const confirmationName = ref('');
const formKey = ref(0);

const canDeleteTeam = computed(() => {
    return confirmationName.value === props.team.name;
});

const handleOpenChange = (nextOpen: boolean) => {
    emit('update:open', nextOpen);

    if (!nextOpen) {
        confirmationName.value = '';
        formKey.value++;
    }
};

const handleSuccess = async () => {
    handleOpenChange(false);

    await auth.fetch();

    await router.push({ name: 'teams.index' });
};
</script>

<template>
    <Dialog :open="props.open" @update:open="handleOpenChange">
        <DialogContent>
            <AppForm
                :key="formKey"
                :route="destroy(props.team.slug)"
                class="space-y-6"
                v-slot="{ errors, processing }"
                @success="handleSuccess"
            >
                <DialogHeader>
                    <DialogTitle>Are you sure?</DialogTitle>
                    <DialogDescription>
                        This action cannot be undone. This will permanently
                        delete the team
                        <strong>"{{ props.team.name }}"</strong>.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-4">
                    <div class="grid gap-2">
                        <Label for="confirmation-name">
                            Type
                            <strong>"{{ props.team.name }}"</strong> to confirm
                        </Label>
                        <Input
                            id="confirmation-name"
                            name="name"
                            data-test="delete-team-name"
                            v-model="confirmationName"
                            placeholder="Enter team name"
                            autocomplete="off"
                        />
                        <InputError :message="errors.name" />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary"> Cancel </Button>
                    </DialogClose>

                    <Button
                        data-test="delete-team-confirm"
                        variant="destructive"
                        type="submit"
                        :disabled="!canDeleteTeam || processing"
                    >
                        Delete team
                    </Button>
                </DialogFooter>
            </AppForm>
        </DialogContent>
    </Dialog>
</template>
