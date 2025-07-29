import { type GatekeeperEntity } from '@/types';

export interface EntityIndexText {
    title: string;
    description: string;
    entityTableText: EntityTableText;
}

export interface EntityTableText {
    searchInputPlaceholder: string;
    nameColumn: string;
    grantedByDefaultColumn: string;
    statusColumn: string;
    active: string;
    inactive: string;
    granted: string;
    notGranted: string;
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
            grantedByDefaultColumn: 'Default',
            statusColumn: 'Status',
            active: 'Active',
            inactive: 'Inactive',
            granted: 'On',
            notGranted: 'Off',
            empty: 'No permissions found.',
            errorFallback: 'Failed to load permissions.',
            pagination: (from, to, total) => `${from} to ${to} of ${total}`,
            previous: 'Previous',
            next: 'Next',
        },
    },
    role: {
        title: 'Roles Index',
        description: "Take stock of your application's roles",
        entityTableText: {
            searchInputPlaceholder: 'Search by role name',
            nameColumn: 'Name',
            grantedByDefaultColumn: 'Default',
            statusColumn: 'Status',
            active: 'Active',
            inactive: 'Inactive',
            granted: 'On',
            notGranted: 'Off',
            empty: 'No roles found.',
            errorFallback: 'Failed to load roles.',
            pagination: (from, to, total) => `${from} to ${to} of ${total}`,
            previous: 'Previous',
            next: 'Next',
        },
    },
    feature: {
        title: 'Features Index',
        description: "Take stock of your application's features",
        entityTableText: {
            searchInputPlaceholder: 'Search by feature name',
            nameColumn: 'Name',
            grantedByDefaultColumn: 'Default',
            statusColumn: 'Status',
            active: 'Active',
            inactive: 'Inactive',
            granted: 'On',
            notGranted: 'Off',
            empty: 'No features found.',
            errorFallback: 'Failed to load features.',
            pagination: (from, to, total) => `${from} to ${to} of ${total}`,
            previous: 'Previous',
            next: 'Next',
        },
    },
    team: {
        title: 'Teams Index',
        description: "Take stock of your application's teams",
        entityTableText: {
            searchInputPlaceholder: 'Search by team name',
            nameColumn: 'Name',
            grantedByDefaultColumn: 'Default',
            statusColumn: 'Status',
            active: 'Active',
            inactive: 'Inactive',
            granted: 'On',
            notGranted: 'Off',
            empty: 'No teams found.',
            errorFallback: 'Failed to load teams.',
            pagination: (from, to, total) => `${from} to ${to} of ${total}`,
            previous: 'Previous',
            next: 'Next',
        },
    },
};
