<script setup lang="ts">
import { ChevronDown, Mail, UserPlus, X } from '@lucide/vue';
import { computed, ref, watchEffect } from 'vue';
import { useRoute } from 'vue-router';
import AppForm from '@/components/AppForm.vue';
import CancelInvitationModal from '@/components/CancelInvitationModal.vue';
import DeleteTeamModal from '@/components/DeleteTeamModal.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import InviteMemberModal from '@/components/InviteMemberModal.vue';
import RemoveMemberModal from '@/components/RemoveMemberModal.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useInitials } from '@/composables/useInitials';
import { setLayoutProps } from '@/composables/useLayoutProps';
import { usePageData } from '@/composables/usePageData';
import { usePageTitle } from '@/composables/usePageTitle';
import { http, request } from '@/lib/http';
import { show as teamData } from '@/routes/api/teams';
import { edit, index, update } from '@/routes/teams';
import { update as updateMember } from '@/routes/teams/members';
import type { TeamInvitation, TeamMember, TeamShowData } from '@/types';

const route = useRoute();

const teamSlug = computed(() =>
    typeof route.params.team === 'string' ? route.params.team : null,
);

const { data, reload } = usePageData(
    async () =>
        (await http.get<TeamShowData>(teamData(String(teamSlug.value)).url))
            .data,
    {
        watch: teamSlug,
        when: () => teamSlug.value !== null,
    },
);

const { getInitials } = useInitials();

const inviteDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const removeMemberDialogOpen = ref(false);
const memberToRemove = ref<TeamMember | null>(null);
const cancelInvitationDialogOpen = ref(false);
const invitationToCancel = ref<TeamInvitation | null>(null);

const pageTitle = computed(() => {
    if (!data.value) {
        return 'Team';
    }

    return data.value.permissions.canUpdateTeam
        ? `Edit ${data.value.team.name}`
        : `View ${data.value.team.name}`;
});

usePageTitle(pageTitle);

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            { title: 'Teams', href: index() },
            ...(data.value
                ? [
                      {
                          title: data.value.team.name,
                          href: edit(data.value.team.slug),
                      },
                  ]
                : []),
        ],
    });
});

const updateMemberRole = async (member: TeamMember, newRole: string) => {
    if (!data.value) {
        return;
    }

    try {
        await request(updateMember([data.value.team.slug, member.id]), {
            role: newRole,
        });

        await reload();
    } catch {
        // Errors are surfaced by the HTTP interceptors.
    }
};

const confirmRemoveMember = (member: TeamMember) => {
    memberToRemove.value = member;
    removeMemberDialogOpen.value = true;
};

const confirmCancelInvitation = (invitation: TeamInvitation) => {
    invitationToCancel.value = invitation;
    cancelInvitationDialogOpen.value = true;
};
</script>

<template>
    <h1 class="sr-only">{{ pageTitle }}</h1>

    <div v-if="!data" class="space-y-6">
        <Skeleton class="h-6 w-48" />
        <Skeleton class="h-9 w-full" />
        <Skeleton class="h-[74px] w-full" />
    </div>

    <template v-else>
        <div class="flex flex-col space-y-10">
            <!-- Team Name Section -->
            <div v-if="data.permissions.canUpdateTeam" class="space-y-6">
                <Heading
                    variant="small"
                    title="Team settings"
                    description="Update your team name and settings"
                />

                <AppForm
                    :route="update(data.team.slug)"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                    @success="reload"
                >
                    <div class="grid gap-2">
                        <Label for="name">Team name</Label>
                        <Input
                            id="name"
                            name="name"
                            data-test="team-name-input"
                            :default-value="data.team.name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            type="submit"
                            data-test="team-save-button"
                            :disabled="processing"
                        >
                            Save
                        </Button>
                    </div>
                </AppForm>
            </div>

            <div v-else class="space-y-6">
                <Heading variant="small" :title="data.team.name" />
            </div>

            <!-- Members Section -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Team members"
                        :description="
                            data.permissions.canCreateInvitation
                                ? 'Manage who belongs to this team'
                                : ''
                        "
                    />

                    <Button
                        v-if="data.permissions.canCreateInvitation"
                        data-test="invite-member-button"
                        @click="inviteDialogOpen = true"
                    >
                        <UserPlus /> Invite member
                    </Button>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="member in data.members"
                        :key="member.id"
                        data-test="member-row"
                        class="flex items-center justify-between rounded-lg border p-4"
                    >
                        <div class="flex items-center gap-4">
                            <Avatar class="h-10 w-10">
                                <AvatarImage
                                    v-if="member.avatar"
                                    :src="member.avatar"
                                    :alt="member.name"
                                />
                                <AvatarFallback>{{
                                    getInitials(member.name)
                                }}</AvatarFallback>
                            </Avatar>
                            <div>
                                <div class="font-medium">
                                    {{ member.name }}
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    {{ member.email }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <DropdownMenu
                                v-if="
                                    member.role !== 'owner' &&
                                    data.permissions.canUpdateMember
                                "
                            >
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        data-test="member-role-trigger"
                                        variant="outline"
                                        size="sm"
                                    >
                                        {{ member.role_label }}
                                        <ChevronDown
                                            class="ml-2 h-4 w-4 opacity-50"
                                        />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent>
                                    <DropdownMenuItem
                                        v-for="role in data.availableRoles"
                                        :key="role.value"
                                        data-test="member-role-option"
                                        @click="
                                            updateMemberRole(member, role.value)
                                        "
                                    >
                                        {{ role.label }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <Badge v-else variant="secondary">
                                {{ member.role_label }}
                            </Badge>

                            <TooltipProvider
                                v-if="
                                    member.role !== 'owner' &&
                                    data.permissions.canRemoveMember
                                "
                            >
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button
                                            data-test="member-remove-button"
                                            variant="ghost"
                                            size="sm"
                                            @click="confirmRemoveMember(member)"
                                        >
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Remove member</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Invitations Section -->
            <div v-if="data.invitations.length > 0" class="space-y-6">
                <Heading
                    variant="small"
                    title="Pending invitations"
                    description="Invitations that haven't been accepted yet"
                />

                <div class="space-y-3">
                    <div
                        v-for="invitation in data.invitations"
                        :key="invitation.code"
                        data-test="invitation-row"
                        class="flex items-center justify-between rounded-lg border p-4"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-muted"
                            >
                                <Mail class="h-5 w-5 text-muted-foreground" />
                            </div>
                            <div>
                                <div class="font-medium">
                                    {{ invitation.email }}
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    {{ invitation.role_label }}
                                </div>
                            </div>
                        </div>

                        <TooltipProvider
                            v-if="data.permissions.canCancelInvitation"
                        >
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button
                                        data-test="invitation-cancel-button"
                                        variant="ghost"
                                        size="sm"
                                        @click="
                                            confirmCancelInvitation(invitation)
                                        "
                                    >
                                        <X class="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>Cancel invitation</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div
                v-if="data.permissions.canDeleteTeam && !data.team.isPersonal"
                class="space-y-6"
            >
                <Heading
                    variant="small"
                    title="Delete team"
                    description="Permanently delete your team"
                />
                <div
                    class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10"
                >
                    <div
                        class="relative space-y-0.5 text-red-600 dark:text-red-100"
                    >
                        <p class="font-medium">Warning</p>
                        <p class="text-sm">
                            Please proceed with caution, this cannot be undone.
                        </p>
                    </div>
                    <Button
                        data-test="delete-team-button"
                        variant="destructive"
                        @click="deleteDialogOpen = true"
                        >Delete team</Button
                    >
                </div>
            </div>
        </div>

        <InviteMemberModal
            v-if="data.permissions.canCreateInvitation"
            :team="data.team"
            :available-roles="data.availableRoles"
            :open="inviteDialogOpen"
            @update:open="inviteDialogOpen = $event"
            @success="reload"
        />

        <RemoveMemberModal
            :team="data.team"
            :member="memberToRemove"
            :open="removeMemberDialogOpen"
            @update:open="removeMemberDialogOpen = $event"
            @success="reload"
        />

        <CancelInvitationModal
            :team="data.team"
            :invitation="invitationToCancel"
            :open="cancelInvitationDialogOpen"
            @update:open="cancelInvitationDialogOpen = $event"
            @success="reload"
        />

        <DeleteTeamModal
            v-if="data.permissions.canDeleteTeam && !data.team.isPersonal"
            :team="data.team"
            :open="deleteDialogOpen"
            @update:open="deleteDialogOpen = $event"
        />
    </template>
</template>
