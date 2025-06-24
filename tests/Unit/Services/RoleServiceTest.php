<?php

namespace Braxey\Gatekeeper\Tests\Unit\Services;

use Braxey\Gatekeeper\Exceptions\ModelDoesNotInteractWithRolesException;
use Braxey\Gatekeeper\Exceptions\RolesFeatureDisabledException;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Services\RoleService;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class RoleServiceTest extends TestCase
{
    protected RoleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.roles', true);

        $this->service = app(RoleService::class);
    }

    public function test_create_role()
    {
        $name = fake()->unique()->word();

        $role = $this->service->create($name);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($name, $role->name);
    }

    public function test_assign_and_revoke_role()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Role::factory()->withName($name)->create();

        $this->assertTrue($this->service->assignToModel($user, $name));
        $this->assertTrue($user->hasRole($name));

        $this->assertTrue($this->service->revokeFromModel($user, $name));
        $this->assertFalse($user->hasRole($name));
    }

    public function test_assign_duplicate_role_is_ignored()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Role::factory()->withName($name)->create();

        $this->assertTrue($this->service->assignToModel($user, $name));
        $this->assertTrue($this->service->assignToModel($user, $name));
    }

    public function test_assign_multiple_roles()
    {
        $user = User::factory()->create();
        $roles = Role::factory()->count(3)->create();

        $this->assertTrue($this->service->assignMultipleToModel($user, $roles));

        $this->assertTrue($user->hasAllRoles($roles));
    }

    public function test_revoke_multiple_roles()
    {
        $user = User::factory()->create();
        $roles = Role::factory()->count(3)->create();

        $this->service->assignMultipleToModel($user, $roles);

        $this->assertTrue($this->service->revokeMultipleFromModel($user, $roles));

        $this->assertFalse($user->hasAnyRole($roles));
    }

    public function test_model_has_role_direct()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        $role = Role::factory()->withName($name)->create();

        $this->service->assignToModel($user, $role);

        $this->assertTrue($this->service->modelHas($user, $role));
    }

    public function test_model_has_role_through_team()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $role = Role::factory()->create();
        $team = Team::factory()->create();

        $team->roles()->attach($role);
        $user->teams()->attach($team);

        $this->assertTrue($this->service->modelHas($user, $role->name));
    }

    public function test_model_has_any_role()
    {
        $user = User::factory()->create();
        $roles = Role::factory()->count(3)->create();
        $names = $roles->pluck('name');

        $this->service->assignToModel($user, $names[1]);

        $this->assertTrue($this->service->modelHasAny($user, $names));
    }

    public function test_model_has_all_roles()
    {
        $user = User::factory()->create();
        $roles = Role::factory()->count(2)->create();
        $names = $roles->pluck('name');

        $this->service->assignMultipleToModel($user, $names);

        $this->assertTrue($this->service->modelHasAll($user, $names));

        $this->service->revokeFromModel($user, $names[0]);

        $this->assertFalse($this->service->modelHasAll($user, $names));
    }

    public function test_model_has_returns_false_if_role_inactive()
    {
        $user = User::factory()->create();
        $role = Role::factory()->inactive()->create();

        $this->service->assignToModel($user, $role->name);

        $this->assertFalse($this->service->modelHas($user, $role->name));
    }

    public function test_model_has_returns_false_if_team_inactive()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $role = Role::factory()->create();
        $team = Team::factory()->inactive()->create();

        $team->roles()->attach($role);
        $user->teams()->attach($team);

        $this->assertFalse($this->service->modelHas($user, $role->name));
    }

    public function test_throws_if_model_does_not_use_trait()
    {
        $model = new class extends \Illuminate\Database\Eloquent\Model
        {
            protected $table = 'users';
        };

        $this->expectException(ModelDoesNotInteractWithRolesException::class);

        $this->service->assignToModel($model, 'any');
    }

    public function test_throws_if_roles_feature_disabled()
    {
        Config::set('gatekeeper.features.roles', false);

        $this->expectException(RolesFeatureDisabledException::class);

        $user = User::factory()->create();
        $this->service->assignToModel($user, 'any');
    }
}
