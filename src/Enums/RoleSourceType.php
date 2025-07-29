<?php

namespace Gillyware\Gatekeeper\Enums;

enum RoleSourceType: string
{
    case DEFAULT = 'default';
    case DIRECT = 'direct';
    case TEAM = 'team';
}
