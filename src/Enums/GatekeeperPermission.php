<?php

namespace Gillyware\Gatekeeper\Enums;

enum GatekeeperPermission: string
{
    case View = 'gatekeeper.view';
    case Manage = 'gatekeeper.manage';
}
