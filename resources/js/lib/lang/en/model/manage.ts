import { type GatekeeperEntity } from '@/types';
import { PermissionSource } from '@/types/api/model';

export interface ManageModelText {
    failedToLoad: string;
    modelManagementTabsText: ModelManagementTabsText;
    modelSummaryText: ModelSummaryText;
    modelEntityTablesText: ModelEntityTablesText;
}

export interface ModelSummaryText {
    modelLabel: string;
    entitySupportLabel: string;
    entitySupportText: ModelEntitySupportText;
    effectivePermissionsText: ModelEffectivePermissionsText;
}

export interface ModelManagementTabsText {
    navOverview: string;
    navPermission: string;
    navRoles: string;
    navTeams: string;
}

export interface ModelEntitySupportText {
    permission: {
        label: string;
        isPermission: string;
        missingTrait: string;
    };
    role: {
        label: string;
        featureDisabled: string;
        isPermission: string;
        isRole: string;
        missingTrait: string;
    };
    team: {
        label: string;
        featureDisabled: string;
        isPermission: string;
        isRole: string;
        isTeam: string;
        missingTrait: string;
    };
}

export interface ModelEffectivePermissionsText {
    title: string;
    titleTooltip: string;
    toggleAllTooltip: (allOpen: boolean) => string;
    searchPlaceholder: string;
    sourceLabel: (source: PermissionSource) => string;
}

export type ModelEntityTablesText = {
    actionHeader: string;
    assignedDateTimeHeader: string;
    assign: string;
    revoke: string;
    previous: string;
    next: string;
    pagination: (from: number, to: number, total: number) => string;
} & Record<GatekeeperEntity, EntitySpecificModelEntityTablesText>;

interface EntitySpecificModelEntityTablesText {
    assignedHeader: string;
    unassignedHeader: string;
    searchPlaceholder: string;
    nameHeader: string;
    statusHeader: string;
    assignedEmpty: string;
    unassignedEmpty: string;
}

export const manageModelText: ManageModelText = {
    failedToLoad: 'Failed to load model',
    modelManagementTabsText: {
        navOverview: 'Overview',
        navPermission: 'Permissions',
        navRoles: 'Roles',
        navTeams: 'Teams',
    },
    modelSummaryText: {
        modelLabel: 'Model:',
        entitySupportLabel: 'Supports',
        entitySupportText: {
            permission: {
                label: 'Permissions:',
                isPermission: 'Permissions cannot be assigned to other permissions',
                missingTrait: 'The model is not using the `HasPermissions` trait',
            },
            role: {
                label: 'Roles:',
                featureDisabled: "The 'roles' feature is disabled in the configuration",
                isPermission: 'Roles cannot be assigned to permissions',
                isRole: 'Roles cannot be assigned to other roles',
                missingTrait: 'The model is not using the `HasRoles` trait',
            },
            team: {
                label: 'Teams:',
                featureDisabled: "The 'teams' feature is disabled in the configuration",
                isPermission: 'Teams cannot be assigned to permissions',
                isRole: 'Teams cannot be assigned to roles',
                isTeam: 'Teams cannot be assigned to other teams',
                missingTrait: 'The model is not using the `HasTeams` trait',
            },
        },
        effectivePermissionsText: {
            title: 'Effective Permissions',
            titleTooltip: 'All permissions this model currently has — whether assigned directly, via roles, or via teams',
            toggleAllTooltip: (allOpen: boolean) => (allOpen ? 'Close All' : 'Open All'),
            searchPlaceholder: 'Search permissions by name',
            sourceLabel: (source: PermissionSource) => {
                switch (source.type) {
                    case 'direct':
                        return 'Direct';
                    case 'role':
                        return `Role: ${source.role}`;
                    case 'team':
                        return `Team: ${source.team}`;
                    case 'team-role':
                        return `Team: ${source.team} via Role: ${source.role}`;
                    default:
                        return 'Unknown';
                }
            },
        },
    },
    modelEntityTablesText: {
        actionHeader: 'Action',
        assignedDateTimeHeader: 'Assigned Date/Time',
        assign: 'Assign',
        revoke: 'Revoke',
        previous: 'Previous',
        next: 'Next',
        pagination: (from: number, to: number, total: number) => `${from} to ${to} of ${total}`,
        permission: {
            assignedHeader: 'Assigned Permissions',
            unassignedHeader: 'Unassigned Permissions',
            searchPlaceholder: 'Search by permission name',
            nameHeader: 'Permission Name',
            statusHeader: 'Permission Status',
            assignedEmpty: 'No permissions assigned.',
            unassignedEmpty: 'No unassigned permissions.',
        },
        role: {
            assignedHeader: 'Assigned Roles',
            unassignedHeader: 'Unassigned Roles',
            searchPlaceholder: 'Search by role name',
            nameHeader: 'Role Name',
            statusHeader: 'Role Status',
            assignedEmpty: 'No roles assigned.',
            unassignedEmpty: 'No unassigned roles.',
        },
        team: {
            assignedHeader: 'Assigned Teams',
            unassignedHeader: 'Unassigned Teams',
            searchPlaceholder: 'Search by team name',
            nameHeader: 'Team Name',
            statusHeader: 'Team Status',
            assignedEmpty: 'No teams assigned.',
            unassignedEmpty: 'No unassigned teams.',
        },
    },
};
