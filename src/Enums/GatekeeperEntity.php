<?php

namespace Gillyware\Gatekeeper\Enums;

enum GatekeeperEntity: string
{
    case Permission = 'permission';
    case Role = 'role';
    case Team = 'team';
}
