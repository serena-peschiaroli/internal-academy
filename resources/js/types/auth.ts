export type Role = {
    id: number;
    name: string;
    key: 'admin' | 'employee';
};

export type User = {
    id: number;
    name: string;
    email: string;
    phone?: string | null;
    socials?: {
        reddit?: string | null;
        linkedin?: string | null;
        facebook?: string | null;
        instagram?: string | null;
        website?: string | null;
    } | null;
    avatar?: string | null;
    role_id: number;
    role: Role | null;
    first_access: boolean;
    is_active: boolean;
    temporary_password_expires_at: string | null;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
