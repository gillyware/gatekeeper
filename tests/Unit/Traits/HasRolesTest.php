<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Facade;

class HasRolesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Facade::clearResolvedInstances();
        Gatekeeper::spy();
    }

    public function test_assign_role_delegates_to_facade()
    {
        $user = User::factory()->create();
        $role = 'admin';

        $user->assignRole($role);

        Gatekeeper::shouldHaveReceived('assignRoleToModel')->with($user, $role)->once();
    }

    public function test_assign_roles_delegates_to_facade()
    {
        $user = User::factory()->create();
        $roles = ['admin', 'editor'];

        $user->assignRoles($roles);

        Gatekeeper::shouldHaveReceived('assignRolesToModel')->with($user, $roles)->once();
    }

    public function test_assign_roles_delegates_with_arrayable()
    {
        $user = User::factory()->create();
        $roles = collect(['admin', 'editor']);

        $user->assignRoles($roles);

        Gatekeeper::shouldHaveReceived('assignRolesToModel')->with($user, $roles)->once();
    }

    public function test_revoke_role_delegates_to_facade()
    {
        $user = User::factory()->create();
        $role = 'admin';

        $user->revokeRole($role);

        Gatekeeper::shouldHaveReceived('revokeRoleFromModel')->with($user, $role)->once();
    }

    public function test_revoke_roles_delegates_to_facade()
    {
        $user = User::factory()->create();
        $roles = ['admin', 'editor'];

        $user->revokeRoles($roles);

        Gatekeeper::shouldHaveReceived('revokeRolesFromModel')->with($user, $roles)->once();
    }

    public function test_has_role_delegates_to_facade()
    {
        $user = User::factory()->create();
        $role = 'admin';

        $user->hasRole($role);

        Gatekeeper::shouldHaveReceived('modelHasRole')->with($user, $role)->once();
    }

    public function test_has_any_role_delegates_to_facade()
    {
        $user = User::factory()->create();
        $roles = ['admin', 'editor'];

        $user->hasAnyRole($roles);

        Gatekeeper::shouldHaveReceived('modelHasAnyRole')->with($user, $roles)->once();
    }

    public function test_has_all_roles_delegates_to_facade()
    {
        $user = User::factory()->create();
        $roles = ['admin', 'editor'];

        $user->hasAllRoles($roles);

        Gatekeeper::shouldHaveReceived('modelHasAllRoles')->with($user, $roles)->once();
    }
}
