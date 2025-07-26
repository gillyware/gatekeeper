<?php

namespace Gillyware\Gatekeeper\Enums;

enum FeatureSourceType: string
{
    case DIRECT = 'direct';
    case TEAM = 'team';
    case DEFAULT = 'default';
}
