export interface LandingText {
    title: string;
    description: string;
    tiles: {
        permissions: { title: string; description: string };
        roles: { title: string; description: string };
        teams: { title: string; description: string };
        models: { title: string; description: string };
        audit: { title: string; description: string };
    };
}

export const landingText: LandingText = {
    title: 'Gatekeeper',
    description: 'Manage permissions, roles, teams, and model access for your application',
    tiles: {
        permissions: {
            title: 'Permissions',
            description: "Manage your application's permissions",
        },
        roles: {
            title: 'Roles',
            description: "Manage your application's roles",
        },
        teams: {
            title: 'Teams',
            description: "Manage your application's teams",
        },
        models: {
            title: 'Models',
            description: 'Manage access for models in your application',
        },
        audit: {
            title: 'Audit',
            description: 'Oversee Gatekeeper changes in your application',
        },
    },
};
