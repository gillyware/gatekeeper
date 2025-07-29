<?php

namespace Gillyware\Gatekeeper\Enums;

enum EntityUpdateAction: string
{
    case Name = 'name';
    case DefaultGrant = 'default_grant';
    case Status = 'status';
}
