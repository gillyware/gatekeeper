interface EntityModel {
    id: number;
    name: string;
    is_active: boolean;
}

interface EntityAssignment {
    id: number;
    model_type: string;
    model_id: string | number;
}

export type Permission = EntityModel;

export type Role = EntityModel;

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
