<?php

namespace Gillyware\Gatekeeper\Enums;

enum PermissionSourceType: string
{
    case DEFAULT = 'default';
    case DIRECT = 'direct';
    case ROLE = 'role';
    case FEATURE = 'feature';
    case TEAM = 'team';
    case TEAM_ROLE = 'team-role';
}
