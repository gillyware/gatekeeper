import { type EntityFormType } from '@/components/entity/EntityForm';
import { type GatekeeperEntity } from '@/types';

export interface ApiText {
    badRequest: string;
    unexpectedError: string;
    entities: Record<GatekeeperEntity, EntityApiText>;
    models: ModelApiText;
}

export interface EntityApiText {
    getPageError: string;
    getOneError: string;
    persistError: Record<EntityFormType, string>;
    revokeDefaultGrantError: string;
    grantByDefaultError: string;
    deactivateError: string;
    reactivateError: string;
    deleteError: string;
}

export interface ModelApiText {
    getPageError: string;
}

export const apiText: ApiText = {
    badRequest: 'Bad request.',
    unexpectedError: 'An unexpected error occurred.',
    entities: {
        permission: {
            getPageError: 'Failed to fetch permissions.',
            getOneError: 'Faild to fetch permission.',
            persistError: {
                create: 'Failed to create permission.',
                update: 'Failed to update permission.',
            },
            revokeDefaultGrantError: '',
            grantByDefaultError: '',
            deactivateError: 'Failed to deactivate permission.',
            reactivateError: 'Failed to reactivate permission.',
            deleteError: 'Failed to delete permission.',
        },
        role: {
            getPageError: 'Failed to fetch roles.',
            getOneError: 'Faild to fetch role.',
            persistError: {
                create: 'Failed to create role.',
                update: 'Failed to update role.',
            },
            revokeDefaultGrantError: '',
            grantByDefaultError: '',
            deactivateError: 'Failed to deactivate role.',
            reactivateError: 'Failed to reactivate role.',
            deleteError: 'Failed to delete role.',
        },
        feature: {
            getPageError: 'Failed to fetch features.',
            getOneError: 'Faild to fetch feature.',
            persistError: {
                create: 'Failed to create feature.',
                update: 'Failed to update feature.',
            },
            revokeDefaultGrantError: 'Failed to grant feature by default.',
            grantByDefaultError: 'Failed to unassign feature default grant.',
            deactivateError: 'Failed to deactivate feature.',
            reactivateError: 'Failed to reactivate feature.',
            deleteError: 'Failed to delete feature.',
        },
        team: {
            getPageError: 'Failed to fetch teams.',
            getOneError: 'Faild to fetch team.',
            persistError: {
                create: 'Failed to create team.',
                update: 'Failed to update team.',
            },
            revokeDefaultGrantError: '',
            grantByDefaultError: '',
            deactivateError: 'Failed to deactivate team.',
            reactivateError: 'Failed to reactivate team.',
            deleteError: 'Failed to delete team.',
        },
    },
    models: {
        getPageError: 'Failed to fetch models.',
    },
};
