import { Permission, Role, Team } from '@/types/models';
import { LucideIcon } from 'lucide-react';

export type GatekeeperSharedData = {
    config: GatekeeperConfig;
    user: GatekeeperUser;
};

export type GatekeeperConfig = {
    path: string;
    audit_enabled: boolean;
    roles_enabled: boolean;
    teams_enabled: boolean;
};

export type GatekeeperUser = {
    name?: string;
    email?: string;
    permissions: {
        can_view: boolean;
        can_manage: boolean;
    };
};

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export type GatekeeperEntity = 'permission' | 'role' | 'team';

export type GatekeeperEntityAssignmentMap = {
    permission: PermissionAssignment;
    role: RoleAssignment;
    team: TeamAssignment;
};

export type GatekeeperEntityModelMap = {
    permission: Permission;
    role: Role;
    team: Team;
};

export type ModelManagementTab = 'overview' | 'permissions' | 'roles' | 'teams';
