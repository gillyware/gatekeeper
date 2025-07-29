import { ConfiguredModelMetadata } from '@/types/api/model';
import {
    type Feature,
    type ModelFeatureAssignment,
    type ModelFeatureDenial,
    type ModelPermissionDenial,
    type ModelRoleDenial,
    type ModelTeamDenial,
    type Permission,
    type Role,
    type Team,
} from '@/types/models';
import { LucideIcon } from 'lucide-react';

export type GatekeeperSharedData = {
    config: GatekeeperConfig;
    user: GatekeeperUser;
};

export type GatekeeperConfig = {
    path: string;
    audit_enabled: boolean;
    roles_enabled: boolean;
    features_enabled: boolean;
    teams_enabled: boolean;
    models: ConfiguredModelMetadata[];
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

export type GatekeeperPermission = 'permission';

export type GatekeeperRole = 'role';

export type GatekeeperFeature = 'feature';

export type GatekeeperTeam = 'team';

export type GatekeeperEntity = GatekeeperPermission | GatekeeperRole | GatekeeperFeature | GatekeeperTeam;

export type GatekeeperModelEntityAssignmentMap = {
    permission: ModelPermissionAssignment;
    role: ModelRoleAssignment;
    feature: ModelFeatureAssignment;
    team: ModelTeamAssignment;
};

export type GatekeeperModelEntityDenialMap = {
    permission: ModelPermissionDenial;
    role: ModelRoleDenial;
    feature: ModelFeatureDenial;
    team: ModelTeamDenial;
};

export type GatekeeperEntityModelMap = {
    permission: Permission;
    role: Role;
    feature: Feature;
    team: Team;
};

export type ModelManagementTab = 'overview' | 'permissions' | 'roles' | 'features' | 'teams';
