<?php

namespace Braxey\Gatekeeper\Constants\AuditLog;

class Action
{
    public const PERMISSION_CREATE = 'permission_create';

    public const PERMISSION_ASSIGN = 'permission_assign';

    public const PERMISSION_REVOKE = 'permission_revoke';

    public const ROLE_CREATE = 'role_create';

    public const ROLE_ASSIGN = 'role_assign';

    public const ROLE_REVOKE = 'role_revoke';

    public const TEAM_CREATE = 'team_create';

    public const TEAM_ADD = 'team_add';

    public const TEAM_REMOVE = 'team_remove';
}
