import { type GatekeeperEntity } from '@/types';

export interface EntityIndexText {
    title: string;
    description: string;
    entityTableText: EntityTableText;
}

export interface EntityTableText {
    searchInputPlaceholder: string;
    nameColumn: string;
    statusColumn: string;
    active: string;
    inactive: string;
    empty: string;
    errorFallback: string;
    pagination: (from: number, to: number, total: number) => string;
    previous: string;
    next: string;
}

export const entityIndexText: Record<GatekeeperEntity, EntityIndexText> = {
    permission: {
        title: 'Permissions Index',
        description: "Take stock of your application's permissions",
        entityTableText: {
            searchInputPlaceholder: 'Search by permission name',
            nameColumn: 'Name',
            statusColumn: 'Status',
            active: 'Active',
            inactive: 'Inactive',
            empty: 'No permissions found.',
            errorFallback: 'Failed to load permissions.',
            pagination: (from, to, total) => `${from} to ${to} of ${total}`,
            previous: 'Previous',
            next: 'Next',
        },
    },
    role: {
        title: 'Roles Index',
        description: "Take stock of your application's permissions",
        entityTableText: {
            searchInputPlaceholder: 'Search by role name',
            nameColumn: 'Name',
            statusColumn: 'Status',
            active: 'Active',
            inactive: 'Inactive',
            empty: 'No roles found.',
            errorFallback: 'Failed to load roles.',
            pagination: (from, to, total) => `${from} to ${to} of ${total}`,
            previous: 'Previous',
            next: 'Next',
        },
    },
    team: {
        title: 'Teams Index',
        description: "Take stock of your application's permissions",
        entityTableText: {
            searchInputPlaceholder: 'Search by team name',
            nameColumn: 'Name',
            statusColumn: 'Status',
            active: 'Active',
            inactive: 'Inactive',
            empty: 'No teams found.',
            errorFallback: 'Failed to load teams.',
            pagination: (from, to, total) => `${from} to ${to} of ${total}`,
            previous: 'Previous',
            next: 'Next',
        },
    },
};
