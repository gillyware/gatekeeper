export interface ManageModelText {
    modelSummaryText: ModelSummaryText;
}

export interface ModelSummaryText {
    entitySupportText: ModelEntitySupportText;
}

export interface ModelEntitySupportText {
    permission: {
        isPermission: string;
        missingTrait: string;
    };
    role: {
        featureDisabled: string;
        isPermission: string;
        isRole: string;
        missingTrait: string;
    };
    team: {
        featureDisabled: string;
        isPermission: string;
        isRole: string;
        isTeam: string;
        missingTrait: string;
    };
}

export const manageModelText: ManageModelText = {
    modelSummaryText: {
        entitySupportText: {
            permission: {
                isPermission: 'Permissions cannot be assigned to other permissions',
                missingTrait: 'The model is not using the `HasPermissions` trait',
            },
            role: {
                featureDisabled: "The 'roles' feature is disabled in the configuration",
                isPermission: 'Roles cannot be assigned to permissions',
                isRole: 'Roles cannot be assigned to other roles',
                missingTrait: 'The model is not using the `HasRoles` trait',
            },
            team: {
                featureDisabled: "The 'teams' feature is disabled in the configuration",
                isPermission: 'Teams cannot be assigned to permissions',
                isRole: 'Teams cannot be assigned to roles',
                isTeam: 'Teams cannot be assigned to other teams',
                missingTrait: 'The model is not using the `HasTeams` trait',
            },
        },
    },
};
