<?php

namespace Gillyware\Gatekeeper\Enums;

enum FeatureSourceType: string
{
    case DEFAULT = 'default';
    case DIRECT = 'direct';
    case TEAM = 'team';
}
