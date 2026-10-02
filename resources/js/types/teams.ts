export type TeamRole = 'owner' | 'admin' | 'member';

export type Team = {
    id: number;
    name: string;
    slug: string;
    is_personal: boolean;
    role?: TeamRole;
    role_label?: string;
    is_current?: boolean;
};

export type TeamMember = {
    id: number;
    name: string;
    email: string;
    avatar?: string | null;
    role: TeamRole;
    role_label: string;
};

export type TeamInvitation = {
    code: string;
    email: string;
    role: TeamRole;
    role_label: string;
    created_at: string;
};

export type TeamInvitationContext = {
    code: string;
    team_name: string;
};

export type DashboardInvitation = {
    code: string;
    inviter_name: string;
    team: {
        name: string;
        slug: string;
    };
};

export type TeamPermissions = {
    can_update_team: boolean;
    can_delete_team: boolean;
    can_add_member: boolean;
    can_update_member: boolean;
    can_remove_member: boolean;
    can_create_invitation: boolean;
    can_cancel_invitation: boolean;
};

export type RoleOption = {
    value: TeamRole;
    label: string;
};
