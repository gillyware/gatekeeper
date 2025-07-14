import { type EntityFormType } from '@/components/entity/EntityForm';
import { type GatekeeperEntity } from '@/types';

export interface ManageEntityText {
    failedToLoad: string;
    entitySummaryText: EntitySummaryText;
    entityFormText: Record<EntityFormType, EntityFormText>;
    entityDeactivationText: EntityDeactivationText;
    entityReactivationText: EntityReactivationText;
    entityDeletionText: EntityDeletionText;
}

export interface EntitySummaryText {
    title: string;
    nameLabel: string;
    statusLabel: string;
    manageAccessLabel: string;
    active: string;
    inactive: string;
}

export interface EntityFormText {
    title: string;
    inputLabel: string;
    submitButton: string;
    successMessage: string;
}

export interface EntityDeactivationText {
    title: string;
    description: string;
    confirmTitle: string;
    confirmDescription: (entityName: string) => string;
    inputLabel: string;
    confirmButton: string;
    cancelButton: string;
    mismatchError: string;
}

export interface EntityReactivationText {
    title: string;
    description: string;
    confirmTitle: string;
    confirmDescription: (entityName: string) => string;
    inputLabel: string;
    confirmButton: string;
    cancelButton: string;
    mismatchError: string;
}

export interface EntityDeletionText {
    title: string;
    description: string;
    confirmTitle: string;
    confirmDescription: (entityName: string) => string;
    inputLabel: string;
    confirmButton: string;
    cancelButton: string;
    mismatchError: string;
}

export const manageEntityText: Record<GatekeeperEntity, ManageEntityText> = {
    permission: {
        failedToLoad: 'Failed to load permission',
        entitySummaryText: {
            title: 'Permission',
            nameLabel: 'Name:',
            statusLabel: 'Status:',
            active: 'Active',
            inactive: 'Inactive',
            manageAccessLabel: '',
        },
        entityFormText: {
            create: {
                title: 'Create Permission',
                inputLabel: 'Permission Name',
                submitButton: 'Create',
                successMessage: 'Saved',
            },
            update: {
                title: 'Update Permission',
                inputLabel: 'Permission Name',
                submitButton: 'Update',
                successMessage: 'Saved',
            },
        },
        entityDeactivationText: {
            title: 'Deactivate Permission',
            description:
                'The permission and its assignments will remain, but the permission will no longer grant access to anything until reactivated.',
            confirmTitle: 'Are you sure you want to deactivate this permission?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm deactivation of this permission.`,
            inputLabel: 'Permission Name',
            confirmButton: 'Deactivate',
            cancelButton: 'Cancel',
            mismatchError: 'Permission name does not match.',
        },
        entityReactivationText: {
            title: 'Reactivate Permission',
            description: "Once reactivated, the permission will once again grant access to all models it's currently assigned to.",
            confirmTitle: 'Are you sure you want to reactivate this permission?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm reactivation of this permission.`,
            inputLabel: 'Permission Name',
            confirmButton: 'Reactivate',
            cancelButton: 'Cancel',
            mismatchError: 'Permission name does not match.',
        },
        entityDeletionText: {
            title: 'Delete Permission',
            description: 'This permission will be removed from the application.',
            confirmTitle: 'Are you sure you want to delete this permission?',
            confirmDescription: (entityName: string) => `Type "${entityName}" to confirm deletion of this permission.`,
            inputLabel: 'Permission Name',
            confirmButton: 'Delete',
            cancelButton: 'Cancel',
            mismatchError: 'Permission name does not match.',
        },
    },
    role: {
        failedToLoad: 'Failed to load role',
        entitySummaryText: {
            title: 'Role',
            nameLabel: 'Name:',
            statusLabel: 'Status:',
            active: 'Active',
            inactive: 'Inactive',
            manageAccessLabel: 'Manage Role Access',
        },
        entityFormText: {
            create: {
                title: 'Create Role',
                inputLabel: 'Role Name',
                submitButton: 'Create',
                successMessage: 'Saved',
            },
            update: {
                title: 'Update Role',
                inputLabel: 'Role Name',
                submitButton: 'Update',
                successMessage: 'Saved',
            },
        },
        entityDeactivationText: {
            title: 'Deactivate Role',
            description: 'The role and its assignments will remain, but the role will no longer grant any permissions until reactivated.',
            confirmTitle: 'Are you sure you want to deactivate this role?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm deactivation of this role.`,
            inputLabel: 'Role Name',
            confirmButton: 'Deactivate',
            cancelButton: 'Cancel',
            mismatchError: 'Role name does not match.',
        },
        entityReactivationText: {
            title: 'Reactivate Role',
            description: 'Once reactivated, the role will once again grant its assigned permissions to all associated models.',
            confirmTitle: 'Are you sure you want to reactivate this role?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm reactivation of this role.`,
            inputLabel: 'Role Name',
            confirmButton: 'Reactivate',
            cancelButton: 'Cancel',
            mismatchError: 'Role name does not match.',
        },
        entityDeletionText: {
            title: 'Delete Role',
            description: 'This role will be removed from the application.',
            confirmTitle: 'Are you sure you want to delete this role?',
            confirmDescription: (entityName: string) => `Type "${entityName}" to confirm deletion of this role.`,
            inputLabel: 'Role Name',
            confirmButton: 'Delete',
            cancelButton: 'Cancel',
            mismatchError: 'Role name does not match.',
        },
    },
    team: {
        failedToLoad: 'Failed to load team',
        entitySummaryText: {
            title: 'Team',
            nameLabel: 'Name:',
            statusLabel: 'Status:',
            active: 'Active',
            inactive: 'Inactive',
            manageAccessLabel: 'Manage Team Access',
        },
        entityFormText: {
            create: {
                title: 'Create Team',
                inputLabel: 'Team Name',
                submitButton: 'Create',
                successMessage: 'Saved',
            },
            update: {
                title: 'Update Team',
                inputLabel: 'Team Name',
                submitButton: 'Update',
                successMessage: 'Saved',
            },
        },
        entityDeactivationText: {
            title: 'Deactivate Team',
            description: 'The team and its memberships will remain, but it will no longer grant roles or permissions until reactivated.',
            confirmTitle: 'Are you sure you want to deactivate this team?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm deactivation of this team.`,
            inputLabel: 'Team Name',
            confirmButton: 'Deactivate',
            cancelButton: 'Cancel',
            mismatchError: 'Team name does not match.',
        },
        entityReactivationText: {
            title: 'Reactivate Team',
            description: 'Once reactivated, the team will resume granting its roles and permissions to all members.',
            confirmTitle: 'Are you sure you want to reactivate this team?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm reactivation of this team.`,
            inputLabel: 'Team Name',
            confirmButton: 'Reactivate',
            cancelButton: 'Cancel',
            mismatchError: 'Team name does not match.',
        },
        entityDeletionText: {
            title: 'Delete Team',
            description: 'This team will be removed from the application.',
            confirmTitle: 'Are you sure you want to delete this team?',
            confirmDescription: (entityName: string) => `Type "${entityName}" to confirm deletion of this team.`,
            inputLabel: 'Team Name',
            confirmButton: 'Delete',
            cancelButton: 'Cancel',
            mismatchError: 'Team name does not match.',
        },
    },
};
