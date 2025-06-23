<?php

namespace Braxey\Gatekeeper\Tests\Unit;

use Braxey\Gatekeeper\Models\ModelHasPermission;
use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;

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

        $modelHasPermissions = ModelHasPermission::forModel($user)
            ->where('permission_id', $permission->id)
            ->withTrashed()
            ->get();

        $this->assertTrue($result);
        $this->assertCount(1, $modelHasPermissions);
    }

    public function test_we_can_assign_multiple_permissions()
    {
        $user = User::factory()->create();
        $permissions = collect([
            Permission::factory()->withName($name1 = fake()->unique()->word())->create(),
            Permission::factory()->withName($name2 = fake()->unique()->word())->create(),
        ]);

        $user->assignPermissions([$name1, $name2]);

        foreach ($permissions as $permission) {
            $this->assertDatabaseHas('model_has_permissions', [
                'permission_id' => $permission->id,
                'model_id' => $user->id,
                'model_type' => $user->getMorphClass(),
            ]);
        }
    }

    public function test_we_can_assign_multiple_permissions_with_arrayable()
    {
        $user = User::factory()->create();
        $permissions = collect([
            Permission::factory()->withName($name1 = fake()->unique()->word())->create(),
            Permission::factory()->withName($name2 = fake()->unique()->word())->create(),
        ]);

        $user->assignPermissions(collect([$name1, $name2]));

        foreach ($permissions as $permission) {
            $this->assertDatabaseHas('model_has_permissions', [
                'permission_id' => $permission->id,
                'model_id' => $user->id,
                'model_type' => $user->getMorphClass(),
            ]);
        }
    }

    public function test_we_can_revoke_a_permission()
    {
        $user = User::factory()->create();
        $permissionName = fake()->unique()->word();
        $permission = Permission::factory()->withName($permissionName)->create();

        $user->assignPermission($permissionName);
        $user->revokePermission($permissionName);

        $this->assertSoftDeleted('model_has_permissions', [
            'permission_id' => $permission->id,
            'model_id' => $user->id,
        ]);
    }

    public function test_we_can_revoke_multiple_duplicates()
    {
        $user = User::factory()->create();
        $permissionName = fake()->unique()->word();
        $permission = Permission::factory()->withName($permissionName)->create();

        // Force multiple entries
        $user->assignPermission($permissionName);
        ModelHasPermission::create([
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'permission_id' => $permission->id,
        ]);

        $user->revokePermission($permissionName);

        $this->assertCount(2, ModelHasPermission::withTrashed()->where('permission_id', $permission->id)->get());
        $this->assertCount(2, ModelHasPermission::onlyTrashed()->where('permission_id', $permission->id)->get());
    }

    public function test_we_can_revoke_multiple_permissions()
    {
        $user = User::factory()->create();
        $permissions = collect([
            Permission::factory()->withName($name1 = fake()->unique()->word())->create(),
            Permission::factory()->withName($name2 = fake()->unique()->word())->create(),
        ]);

        $user->assignPermissions([$name1, $name2]);
        $user->revokePermissions([$name1, $name2]);

        foreach ($permissions as $permission) {
            $this->assertSoftDeleted('model_has_permissions', [
                'permission_id' => $permission->id,
                'model_id' => $user->id,
            ]);
        }
    }

    public function test_we_can_revoke_multiple_permissions_with_arrayable()
    {
        $user = User::factory()->create();
        $permissions = collect([
            Permission::factory()->withName($name1 = fake()->unique()->word())->create(),
            Permission::factory()->withName($name2 = fake()->unique()->word())->create(),
        ]);

        $user->assignPermissions(collect([$name1, $name2]));
        $user->revokePermissions(collect([$name1, $name2]));

        foreach ($permissions as $permission) {
            $this->assertSoftDeleted('model_has_permissions', [
                'permission_id' => $permission->id,
                'model_id' => $user->id,
            ]);
        }
    }

    public function test_we_can_check_if_model_has_permission()
    {
        $user = User::factory()->create();
        $permissionName = fake()->unique()->word();
        Permission::factory()->withName($permissionName)->create();

        $user->assignPermission($permissionName);

        $this->assertTrue($user->hasPermission($permissionName));
    }

    public function test_it_returns_false_if_permission_is_inactive()
    {
        $user = User::factory()->create();
        $permissionName = fake()->unique()->word();
        Permission::factory()->withName($permissionName)->inactive()->create();

        $user->assignPermission($permissionName);

        $this->assertFalse($user->hasPermission($permissionName));
    }

    public function test_it_returns_false_if_permission_is_revoked()
    {
        $user = User::factory()->create();
        $permissionName = fake()->unique()->word();
        Permission::factory()->withName($permissionName)->create();

        $user->assignPermission($permissionName);
        $user->revokePermission($permissionName);

        $this->assertFalse($user->hasPermission($permissionName));
    }

    public function test_it_returns_true_if_permission_is_granted_through_role()
    {
        Config::set('gatekeeper.features.roles', true);

        $user = User::factory()->create();
        $permissionName = fake()->unique()->word();
        $permission = Permission::factory()->withName($permissionName)->create();

        $role = Role::factory()->create();
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->assertTrue($user->hasPermission($permissionName));
    }

    public function test_it_returns_false_when_no_roles_have_permission()
    {
        Config::set('gatekeeper.features.roles', true);

        $user = User::factory()->create();
        $permissionName = fake()->unique()->word();
        Permission::factory()->withName($permissionName)->create();

        $role = Role::factory()->create();
        $user->roles()->attach($role);

        $this->assertFalse($user->hasPermission($permissionName));
    }

    public function test_it_returns_false_if_user_does_not_have_permission_and_roles_are_disabled()
    {
        Config::set('gatekeeper.features.roles', false);

        $user = User::factory()->create();

        $permissionName = fake()->unique()->word();
        Permission::factory()->withName($permissionName)->create();

        $this->assertFalse($user->hasPermission($permissionName));
    }

    public function test_it_returns_true_if_permission_is_granted_through_team()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $team = Team::factory()->create();
        $permissionName = fake()->unique()->word();
        $permission = Permission::factory()->withName($permissionName)->create();

        $team->permissions()->attach($permission);
        $user->teams()->attach($team);

        $this->assertTrue($user->hasPermission($permissionName));
    }

    public function test_it_returns_true_if_permission_is_granted_through_team_role()
    {
        Config::set('gatekeeper.features.teams', true);
        Config::set('gatekeeper.features.roles', true);

        $user = User::factory()->create();
        $team = Team::factory()->create();
        $role = Role::factory()->create();
        $permissionName = fake()->unique()->word();
        $permission = Permission::factory()->withName($permissionName)->create();

        $role->permissions()->attach($permission);
        $team->roles()->attach($role);
        $user->teams()->attach($team);

        $this->assertTrue($user->hasPermission($permissionName));
    }

    public function test_it_returns_false_if_team_is_inactive()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $team = Team::factory()->inactive()->create();
        $permissionName = fake()->unique()->word();
        $permission = Permission::factory()->withName($permissionName)->create();

        $team->permissions()->attach($permission);
        $user->teams()->attach($team);

        $this->assertFalse($user->hasPermission($permissionName));
    }

    public function test_it_returns_false_if_team_role_is_inactive()
    {
        Config::set('gatekeeper.features.teams', true);
        Config::set('gatekeeper.features.roles', true);

        $user = User::factory()->create();
        $team = Team::factory()->create();
        $role = Role::factory()->inactive()->create();
        $permissionName = fake()->unique()->word();
        $permission = Permission::factory()->withName($permissionName)->create();

        $role->permissions()->attach($permission);
        $team->roles()->attach($role);
        $user->teams()->attach($team);

        $this->assertFalse($user->hasPermission($permissionName));
    }

    public function test_it_returns_false_if_teams_feature_is_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $user = User::factory()->create();
        $team = Team::factory()->create();
        $permissionName = fake()->unique()->word();
        $permission = Permission::factory()->withName($permissionName)->create();

        $team->permissions()->attach($permission);
        $user->teams()->attach($team);

        $this->assertFalse($user->hasPermission($permissionName));
    }

    public function test_it_returns_false_if_team_does_not_have_permission()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $team = Team::factory()->create();
        $permissionName = fake()->unique()->word();
        Permission::factory()->withName($permissionName)->create();

        $user->teams()->attach($team);

        $this->assertFalse($user->hasPermission($permissionName));
    }

    public function test_has_any_permission_returns_true_if_one_matches()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word(), fake()->unique()->word()];

        foreach ($names as $name) {
            Permission::factory()->withName($name)->create();
        }

        $user->assignPermission($names[1]);

        $this->assertTrue($user->hasAnyPermission($names));
    }

    public function test_has_any_permission_returns_true_if_one_matches_with_arrayable()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word(), fake()->unique()->word()];

        foreach ($names as $name) {
            Permission::factory()->withName($name)->create();
        }

        $user->assignPermission($names[1]);

        $this->assertTrue($user->hasAnyPermission(collect($names)));
    }

    public function test_has_any_permission_returns_false_if_none_match()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];

        foreach ($names as $name) {
            Permission::factory()->withName($name)->create();
        }

        $this->assertFalse($user->hasAnyPermission($names));
    }

    public function test_has_all_permissions_returns_false_if_any_are_missing()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        Permission::factory()->withName($names[0])->create();
        Permission::factory()->withName($names[1])->create();

        $user->assignPermission($names[0]);

        $this->assertFalse($user->hasAllPermissions($names));
    }

    public function test_has_all_permissions_returns_true_if_all_match()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        foreach ($names as $name) {
            Permission::factory()->withName($name)->create();
        }

        $user->assignPermissions($names);

        $this->assertTrue($user->hasAllPermissions($names));
    }

    public function test_has_all_permissions_returns_true_if_all_match_with_arrayable()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        foreach ($names as $name) {
            Permission::factory()->withName($name)->create();
        }

        $user->assignPermissions($names);

        $this->assertTrue($user->hasAllPermissions(collect($names)));
    }

    public function test_it_throws_if_permission_does_not_exist()
    {
        $user = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        $user->assignPermission('nonexistent_permission');
    }
}
