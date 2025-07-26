import { type GatekeeperEntity } from '@/types';
import { type FeatureSource, type PermissionSource, type RoleSource } from '@/types/api/model';

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
    effectiveRolesText: ModelEffectiveRolesText;
    effectiveFeaturesText: ModelEffectiveFeaturesText;
}

export interface ModelManagementTabsText {
    navOverview: string;
    navPermission: string;
    navRoles: string;
    navFeatures: string;
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
        isFeature: string;
        missingTrait: string;
    };
    feature: {
        label: string;
        featureDisabled: string;
        isPermission: string;
        isFeature: string;
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
    empty: string;
}

export interface ModelEffectiveRolesText {
    title: string;
    titleTooltip: string;
    toggleAllTooltip: (allOpen: boolean) => string;
    searchPlaceholder: string;
    sourceLabel: (source: RoleSource) => string;
    empty: string;
}

export interface ModelEffectiveFeaturesText {
    title: string;
    titleTooltip: string;
    toggleAllTooltip: (allOpen: boolean) => string;
    searchPlaceholder: string;
    sourceLabel: (source: FeatureSource) => string;
    empty: string;
}

export type ModelEntityTablesText = {
    actionHeader: string;
    assignedDateTimeHeader: string;
    previous: string;
    next: string;
    pagination: (from: number, to: number, total: number) => string;
} & Record<GatekeeperEntity, EntitySpecificModelEntityTablesText>;

interface EntitySpecificModelEntityTablesText {
    assign: string;
    revoke: string;
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
        navFeatures: 'Features',
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
                isFeature: 'Roles cannot be assigned to features',
                missingTrait: 'The model is not using the `HasRoles` trait',
            },
            feature: {
                label: 'Features:',
                featureDisabled: "The 'features' feature is disabled in the configuration",
                isPermission: 'Features cannot be assigned to permissions',
                isFeature: 'Features cannot be assigned to other features',
                isRole: 'Features cannot be assigned to roles',
                missingTrait: 'The model is not using the `HasFeatures` trait',
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
            empty: 'This model has no effective permissions.',
        },
        effectiveRolesText: {
            title: 'Effective Roles',
            titleTooltip: 'All roles this model currently has — whether assigned directly or via teams',
            toggleAllTooltip: (allOpen: boolean) => (allOpen ? 'Close All' : 'Open All'),
            searchPlaceholder: 'Search roles by name',
            sourceLabel: (source: RoleSource) => {
                switch (source.type) {
                    case 'direct':
                        return 'Direct';
                    case 'team':
                        return `Team: ${source.team}`;
                    default:
                        return 'Unknown';
                }
            },
            empty: 'This model has no effective roles.',
        },
        effectiveFeaturesText: {
            title: 'Effective Features',
            titleTooltip: 'All features this model currently has — whether turned on directly, via teams, or on by default',
            toggleAllTooltip: (allOpen: boolean) => (allOpen ? 'Close All' : 'Open All'),
            searchPlaceholder: 'Search features by name',
            sourceLabel: (source: FeatureSource) => {
                switch (source.type) {
                    case 'direct':
                        return 'Direct';
                    case 'team':
                        return `Team: ${source.team}`;
                    case 'default':
                        return 'Default';
                    default:
                        return 'Unknown';
                }
            },
            empty: 'This model has no effective features.',
        },
    },
    modelEntityTablesText: {
        actionHeader: 'Action',
        assignedDateTimeHeader: 'Assigned Date/Time',
        previous: 'Previous',
        next: 'Next',
        pagination: (from: number, to: number, total: number) => `${from} to ${to} of ${total}`,
        permission: {
            assign: 'Assign',
            revoke: 'Revoke',
            assignedHeader: 'Assigned Permissions',
            unassignedHeader: 'Unassigned Permissions',
            searchPlaceholder: 'Search by permission name',
            nameHeader: 'Permission Name',
            statusHeader: 'Permission Status',
            assignedEmpty: 'No permissions assigned.',
            unassignedEmpty: 'No unassigned permissions.',
        },
        role: {
            assign: 'Assign',
            revoke: 'Revoke',
            assignedHeader: 'Assigned Roles',
            unassignedHeader: 'Unassigned Roles',
            searchPlaceholder: 'Search by role name',
            nameHeader: 'Role Name',
            statusHeader: 'Role Status',
            assignedEmpty: 'No roles assigned.',
            unassignedEmpty: 'No unassigned roles.',
        },
        feature: {
            assign: 'Turn On',
            revoke: 'Turn Off',
            assignedHeader: 'Assigned Features',
            unassignedHeader: 'Unassigned Features',
            searchPlaceholder: 'Search by feature name',
            nameHeader: 'Feature Name',
            statusHeader: 'Feature Status',
            assignedEmpty: 'No features assigned.',
            unassignedEmpty: 'No unassigned features.',
        },
        team: {
            assign: 'Assign',
            revoke: 'Revoke',
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
