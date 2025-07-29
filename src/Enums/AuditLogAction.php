<?php

namespace Gillyware\Gatekeeper\Enums;

enum AuditLogAction: string
{
    case CreatePermission = 'permission_create';

    case UpdatePermissionName = 'permission_update_name';

    case GrantPermissionByDefault = 'permission_grant_by_default';

    case RevokePermissionDefaultGrant = 'permission_revoke_default_grant';

    case DeactivatePermission = 'permission_deactivate';

    case ReactivatePermission = 'permission_reactivate';

    case DeletePermission = 'permission_delete';

    case AssignPermission = 'permission_assign';

    case UnassignPermission = 'permission_unassign';

    case RevokePermission = 'permission_revoke'; // Deprecated.

    case DenyPermission = 'permission_deny';

    case UndenyPermission = 'permission_undeny';

    case CreateRole = 'role_create';

    case UpdateRoleName = 'role_update_name';

    case GrantRoleByDefault = 'role_grant_by_default';

    case RevokeRoleDefaultGrant = 'role_revoke_default_grant';

    case DeactivateRole = 'role_deactivate';

    case ReactivateRole = 'role_reactivate';

    case DeleteRole = 'role_delete';

    case AssignRole = 'role_assign';

    case UnassignRole = 'role_unassign';

    case RevokeRole = 'role_revoke'; // Deprecated.

    case DenyRole = 'role_deny';

    case UndenyRole = 'role_undeny';

    case CreateFeature = 'feature_create';

    case UpdateFeatureName = 'feature_update_name';

    case GrantFeatureByDefault = 'feature_grant_by_default';

    case RevokeFeatureDefaultGrant = 'feature_revoke_default_grant';

    case DeactivateFeature = 'feature_deactivate';

    case ReactivateFeature = 'feature_reactivate';

    case DeleteFeature = 'feature_delete';

    case AssignFeature = 'feature_assign';

    case UnassignFeature = 'feature_unassign';

    case RevokeFeature = 'feature_revoke'; // Deprecated.

    case DenyFeature = 'feature_deny';

    case UndenyFeature = 'feature_undeny';

    case CreateTeam = 'team_create';

    case UpdateTeamName = 'team_update_name';

    case GrantTeamByDefault = 'team_grant_by_default';

    case RevokeTeamDefaultGrant = 'team_revoke_default_grant';

    case DeactivateTeam = 'team_deactivate';

    case ReactivateTeam = 'team_reactivate';

    case DeleteTeam = 'team_delete';

    case AddTeam = 'team_add';

    case RemoveTeam = 'team_remove';

    case DenyTeam = 'team_deny';

    case UndenyTeam = 'team_undeny';

    public static function build(GatekeeperEntity $entity, AuditLogActionVerb $verb): static
    {
        return self::from($entity->value.'_'.$verb->value);
    }
}
