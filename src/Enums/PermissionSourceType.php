<?php

namespace Gillyware\Gatekeeper\Enums;

enum PermissionSourceType: string
{
    case DIRECT = 'direct';
    case ROLE = 'role';
    case TEAM = 'team';
    case TEAM_ROLE = 'team-role';
}
