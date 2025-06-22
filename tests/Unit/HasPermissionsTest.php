<?php

namespace Braxey\Gatekeeper\Tests\Unit;

use Braxey\Gatekeeper\Models\ModelHasPermission;
use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Carbon;

class HasPermissionsTest extends TestCase
{
    public function test_we_can_assign_a_permission()
    {
        $user = User::factory()->create();

        $permissionName = fake()->unique()->word();
        $permission = Permission::factory()->withName($permissionName)->create();

        $result = $user->assignPermission($permissionName);

        $this->assertTrue($result);
        $this->assertDatabaseHas('model_has_permissions', [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'permission_id' => $permission->id,
            'deleted_at' => null,
        ]);
    }

    public function test_we_cannot_assign_a_permission_twice()
    {
        $user = User::factory()->create();

        $permissionName = fake()->unique()->word();
        $permission = Permission::factory()->withName($permissionName)->create();

        $user->assignPermission($permissionName);
        $result = $user->assignPermission($permissionName);

        $this->assertTrue($result);
        $this->assertEquals(1, ModelHasPermission::forModel($user)
            ->where('permission_id', $permission->id)
            ->count());
    }

    public function test_we_can_revoke_a_permission()
    {
        $user = User::factory()->create();

        $permissionName = fake()->unique()->word();
        $permission = Permission::factory()->withName($permissionName)->create();

        $user->assignPermission($permissionName);
        $user->revokePermission($permissionName);

        $modelHasPermissions = ModelHasPermission::forModel($user)
            ->where('permission_id', $permission->id)
            ->withTrashed()
            ->get();

        $this->assertCount(1, $modelHasPermissions);
        $this->assertNotNull($modelHasPermissions->first()->deleted_at);
    }

    public function test_we_can_check_if_model_has_permission()
    {
        $user = User::factory()->create();

        $permissionName = fake()->unique()->word();
        Permission::factory()->withName($permissionName)->create();

        $user->assignPermission($permissionName);

        $this->assertTrue($user->hasPermission($permissionName));
    }

    public function test_it_returns_false_if_permission_was_revoked()
    {
        $user = User::factory()->create();

        $permissionName = fake()->unique()->word();
        Permission::factory()->withName($permissionName)->create();

        $user->assignPermission($permissionName);
        $user->revokePermission($permissionName);

        $this->assertFalse($user->hasPermission($permissionName));
    }

    public function test_it_returns_false_if_permission_is_inactive()
    {
        $user = User::factory()->create();

        $permissionName = fake()->unique()->word();
        Permission::factory()->withName($permissionName)->inactive()->create();

        $user->assignPermission($permissionName);

        $this->assertFalse($user->hasPermission($permissionName));
    }
}
