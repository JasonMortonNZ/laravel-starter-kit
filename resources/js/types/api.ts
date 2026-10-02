import type { Auth, Features } from './auth';
import type {
    DashboardInvitation,
    RoleOption,
    Team,
    TeamInvitation,
    TeamMember,
    TeamPermissions,
} from './teams';
import type { FlashToast } from './ui';

export type BootstrapData = {
    name: string;
    auth: Auth;
    current_team: Team | null;
    teams: Team[];
    features: Features;
    password_rules: string;
};

export type ToastResponse = {
    toast?: FlashToast;
};

export type MessageResponse = {
    message: string;
};

export type DashboardData = {
    current_team: Team | null;
    pending_invitations: DashboardInvitation[];
};

export type SecuritySettings = {
    can_manage_two_factor: boolean;
    password_rules: string;
    two_factor_enabled?: boolean;
    requires_confirmation?: boolean;
};

export type TeamsIndexData = {
    teams: Team[];
};

export type TeamShowData = {
    team: Team;
    members: TeamMember[];
    invitations: TeamInvitation[];
    permissions: TeamPermissions;
    available_roles: RoleOption[];
};
