interface EntityModel {
    id: number;
    name: string;
    is_active: boolean;
    grant_by_default: boolean;
}

interface EntityAssignment {
    id: number;
    model_type: string;
    model_id: string | number;
}

export type Permission = EntityModel;

export type Role = EntityModel;

export type Feature = EntityModel;

export type Team = EntityModel;

export type ModelPermissionAssignment = EntityAssignment & {
    permission_id: number;
    permission: Permission;
    assigned_at: string | null;
};

export type ModelRoleAssignment = EntityAssignment & {
    role_id: number;
    role: Role;
    assigned_at: string | null;
};

export type ModelFeatureAssignment = EntityAssignment & {
    feature_id: number;
    feature: Feature;
    assigned_at: string | null;
};

export type ModelTeamAssignment = EntityAssignment & {
    team_id: number;
    team: Team;
    assigned_at: string | null;
};

export type ModelPermissionDenial = EntityAssignment & {
    permission_id: number;
    permission: Permission;
    denied_at: string | null;
};

export type ModelRoleDenial = EntityAssignment & {
    role_id: number;
    role: Role;
    denied_at: string | null;
};

export type ModelFeatureDenial = EntityAssignment & {
    feature_id: number;
    feature: Feature;
    denied_at: string | null;
};

export type ModelTeamDenial = EntityAssignment & {
    team_id: number;
    team: Team;
    denied_at: string | null;
};

export interface AuditLog {
    id: number;
    message: string;
    created_at: string | null;
}
