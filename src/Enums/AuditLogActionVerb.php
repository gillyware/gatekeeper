<?php

namespace Gillyware\Gatekeeper\Enums;

enum AuditLogActionVerb: string
{
    case Create = 'create';

    case UpdateName = 'update_name';

    case GrantByDefault = 'grant_by_default';

    case RevokeDefaultGrant = 'revoke_default_grant';

    case Deactivate = 'deactivate';

    case Reactivate = 'reactivate';

    case Delete = 'delete';

    case Assign = 'assign';

    case Unassign = 'unassign';

    case Add = 'add';

    case Remove = 'remove';

    case Revoke = 'revoke'; // Deprecated.

    case Deny = 'deny';

    case Undeny = 'undeny';
}
