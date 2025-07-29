import { type EntityFormType } from '@/components/entity/EntityForm';
import { type GatekeeperEntity } from '@/types';

export interface ManageEntityText {
    failedToLoad: string;
    entitySummaryText: EntitySummaryText;
    entityFormText: Record<EntityFormType, EntityFormText>;
    revokeEntityDefaultGrantText: RevokeEntityDefaultGrantText;
    grantEntityByDefaultText: GrantEntityByDefaultText;
    entityDeactivationText: EntityDeactivationText;
    entityReactivationText: EntityReactivationText;
    entityDeletionText: EntityDeletionText;
}

export interface EntitySummaryText {
    title: string;
    nameLabel: string;
    defaultValueLabel: string;
    statusLabel: string;
    manageAccessLabel: string;
    offByDefault: string;
    onByDefault: string;
    active: string;
    inactive: string;
}

export interface EntityFormText {
    title: string;
    inputLabel: string;
    submitButton: string;
    successMessage: string;
}

export interface RevokeEntityDefaultGrantText {
    title: string;
    description: string;
    confirmTitle: string;
    confirmDescription: (entityName: string) => string;
    inputLabel: string;
    confirmButton: string;
    cancelButton: string;
    mismatchError: string;
}

export interface GrantEntityByDefaultText {
    title: string;
    description: string;
    confirmTitle: string;
    confirmDescription: (entityName: string) => string;
    inputLabel: string;
    confirmButton: string;
    cancelButton: string;
    mismatchError: string;
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
            defaultValueLabel: 'Default:',
            statusLabel: 'Status:',
            offByDefault: 'Off',
            onByDefault: 'On',
            active: 'Active',
            inactive: 'Inactive',
            manageAccessLabel: 'Manage Permission Access',
        },
        entityFormText: {
            create: {
                title: 'Create Permission',
                inputLabel: 'Permission Name',
                submitButton: 'Create',
                successMessage: 'Saved',
            },
            update: {
                title: 'Update Permission Name',
                inputLabel: 'Permission Name',
                submitButton: 'Update',
                successMessage: 'Saved',
            },
        },
        revokeEntityDefaultGrantText: {
            title: 'Revoke Permission Default Grant',
            description:
                "Revoking this permission's default grant will require models to have it directly assigned or assigned via a role, feature, or team to access the permission.",
            confirmTitle: "Are you sure you want to revoke this permission's default grant?",
            confirmDescription: (entityName) => `Type "${entityName}" to confirm revoking this permission's default grant.`,
            inputLabel: 'Permission Name',
            confirmButton: 'Revoke Default Grant',
            cancelButton: 'Cancel',
            mismatchError: 'Permission name does not match.',
        },
        grantEntityByDefaultText: {
            title: 'Grant Permission by Default',
            description: 'Granting this permission by default will allow all models to access the permission, unless explicitly denied by the model.',
            confirmTitle: 'Are you sure you want to grant this permission by default?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm granting this permission by default.`,
            inputLabel: 'Permission Name',
            confirmButton: 'Grant by Default',
            cancelButton: 'Cancel',
            mismatchError: 'Permission name does not match.',
        },
        entityDeactivationText: {
            title: 'Deactivate Permission',
            description:
                'Deactivating this permission will keep all assignments intact but will temporarily stop granting access to assigned models.',
            confirmTitle: 'Are you sure you want to deactivate this permission?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm deactivation of this permission.`,
            inputLabel: 'Permission Name',
            confirmButton: 'Deactivate',
            cancelButton: 'Cancel',
            mismatchError: 'Permission name does not match.',
        },
        entityReactivationText: {
            title: 'Reactivate Permission',
            description: 'Reactivating this permission will resume granting access to its assigned models.',
            confirmTitle: 'Are you sure you want to reactivate this permission?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm reactivation of this permission.`,
            inputLabel: 'Permission Name',
            confirmButton: 'Reactivate',
            cancelButton: 'Cancel',
            mismatchError: 'Permission name does not match.',
        },
        entityDeletionText: {
            title: 'Delete Permission',
            description: 'Deleting this permission will remove it from the application and unassign it from all models.',
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
            defaultValueLabel: 'Default:',
            statusLabel: 'Status:',
            offByDefault: 'Off',
            onByDefault: 'On',
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
        revokeEntityDefaultGrantText: {
            title: 'Revoke Role Default Grant',
            description:
                "Revoking this role's default grant will require models to have it directly assigned or assigned via a team to access the role.",
            confirmTitle: "Are you sure you want to revoke this role's default grant?",
            confirmDescription: (entityName) => `Type "${entityName}" to confirm revoking this role's default grant.`,
            inputLabel: 'Role Name',
            confirmButton: 'Revoke Default Grant',
            cancelButton: 'Cancel',
            mismatchError: 'Role name does not match.',
        },
        grantEntityByDefaultText: {
            title: 'Grant Role by Default',
            description: 'Granting this role by default will allow all models to access the role, unless explicitly denied by the model.',
            confirmTitle: 'Are you sure you want to grant this role by default?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm granting this role by default.`,
            inputLabel: 'Role Name',
            confirmButton: 'Grant by Default',
            cancelButton: 'Cancel',
            mismatchError: 'Role name does not match.',
        },
        entityDeactivationText: {
            title: 'Deactivate Role',
            description:
                'Deactivating this role will keep all assignments intact but will temporarily stop granting its permissions to assigned models.',
            confirmTitle: 'Are you sure you want to deactivate this role?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm deactivation of this role.`,
            inputLabel: 'Role Name',
            confirmButton: 'Deactivate',
            cancelButton: 'Cancel',
            mismatchError: 'Role name does not match.',
        },
        entityReactivationText: {
            title: 'Reactivate Role',
            description: 'Reactivating this role will resume granting permissions to its assigned models.',
            confirmTitle: 'Are you sure you want to reactivate this role?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm reactivation of this role.`,
            inputLabel: 'Role Name',
            confirmButton: 'Reactivate',
            cancelButton: 'Cancel',
            mismatchError: 'Role name does not match.',
        },
        entityDeletionText: {
            title: 'Delete Role',
            description: 'Deleting this role will remove it from the application and unassign it from all models.',
            confirmTitle: 'Are you sure you want to delete this role?',
            confirmDescription: (entityName: string) => `Type "${entityName}" to confirm deletion of this role.`,
            inputLabel: 'Role Name',
            confirmButton: 'Delete',
            cancelButton: 'Cancel',
            mismatchError: 'Role name does not match.',
        },
    },
    feature: {
        failedToLoad: 'Failed to load feature',
        entitySummaryText: {
            title: 'Feature',
            nameLabel: 'Name:',
            defaultValueLabel: 'Default:',
            statusLabel: 'Status:',
            offByDefault: 'Off',
            onByDefault: 'On',
            active: 'Active',
            inactive: 'Inactive',
            manageAccessLabel: 'Manage Feature Access',
        },
        entityFormText: {
            create: {
                title: 'Create Feature',
                inputLabel: 'Feature Name',
                submitButton: 'Create',
                successMessage: 'Saved',
            },
            update: {
                title: 'Update Feature',
                inputLabel: 'Feature Name',
                submitButton: 'Update',
                successMessage: 'Saved',
            },
        },
        revokeEntityDefaultGrantText: {
            title: 'Revoke Feature Default Grant',
            description:
                "Revoking this feature's default grant will require models to have it directly assigned or assigned via a team to access the feature.",
            confirmTitle: "Are you sure you want to revoke this feature's default grant?",
            confirmDescription: (entityName) => `Type "${entityName}" to confirm revoking this feature's default grant.`,
            inputLabel: 'Feature Name',
            confirmButton: 'Revoke Default Grant',
            cancelButton: 'Cancel',
            mismatchError: 'Feature name does not match.',
        },
        grantEntityByDefaultText: {
            title: 'Grant Feature by Default',
            description: 'Granting this feature by default will allow all models to access the feature, unless explicitly denied by the model.',
            confirmTitle: 'Are you sure you want to grant this feature by default?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm granting this feature by default.`,
            inputLabel: 'Feature Name',
            confirmButton: 'Grant by Default',
            cancelButton: 'Cancel',
            mismatchError: 'Feature name does not match.',
        },
        entityDeactivationText: {
            title: 'Deactivate Feature',
            description:
                'Deactivating this feature will keep all assignments intact but will temporarily stop granting its permissions to assigned models.',
            confirmTitle: 'Are you sure you want to deactivate this feature?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm deactivation of this feature.`,
            inputLabel: 'Feature Name',
            confirmButton: 'Deactivate',
            cancelButton: 'Cancel',
            mismatchError: 'Feature name does not match.',
        },
        entityReactivationText: {
            title: 'Reactivate Feature',
            description: 'Reactivating this feature will resume granting permissions to its assigned models.',
            confirmTitle: 'Are you sure you want to reactivate this feature?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm reactivation of this feature.`,
            inputLabel: 'Feature Name',
            confirmButton: 'Reactivate',
            cancelButton: 'Cancel',
            mismatchError: 'Feature name does not match.',
        },
        entityDeletionText: {
            title: 'Delete Feature',
            description: 'Deleting this feature will remove it from the application and unassign it from all models.',
            confirmTitle: 'Are you sure you want to delete this feature?',
            confirmDescription: (entityName: string) => `Type "${entityName}" to confirm deletion of this feature.`,
            inputLabel: 'Feature Name',
            confirmButton: 'Delete',
            cancelButton: 'Cancel',
            mismatchError: 'Feature name does not match.',
        },
    },
    team: {
        failedToLoad: 'Failed to load team',
        entitySummaryText: {
            title: 'Team',
            nameLabel: 'Name:',
            defaultValueLabel: 'Default:',
            statusLabel: 'Status:',
            offByDefault: 'Off',
            onByDefault: 'On',
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
        revokeEntityDefaultGrantText: {
            title: 'Revoke Team Default Grant',
            description: "Revoking this team's default grant will require models to be directly assigned to the team to access it.",
            confirmTitle: "Are you sure you want to revoke this team's default grant?",
            confirmDescription: (entityName) => `Type "${entityName}" to confirm revoking this team's default grant.`,
            inputLabel: 'Team Name',
            confirmButton: 'Revoke Default Grant',
            cancelButton: 'Cancel',
            mismatchError: 'Team name does not match.',
        },
        grantEntityByDefaultText: {
            title: 'Grant Team by Default',
            description: 'Granting this team by default will allow all models to access the team, unless explicitly denied by the model.',
            confirmTitle: 'Are you sure you want to grant this team by default?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm granting this team by default.`,
            inputLabel: 'Team Name',
            confirmButton: 'Grant by Default',
            cancelButton: 'Cancel',
            mismatchError: 'Team name does not match.',
        },
        entityDeactivationText: {
            title: 'Deactivate Team',
            description:
                'Deactivating this team will keep all memberships intact but will temporarily stop granting its roles and permissions to members.',
            confirmTitle: 'Are you sure you want to deactivate this team?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm deactivation of this team.`,
            inputLabel: 'Team Name',
            confirmButton: 'Deactivate',
            cancelButton: 'Cancel',
            mismatchError: 'Team name does not match.',
        },
        entityReactivationText: {
            title: 'Reactivate Team',
            description: 'Reactivating this team will resume granting its roles and permissions to all members.',
            confirmTitle: 'Are you sure you want to reactivate this team?',
            confirmDescription: (entityName) => `Type "${entityName}" to confirm reactivation of this team.`,
            inputLabel: 'Team Name',
            confirmButton: 'Reactivate',
            cancelButton: 'Cancel',
            mismatchError: 'Team name does not match.',
        },
        entityDeletionText: {
            title: 'Delete Team',
            description: 'Deleting this team will remove it from the application and unassign it from all models.',
            confirmTitle: 'Are you sure you want to delete this team?',
            confirmDescription: (entityName: string) => `Type "${entityName}" to confirm deletion of this team.`,
            inputLabel: 'Team Name',
            confirmButton: 'Delete',
            cancelButton: 'Cancel',
            mismatchError: 'Team name does not match.',
        },
    },
};
