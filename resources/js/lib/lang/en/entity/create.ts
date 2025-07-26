import { type GatekeeperEntity } from '@/types';

export interface CreateEntityText {
    title: string;
    description: string;
}

export const createEntityText: Record<GatekeeperEntity, CreateEntityText> = {
    permission: {
        title: 'Create Permission',
        description: 'Introduce a new permission into your application',
    },
    role: {
        title: 'Create Role',
        description: 'Introduce a new role into your application',
    },
    feature: {
        title: 'Create Feature',
        description: 'Introduce a new feature into your application',
    },
    team: {
        title: 'Create Team',
        description: 'Introduce a new team into your application',
    },
};
