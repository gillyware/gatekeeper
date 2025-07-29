<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Services\GatekeeperForModelService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;

class HasRolesTest extends TestCase
{
    private GatekeeperForModelService $gatekeeperForModelService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gatekeeperForModelService = app(GatekeeperForModelService::class);
    }

    public function test_assign_role_delegates_to_facade()
    {
        $user = User::factory()->create();
        $role = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('assignRoleToModel')->with($user, $role)->once();

        $user->assignRole($role);
    }

    public function test_assign_roles_delegates_to_facade()
    {
        $user = User::factory()->create();
        $roles = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('assignAllRolesToModel')->with($user, $roles)->once();

        $user->assignAllRoles($roles);
    }

    public function test_unassign_role_delegates_to_facade()
    {
        $user = User::factory()->create();
        $role = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('unassignRoleFromModel')->with($user, $role)->once();

        $user->unassignRole($role);
    }

    public function test_unassign_roles_delegates_to_facade()
    {
        $user = User::factory()->create();
        $roles = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('unassignAllRolesFromModel')->with($user, $roles)->once();

        $user->unassignAllRoles($roles);
    }

    public function test_deny_role_delegates_to_facade()
    {
        $user = User::factory()->create();
        $role = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('denyRoleFromModel')->with($user, $role)->once();

        $user->denyRole($role);
    }

    public function test_deny_roles_delegates_to_facade()
    {
        $user = User::factory()->create();
        $roles = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('denyAllRolesFromModel')->with($user, $roles)->once();

        $user->denyAllRoles($roles);
    }

    public function test_has_role_delegates_to_facade()
    {
        $user = User::factory()->create();
        $role = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('modelHasRole')->with($user, $role)->once();

        $user->hasRole($role);
    }

    public function test_has_any_role_delegates_to_facade()
    {
        $user = User::factory()->create();
        $roles = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('modelHasAnyRole')->with($user, $roles)->once();

        $user->hasAnyRole($roles);
    }

    public function test_has_all_roles_delegates_to_facade()
    {
        $user = User::factory()->create();
        $roles = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('modelHasAllRoles')->with($user, $roles)->once();

        $user->hasAllRoles($roles);
    }
}
