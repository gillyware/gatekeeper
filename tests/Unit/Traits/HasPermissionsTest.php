<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Services\GatekeeperForModelService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;

class HasPermissionsTest extends TestCase
{
    private GatekeeperForModelService $gatekeeperForModelService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gatekeeperForModelService = app(GatekeeperForModelService::class);
    }

    public function test_assign_permission_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permission = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('assignPermissionToModel')->with($user, $permission)->once();

        $user->assignPermission($permission);
    }

    public function test_assign_permissions_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permissions = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('assignAllPermissionsToModel')->with($user, $permissions)->once();

        $user->assignAllPermissions($permissions);
    }

    public function test_unassign_permission_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permission = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('unassignPermissionFromModel')->with($user, $permission)->once();

        $user->unassignPermission($permission);
    }

    public function test_unassign_permissions_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permissions = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('unassignAllPermissionsFromModel')->with($user, $permissions)->once();

        $user->unassignAllPermissions($permissions);
    }

    public function test_deny_permission_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permission = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('denyPermissionFromModel')->with($user, $permission)->once();

        $user->denyPermission($permission);
    }

    public function test_deny_permissions_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permissions = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('denyAllPermissionsFromModel')->with($user, $permissions)->once();

        $user->denyAllPermissions($permissions);
    }

    public function test_has_permission_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permission = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('modelHasPermission')->with($user, $permission)->once();

        $user->hasPermission($permission);
    }

    public function test_has_any_permission_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permissions = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('modelHasAnyPermission')->with($user, $permissions)->once();

        $user->hasAnyPermission($permissions);
    }

    public function test_has_all_permissions_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permissions = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('modelHasAllPermissions')->with($user, $permissions)->once();

        $user->hasAllPermissions($permissions);
    }
}
