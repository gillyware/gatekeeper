<?php

namespace Gillyware\Gatekeeper\Http\Middleware;

use Gillyware\Gatekeeper\Services\FeatureService;
use Gillyware\Gatekeeper\Services\PermissionService;
use Gillyware\Gatekeeper\Services\RoleService;
use Gillyware\Gatekeeper\Services\TeamService;
use Gillyware\Gatekeeper\Traits\Responds;

abstract class AbstractBaseEntityMiddleware
{
    use Responds;

    public function __construct(
        protected readonly PermissionService $permissionService,
        protected readonly RoleService $roleService,
        protected readonly FeatureService $featureService,
        protected readonly TeamService $teamService,
    ) {}
}
