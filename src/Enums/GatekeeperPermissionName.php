<?php

namespace Gillyware\Gatekeeper\Enums;

enum GatekeeperPermissionName: string
{
    case View = 'gatekeeper.view';
    case Manage = 'gatekeeper.manage';
}
