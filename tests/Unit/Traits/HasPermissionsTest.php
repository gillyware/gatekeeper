<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Facade;

class HasPermissionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Facade::clearResolvedInstances();
        Gatekeeper::spy();
    }

    public function test_assign_permission_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permission = 'edit-posts';

        $user->assignPermission($permission);

        Gatekeeper::shouldHaveReceived('assignPermissionToModel')->with($user, $permission)->once();
    }

    public function test_assign_permissions_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permissions = ['edit-posts', 'delete-posts'];

        $user->assignPermissions($permissions);

        Gatekeeper::shouldHaveReceived('assignPermissionsToModel')->with($user, $permissions)->once();
    }

    public function test_assign_permissions_delegates_with_arrayable()
    {
        $user = User::factory()->create();
        $permissions = collect(['edit-posts', 'delete-posts']);

        $user->assignPermissions($permissions);

        Gatekeeper::shouldHaveReceived('assignPermissionsToModel')->with($user, $permissions)->once();
    }

    public function test_revoke_permission_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permission = 'edit-posts';

        $user->revokePermission($permission);

        Gatekeeper::shouldHaveReceived('revokePermissionFromModel')->with($user, $permission)->once();
    }

    public function test_revoke_permissions_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permissions = ['edit-posts', 'delete-posts'];

        $user->revokePermissions($permissions);

        Gatekeeper::shouldHaveReceived('revokePermissionsFromModel')->with($user, $permissions)->once();
    }

    public function test_has_permission_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permission = 'edit-posts';

        $user->hasPermission($permission);

        Gatekeeper::shouldHaveReceived('modelHasPermission')->with($user, $permission)->once();
    }

    public function test_has_any_permission_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permissions = ['edit-posts', 'delete-posts'];

        $user->hasAnyPermission($permissions);

        Gatekeeper::shouldHaveReceived('modelHasAnyPermission')->with($user, $permissions)->once();
    }

    public function test_has_all_permissions_delegates_to_facade()
    {
        $user = User::factory()->create();
        $permissions = ['edit-posts', 'delete-posts'];

        $user->hasAllPermissions($permissions);

        Gatekeeper::shouldHaveReceived('modelHasAllPermissions')->with($user, $permissions)->once();
    }
}
