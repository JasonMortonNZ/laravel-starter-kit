export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
};

export type Features = {
    can_register: boolean;
    can_reset_password: boolean;
    can_manage_two_factor: boolean;
    requires_two_factor_confirmation: boolean;
    must_verify_email: boolean;
};

export type AuthResponse = {
    two_factor: boolean;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
