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
    currentTeam: Team | null;
    teams: Team[];
    features: Features;
    passwordRules: string;
};

export type ToastResponse = {
    toast?: FlashToast;
    redirect?: string;
};

export type MessageResponse = {
    message: string;
};

export type DashboardData = {
    currentTeam: Team | null;
    pendingInvitations: DashboardInvitation[];
};

export type SecuritySettings = {
    canManageTwoFactor: boolean;
    passwordRules: string;
    twoFactorEnabled?: boolean;
    requiresConfirmation?: boolean;
};

export type TeamsIndexData = {
    teams: Team[];
};

export type TeamShowData = {
    team: Team;
    members: TeamMember[];
    invitations: TeamInvitation[];
    permissions: TeamPermissions;
    availableRoles: RoleOption[];
};
