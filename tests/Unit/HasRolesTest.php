<?php

namespace Braxey\Gatekeeper\Tests\Unit;

use Braxey\Gatekeeper\Models\ModelHasRole;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class HasRolesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.roles', true);
    }

    public function test_we_can_assign_a_role()
    {
        $user = User::factory()->create();
        $roleName = fake()->unique()->word();
        $role = Role::factory()->withName($roleName)->create();

        $result = $user->assignRole($roleName);

        $this->assertTrue($result);
        $this->assertDatabaseHas('model_has_roles', [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'role_id' => $role->id,
            'deleted_at' => null,
        ]);
    }

    public function test_assigning_duplicate_role_does_not_duplicate()
    {
        $user = User::factory()->create();
        $roleName = fake()->unique()->word();
        $role = Role::factory()->withName($roleName)->create();

        $user->assignRole($roleName);
        $result = $user->assignRole($roleName);

        $this->assertTrue($result);

        $this->assertCount(1, ModelHasRole::forModel($user)
            ->where('role_id', $role->id)
            ->withTrashed()
            ->get());
    }

    public function test_we_can_assign_multiple_roles()
    {
        $user = User::factory()->create();
        $roles = collect([
            Role::factory()->withName($name1 = fake()->unique()->word())->create(),
            Role::factory()->withName($name2 = fake()->unique()->word())->create(),
        ]);

        $user->assignRoles([$name1, $name2]);

        foreach ($roles as $role) {
            $this->assertDatabaseHas('model_has_roles', [
                'role_id' => $role->id,
                'model_id' => $user->id,
                'model_type' => $user->getMorphClass(),
            ]);
        }
    }

    public function test_we_can_assign_multiple_roles_with_arrayable()
    {
        $user = User::factory()->create();
        $roles = collect([
            Role::factory()->withName($name1 = fake()->unique()->word())->create(),
            Role::factory()->withName($name2 = fake()->unique()->word())->create(),
        ]);

        $user->assignRoles(collect([$name1, $name2]));

        foreach ($roles as $role) {
            $this->assertDatabaseHas('model_has_roles', [
                'role_id' => $role->id,
                'model_id' => $user->id,
                'model_type' => $user->getMorphClass(),
            ]);
        }
    }

    public function test_we_can_revoke_a_role()
    {
        $user = User::factory()->create();
        $roleName = fake()->unique()->word();
        $role = Role::factory()->withName($roleName)->create();

        $user->assignRole($roleName);
        $user->revokeRole($roleName);

        $this->assertSoftDeleted('model_has_roles', [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_we_can_revoke_multiple_roles()
    {
        $user = User::factory()->create();
        $roles = collect([
            Role::factory()->withName($name1 = fake()->unique()->word())->create(),
            Role::factory()->withName($name2 = fake()->unique()->word())->create(),
        ]);

        $user->assignRoles([$name1, $name2]);
        $user->revokeRoles([$name1, $name2]);

        foreach ($roles as $role) {
            $this->assertSoftDeleted('model_has_roles', [
                'role_id' => $role->id,
                'model_id' => $user->id,
            ]);
        }
    }

    public function test_we_can_revoke_multiple_roles_with_arrayable()
    {
        $user = User::factory()->create();
        $roles = collect([
            Role::factory()->withName($name1 = fake()->unique()->word())->create(),
            Role::factory()->withName($name2 = fake()->unique()->word())->create(),
        ]);

        $user->assignRoles(collect([$name1, $name2]));
        $user->revokeRoles(collect([$name1, $name2]));

        foreach ($roles as $role) {
            $this->assertSoftDeleted('model_has_roles', [
                'role_id' => $role->id,
                'model_id' => $user->id,
            ]);
        }
    }

    public function test_we_can_check_if_user_has_a_role()
    {
        $user = User::factory()->create();
        $roleName = fake()->unique()->word();
        Role::factory()->withName($roleName)->create();

        $user->assignRole($roleName);

        $this->assertTrue($user->hasRole($roleName));
    }

    public function test_has_role_returns_false_if_role_inactive()
    {
        $user = User::factory()->create();
        $roleName = fake()->unique()->word();
        Role::factory()->withName($roleName)->inactive()->create();

        $user->assignRole($roleName);

        $this->assertFalse($user->hasRole($roleName));
    }

    public function test_has_role_returns_false_if_role_missing()
    {
        $user = User::factory()->create();
        $roleName = fake()->unique()->word();
        Role::factory()->withName($roleName)->create();

        $this->assertFalse($user->hasRole($roleName));
    }

    public function test_has_role_returns_false_when_roles_feature_disabled()
    {
        Config::set('gatekeeper.features.roles', false);

        $user = User::factory()->create();
        $roleName = fake()->unique()->word();
        Role::factory()->withName($roleName)->create();

        $this->assertFalse($user->hasRole($roleName));
    }

    public function test_it_returns_true_if_role_is_granted_through_team()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $roleName = fake()->unique()->word();
        $role = Role::factory()->withName($roleName)->create();

        $team = Team::factory()->create();
        $team->roles()->attach($role);
        $user->teams()->attach($team);

        $this->assertTrue($user->hasRole($roleName));
    }

    public function test_it_returns_false_if_team_does_not_have_the_role()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $roleName = fake()->unique()->word();
        Role::factory()->withName($roleName)->create();

        $team = Team::factory()->create();
        $user->teams()->attach($team);

        $this->assertFalse($user->hasRole($roleName));
    }

    public function test_it_returns_false_if_all_teams_are_inactive_or_deleted()
    {
        Config::set('gatekeeper.features.teams', true);

        $user = User::factory()->create();
        $roleName = fake()->unique()->word();
        $role = Role::factory()->withName($roleName)->create();

        $team1 = Team::factory()->create(['is_active' => true]);
        $team1->roles()->attach($role);
        $team1->delete();
        $user->teams()->attach($team1);

        $team2 = Team::factory()->inactive()->create();
        $team2->roles()->attach($role);
        $user->teams()->attach($team2);

        $this->assertFalse($user->hasRole($roleName));
    }

    public function test_assign_role_throws_if_roles_disabled()
    {
        Config::set('gatekeeper.features.roles', false);

        $this->expectException(RuntimeException::class);

        $user = User::factory()->create();
        $roleName = fake()->unique()->word();
        Role::factory()->withName($roleName)->create();

        $user->assignRole($roleName);
    }

    public function test_revoke_role_throws_if_roles_disabled()
    {
        Config::set('gatekeeper.features.roles', false);

        $this->expectException(RuntimeException::class);

        $user = User::factory()->create();
        $roleName = fake()->unique()->word();
        Role::factory()->withName($roleName)->create();

        $user->revokeRole($roleName);
    }

    public function test_has_any_role_returns_true_if_one_matches()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word(), fake()->unique()->word()];

        foreach ($names as $name) {
            Role::factory()->withName($name)->create();
        }

        $user->assignRole($names[1]);

        $this->assertTrue($user->hasAnyRole($names));
    }

    public function test_has_any_role_returns_true_if_one_matches_with_arrayable()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word(), fake()->unique()->word()];

        foreach ($names as $name) {
            Role::factory()->withName($name)->create();
        }

        $user->assignRole($names[1]);

        $this->assertTrue($user->hasAnyRole(collect($names)));
    }

    public function test_has_any_role_returns_false_if_none_match()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];

        foreach ($names as $name) {
            Role::factory()->withName($name)->create();
        }

        $this->assertFalse($user->hasAnyRole($names));
    }

    public function test_has_all_roles_returns_false_if_any_are_missing()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        Role::factory()->withName($names[0])->create();
        Role::factory()->withName($names[1])->create();

        $user->assignRole($names[0]);

        $this->assertFalse($user->hasAllRoles($names));
    }

    public function test_has_all_roles_returns_true_if_all_match()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        foreach ($names as $name) {
            Role::factory()->withName($name)->create();
        }

        $user->assignRoles($names);

        $this->assertTrue($user->hasAllRoles($names));
    }

    public function test_has_all_roles_returns_true_if_all_match_with_arrayable()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        foreach ($names as $name) {
            Role::factory()->withName($name)->create();
        }

        $user->assignRoles(collect($names));

        $this->assertTrue($user->hasAllRoles(collect($names)));
    }

    public function test_it_throws_if_role_does_not_exist()
    {
        $this->expectException(ModelNotFoundException::class);

        $user = User::factory()->create();
        $user->assignRole('nonexistent');
    }
}
