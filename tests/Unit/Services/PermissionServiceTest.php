<?php

namespace Braxey\Gatekeeper\Tests\Unit\Services;

use Braxey\Gatekeeper\Constants\AuditLog\Action;
use Braxey\Gatekeeper\Exceptions\ModelDoesNotInteractWithPermissionsException;
use Braxey\Gatekeeper\Facades\Gatekeeper;
use Braxey\Gatekeeper\Models\AuditLog;
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

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.audit', true);

        $this->user = User::factory()->create();
        Gatekeeper::setActor($this->user);

        $this->service = app(PermissionService::class);
        $this->service->actingAs($this->user);
    }

    public function test_create_permission()
    {
        $name = fake()->unique()->word();

        $permission = $this->service->create($name);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals($name, $permission->name);
    }

    public function test_audit_log_inserted_on_permission_creation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit', true);

        $name = fake()->unique()->word();

        $this->service->create($name);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $createPermissionLog = $auditLogs->first();
        $this->assertEquals(Action::PERMISSION_CREATE, $createPermissionLog->action);
        $this->assertEquals($name, $createPermissionLog->metadata['name']);
        $this->assertTrue($this->user->is($createPermissionLog->actionBy));
        $this->assertNull($createPermissionLog->actionTo);
    }

    public function test_audit_log_not_inserted_on_permission_creation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit', false);

        $name = fake()->unique()->word();

        $this->service->create($name);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_assign_permission()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Permission::factory()->withName($name)->create();

        $this->assertTrue($this->service->assignToModel($user, $name));
        $this->assertTrue($user->hasPermission($name));
    }

    public function test_audit_log_inserted_on_permission_assignment_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit', true);

        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Permission::factory()->withName($name)->create();

        $this->service->assignToModel($user, $name);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $assignPermissionLog = $auditLogs->first();
        $this->assertEquals(Action::PERMISSION_ASSIGN, $assignPermissionLog->action);
        $this->assertEquals($name, $assignPermissionLog->metadata['name']);
        $this->assertEquals($this->user->id, $assignPermissionLog->actionBy->id);
        $this->assertEquals($user->id, $assignPermissionLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_permission_assignment_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit', false);

        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Permission::factory()->withName($name)->create();

        $this->service->assignToModel($user, $name);

        $this->assertCount(0, AuditLog::all());
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

        $this->assertTrue($this->service->assignMultipleToModel($user, $permissions));
        $this->assertTrue($this->service->modelHasAll($user, $permissions));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_permission_assignment()
    {
        Config::set('gatekeeper.features.audit', true);

        $user = User::factory()->create();
        $permissions = Permission::factory()->count(3)->create();

        $this->service->assignMultipleToModel($user, $permissions);

        $auditLogs = AuditLog::all();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_revoke_permission()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Permission::factory()->withName($name)->create();

        $this->service->assignToModel($user, $name);

        $this->assertTrue($this->service->revokeFromModel($user, $name));
        $this->assertSoftDeleted(Config::get('gatekeeper.tables.model_has_permissions'), [
            'model_id' => $user->id,
        ]);
        $this->assertFalse($user->hasPermission($name));
    }

    public function test_audit_log_inserted_on_permission_revocation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit', true);

        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Permission::factory()->withName($name)->create();

        $this->service->assignToModel($user, $name);
        $this->service->revokeFromModel($user, $name);

        $auditLogs = AuditLog::query()->where('action', Action::PERMISSION_REVOKE)->get();
        $this->assertCount(1, $auditLogs);

        $assignPermissionLog = $auditLogs->first();
        $this->assertEquals(Action::PERMISSION_REVOKE, $assignPermissionLog->action);
        $this->assertEquals($name, $assignPermissionLog->metadata['name']);
        $this->assertEquals($this->user->id, $assignPermissionLog->actionBy->id);
        $this->assertEquals($user->id, $assignPermissionLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_permission_revocation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit', false);

        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Permission::factory()->withName($name)->create();

        $this->service->assignToModel($user, $name);
        $this->service->revokeFromModel($user, $name);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_revoke_multiple_permissions()
    {
        $user = User::factory()->create();
        $permissions = Permission::factory()->count(3)->create();

        $this->service->assignMultipleToModel($user, $permissions);

        $this->service->revokeMultipleFromModel($user, $permissions);

        $this->assertFalse($user->hasAnyPermission($permissions));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_permission_revocation()
    {
        Config::set('gatekeeper.features.audit', true);

        $user = User::factory()->create();
        $permissions = Permission::factory()->count(3)->create();

        $this->service->assignMultipleToModel($user, $permissions);

        $this->service->revokeMultipleFromModel($user, $permissions);

        $auditLogs = AuditLog::query()->where('action', Action::PERMISSION_REVOKE)->get();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
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
