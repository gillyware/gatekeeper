<?php

namespace Gillyware\Gatekeeper\Enums;

enum GatekeeperEntity: string
{
    case Permission = 'permission';
    case Role = 'role';
    case Feature = 'feature';
    case Team = 'team';
}
