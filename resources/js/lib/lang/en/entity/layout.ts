import { type GatekeeperEntity } from '@/types';

export interface EntityLayoutText {
    title: string;
    description: string;
    featureDisabledTitle: string;
    featureDisabledDescription: string;
    navIndex: string;
    navCreate: string;
    navManage: string;
}

export const entityLayoutText: Record<GatekeeperEntity, EntityLayoutText> = {
    permission: {
        title: 'Permissions',
        description: "Manage your application's permissions",
        featureDisabledTitle: '',
        featureDisabledDescription: '',
        navIndex: 'Index',
        navCreate: 'Create',
        navManage: 'Manage',
    },
    role: {
        title: 'Roles',
        description: "Manage your application's roles",
        featureDisabledTitle: 'Roles Feature Disabled',
        featureDisabledDescription:
            'Roles cannot be created, edited, reactivated, or assigned at this time. Only deactivation and revocation are allowed. For full functionality, please enable the roles feature in your Gatekeeper configuration.',
        navIndex: 'Index',
        navCreate: 'Create',
        navManage: 'Manage',
    },
    feature: {
        title: 'Features',
        description: "Manage your application's features",
        featureDisabledTitle: 'Features Feature Disabled',
        featureDisabledDescription:
            'Features cannot be created, edited, reactivated, or turned on at this time. Only deactivation and turning off are allowed. For full functionality, please enable the features feature in your Gatekeeper configuration.',
        navIndex: 'Index',
        navCreate: 'Create',
        navManage: 'Manage',
    },
    team: {
        title: 'Teams',
        description: "Manage your application's teams",
        featureDisabledTitle: 'Teams Feature Disabled',
        featureDisabledDescription:
            'Teams cannot be created, edited, reactivated, or added to at this time. Only deactivation and removal from are allowed. For full functionality, please enable the teams feature in your Gatekeeper configuration.',
        navIndex: 'Index',
        navCreate: 'Create',
        navManage: 'Manage',
    },
};
