import { type GatekeeperEntity } from '@/types';
import { type FeatureSource, type PermissionSource, type RoleSource, type TeamSource } from '@/types/api/model';

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
    effectiveTeamsText: ModelEffectiveTeamsText;
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

export interface ModelEffectiveTeamsText {
    title: string;
    titleTooltip: string;
    toggleAllTooltip: (allOpen: boolean) => string;
    searchPlaceholder: string;
    sourceLabel: (source: TeamSource) => string;
    empty: string;
}

export type ModelEntityTablesText = {
    actionHeader: string;
    nameHeader: string;
    grantedByDefaultHeader: string;
    statusHeader: string;
    assignedDateTimeHeader: string;
    assign: string;
    unassign: string;
    deny: string;
    undeny: string;
    previous: string;
    next: string;
    pagination: (from: number, to: number, total: number) => string;
} & Record<GatekeeperEntity, EntitySpecificModelEntityTablesText>;

interface EntitySpecificModelEntityTablesText {
    assignedHeader: string;
    unassignedHeader: string;
    deniedHeader: string;
    searchPlaceholder: string;
    assignedEmpty: string;
    unassignedEmpty: string;
    deniedEmpty: string;
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
            titleTooltip: 'All permissions this model currently has, whether assigned directly, via roles, via teams, or granted by default',
            toggleAllTooltip: (allOpen: boolean) => (allOpen ? 'Close All' : 'Open All'),
            searchPlaceholder: 'Search permissions by name',
            sourceLabel: (source: PermissionSource) => {
                switch (source.type) {
                    case 'default':
                        return 'Granted by Default';
                    case 'direct':
                        return 'Direct';
                    case 'role':
                        return `Role: ${source.role}`;
                    case 'feature':
                        return `Feature: ${source.feature}`;
                    case 'team':
                        return `Team: ${source.team}`;
                    default:
                        return 'Unknown';
                }
            },
            empty: 'This model has no effective permissions.',
        },
        effectiveRolesText: {
            title: 'Effective Roles',
            titleTooltip: 'All roles this model currently has, whether assigned directly, via teams, or granted by default',
            toggleAllTooltip: (allOpen: boolean) => (allOpen ? 'Close All' : 'Open All'),
            searchPlaceholder: 'Search roles by name',
            sourceLabel: (source: RoleSource) => {
                switch (source.type) {
                    case 'default':
                        return 'Granted by Default';
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
            titleTooltip: 'All features this model currently has, whether assigned directly, via teams, or granted by default',
            toggleAllTooltip: (allOpen: boolean) => (allOpen ? 'Close All' : 'Open All'),
            searchPlaceholder: 'Search features by name',
            sourceLabel: (source: FeatureSource) => {
                switch (source.type) {
                    case 'default':
                        return 'Granted by Default';
                    case 'direct':
                        return 'Direct';
                    case 'team':
                        return `Team: ${source.team}`;
                    default:
                        return 'Unknown';
                }
            },
            empty: 'This model has no effective features.',
        },
        effectiveTeamsText: {
            title: 'Effective Teams',
            titleTooltip: 'All teams this model is currently on, whether turned on directly or granted by default',
            toggleAllTooltip: (allOpen: boolean) => (allOpen ? 'Close All' : 'Open All'),
            searchPlaceholder: 'Search teams by name',
            sourceLabel: (source: TeamSource) => {
                switch (source.type) {
                    case 'default':
                        return 'Granted by Default';
                    case 'direct':
                        return 'Direct';
                    default:
                        return 'Unknown';
                }
            },
            empty: 'This model has no effective teams.',
        },
    },
    modelEntityTablesText: {
        actionHeader: 'Action',
        nameHeader: 'Name',
        statusHeader: 'Status',
        grantedByDefaultHeader: 'Default',
        assignedDateTimeHeader: 'Assigned Date/Time',
        assign: 'Assign',
        unassign: 'Unassign',
        deny: 'Deny',
        undeny: 'Undeny',
        previous: 'Previous',
        next: 'Next',
        pagination: (from: number, to: number, total: number) => `${from} to ${to} of ${total}`,
        permission: {
            assignedHeader: 'Assigned Permissions',
            unassignedHeader: 'Unassigned Permissions',
            deniedHeader: 'Denied Permissions',
            searchPlaceholder: 'Search by permission name',
            assignedEmpty: 'No permissions assigned.',
            unassignedEmpty: 'No unassigned permissions.',
            deniedEmpty: 'No denied permissions.',
        },
        role: {
            assignedHeader: 'Assigned Roles',
            unassignedHeader: 'Unassigned Roles',
            deniedHeader: 'Denied Roles',
            searchPlaceholder: 'Search by role name',
            assignedEmpty: 'No roles assigned.',
            unassignedEmpty: 'No unassigned roles.',
            deniedEmpty: 'No denied roles.',
        },
        feature: {
            assignedHeader: 'Assigned Features',
            unassignedHeader: 'Unassigned Features',
            deniedHeader: 'Denied Features',
            searchPlaceholder: 'Search by feature name',
            assignedEmpty: 'No features assigned.',
            unassignedEmpty: 'No unassigned features.',
            deniedEmpty: 'No denied features',
        },
        team: {
            assignedHeader: 'Assigned Teams',
            unassignedHeader: 'Unassigned Teams',
            deniedHeader: 'Denied Teams',
            searchPlaceholder: 'Search by team name',
            assignedEmpty: 'No teams assigned.',
            unassignedEmpty: 'No unassigned teams.',
            deniedEmpty: 'No denied teams.',
        },
    },
};
