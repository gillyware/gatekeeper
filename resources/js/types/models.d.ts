interface EntityModel {
    id: number;
    name: string;
    is_active: boolean;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
}

interface EntityAssignment {
    id: number;
    model_type: string;
    model_id: string | number;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
}

export type Permission = EntityModel;

export type Role = EntityModel;

export type Team = EntityModel;

export type ModelPermissionAssignment = EntityAssignment & {
    permission_id: number;
    permission: Permission;
    assigned_at: string | null;
};

export type ModelRoleAssignmetn = EntityAssignment & {
    role_id: number;
    role: Role;
    assigned_at: string | null;
};

export type ModelTeamAssignment = EntityAssignment & {
    team_id: number;
    team: Team;
    assigned_at: string | null;
};

export interface AuditLog {
    id: number;
    message: string;
    created_at: string | null;
}
