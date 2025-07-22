<?php

namespace Gillyware\Gatekeeper\Enums;

enum AuditLogAction: string
{
    case CreatePermission = 'permission_create';

    case UpdatePermission = 'permission_update';

    case DeactivatePermission = 'permission_deactivate';

    case ReactivatePermission = 'permission_reactivate';

    case DeletePermission = 'permission_delete';

    case AssignPermission = 'permission_assign';

    case RevokePermission = 'permission_revoke';

    case CreateRole = 'role_create';

    case UpdateRole = 'role_update';

    case DeactivateRole = 'role_deactivate';

    case ReactivateRole = 'role_reactivate';

    case DeleteRole = 'role_delete';

    case AssignRole = 'role_assign';

    case RevokeRole = 'role_revoke';

    case CreateTeam = 'team_create';

    case UpdateTeam = 'team_update';

    case DeactivateTeam = 'team_deactivate';

    case ReactivateTeam = 'team_reactivate';

    case DeleteTeam = 'team_delete';

    case AddTeam = 'team_add';

    case RemoveTeam = 'team_remove';
}
