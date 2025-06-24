<?php

namespace Braxey\Gatekeeper\Tests\Unit;

use Braxey\Gatekeeper\Exceptions\ModelDoesNotInteractWithPermissionsException;
use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Services\PermissionService;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class PermissionServiceTest extends TestCase
{
    protected PermissionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PermissionService::class);
    }

    public function test_create_permission()
    {
        $name = fake()->unique()->word();

        $permission = $this->service->create($name);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals($name, $permission->name);
    }

    public function test_assign_and_revoke_permission()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Permission::factory()->withName($name)->create();

        $this->assertTrue($this->service->assignToModel($user, $name));
        $this->assertTrue($user->hasPermission($name));

        $this->assertTrue($this->service->revokeFromModel($user, $name));
        $this->assertSoftDeleted('model_has_permissions', [
            'model_id' => $user->id,
        ]);
        $this->assertFalse($user->hasPermission($name));
    }

    public function test_assign_multiple_permissions()
    {
        $user = User::factory()->create();
        $permissions = Permission::factory()->count(3)->create();
        $names = $permissions->pluck('name')->toArray();

        $this->assertTrue($this->service->assignMultipleToModel($user, $names));

        $permissions->each(function (Permission $permission) use ($user) {
            $this->assertTrue($user->hasPermission($permission->name));
        });
    }

    public function test_assign_multiple_permissions_from_arrayable()
    {
        $user = User::factory()->create();
        $permissions = Permission::factory()->count(3)->create();
        $names = $permissions->pluck('name');

        $this->assertTrue($this->service->assignMultipleToModel($user, $names));

        $this->assertTrue($user->hasAllPermissions($names));
    }

    public function test_revoke_multiple_permissions()
    {
        $user = User::factory()->create();
        $permissions = Permission::factory()->count(3)->create();
        $names = $permissions->pluck('name');

        $this->service->assignMultipleToModel($user, $names);

        $this->service->revokeMultipleFromModel($user, $names);

        $this->assertFalse($user->hasAnyPermission($names));
    }

    public function test_model_has_direct_permission()
    {
        $user = User::factory()->create();
        $name = fake()->word();

        Permission::factory()->withName($name)->create();
        $this->service->assignToModel($user, $name);

        $this->assertTrue($this->service->modelHas($user, $name));
    }

    public function test_model_does_not_have_permission_if_inactive()
    {
        $user = User::factory()->create();
        $name = fake()->word();

        Permission::factory()->withName($name)->inactive()->create();
        $this->service->assignToModel($user, $name);

        $this->assertFalse($this->service->modelHas($user, $name));
    }

    public function test_model_has_permission_through_role()
    {
        Config::set('gatekeeper.features.roles', true);

        $user = User::factory()->create();
        $perm = Permission::factory()->create();
        $role = Role::factory()->create();

        $role->permissions()->attach($perm);
        $user->roles()->attach($role);

        $this->assertTrue($this->service->modelHas($user, $perm->name));
    }

    public function test_model_has_permission_through_team_permission()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $perm = Permission::factory()->create();
        $team = Team::factory()->create();

        $team->permissions()->attach($perm);
        $user->teams()->attach($team);

        $this->assertTrue($this->service->modelHas($user, $perm->name));
    }

    public function test_model_has_permission_through_team_role_permission()
    {
        Config::set('gatekeeper.features.teams', true);
        Config::set('gatekeeper.features.roles', true);

        $user = User::factory()->create();
        $perm = Permission::factory()->create();
        $team = Team::factory()->create();
        $role = Role::factory()->create();

        $role->permissions()->attach($perm);
        $team->roles()->attach($role);
        $user->teams()->attach($team);

        $this->assertTrue($this->service->modelHas($user, $perm->name));
    }

    public function test_model_has_any_permission()
    {
        $user = User::factory()->create();
        $permissions = Permission::factory()->count(2)->create();
        $names = $permissions->pluck('name');

        $this->service->assignToModel($user, $names[1]);

        $this->assertTrue($this->service->modelHasAny($user, $names));
    }

    public function test_model_has_all_permissions()
    {
        $user = User::factory()->create();
        $permissions = Permission::factory()->count(2)->create();
        $names = $permissions->pluck('name');

        $this->service->assignMultipleToModel($user, $names);

        $this->assertTrue($this->service->modelHasAll($user, $names));

        $this->service->revokeFromModel($user, $names[0]);

        $this->assertFalse($this->service->modelHasAll($user, $names));
    }

    public function test_it_throws_if_model_does_not_interact_with_permissions()
    {
        $model = new class extends \Illuminate\Database\Eloquent\Model
        {
            protected $table = 'users';
        };

        $this->expectException(ModelDoesNotInteractWithPermissionsException::class);

        $this->service->assignToModel($model, 'any');
    }
}
