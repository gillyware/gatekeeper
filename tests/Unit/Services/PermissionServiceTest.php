<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Services;

use Gillyware\Gatekeeper\Constants\Action;
use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Exceptions\Model\ModelDoesNotInteractWithPermissionsException;
use Gillyware\Gatekeeper\Exceptions\Permission\PermissionAlreadyExistsException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Models\ModelHasPermission;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\PermissionPacket;
use Gillyware\Gatekeeper\Services\PermissionService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class PermissionServiceTest extends TestCase
{
    protected PermissionService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.audit.enabled', true);

        $this->user = User::factory()->create();
        Gatekeeper::setActor($this->user);

        $this->service = app(PermissionService::class);
        $this->service->actingAs($this->user);
    }

    public function test_permission_exists()
    {
        $name = fake()->unique()->word();

        Permission::factory()->withName($name)->create();

        $this->assertTrue($this->service->exists($name));
    }

    public function test_permission_does_not_exist()
    {
        $name = fake()->unique()->word();

        $this->assertFalse($this->service->exists($name));
    }

    public function test_create_permission()
    {
        $name = fake()->unique()->word();

        $permission = $this->service->create($name);

        $this->assertInstanceOf(PermissionPacket::class, $permission);
        $this->assertEquals($name, $permission->getName());
    }

    public function test_create_fails_if_permission_already_exists()
    {
        $existing = Permission::factory()->create();

        $this->expectException(PermissionAlreadyExistsException::class);

        $this->service->create($existing->name);
    }

    public function test_audit_log_inserted_on_permission_creation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $name = fake()->unique()->word();

        $permission = $this->service->create($name);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $createPermissionLog = $auditLogs->first();
        $this->assertEquals(Action::PERMISSION_CREATE, $createPermissionLog->action);
        $this->assertEquals($name, $createPermissionLog->metadata['name']);
        $this->assertTrue($this->user->is($createPermissionLog->actionBy));
        $this->assertEquals($permission->getId(), $createPermissionLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_permission_creation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $name = fake()->unique()->word();

        $this->service->create($name);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_update_permission()
    {
        $name = fake()->unique()->word();
        $newName = fake()->unique()->word();

        $permission = Permission::factory()->withName($name)->create();

        $updatedPermission = $this->service->update($permission, $newName);

        $this->assertInstanceOf(PermissionPacket::class, $updatedPermission);
        $this->assertEquals($newName, $updatedPermission->getName());
    }

    public function test_audit_log_inserted_on_permission_update_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $name = fake()->unique()->word();
        $newName = fake()->unique()->word();

        $permission = Permission::factory()->withName($name)->create();

        $permission = $this->service->update($permission, $newName);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $updatePermissionLog = $auditLogs->first();
        $this->assertEquals(Action::PERMISSION_UPDATE, $updatePermissionLog->action);
        $this->assertEquals($name, $updatePermissionLog->metadata['old_name']);
        $this->assertEquals($newName, $updatePermissionLog->metadata['name']);
        $this->assertTrue($this->user->is($updatePermissionLog->actionBy));
        $this->assertEquals($permission->getId(), $updatePermissionLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_permission_update_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $name = fake()->unique()->word();
        $newName = fake()->unique()->word();

        $permission = Permission::factory()->withName($name)->create();

        $this->service->update($permission, $newName);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_deactivate_permission()
    {
        $name = fake()->unique()->word();
        $permission = Permission::factory()->withName($name)->create();

        $permission = $this->service->deactivate($permission);

        $this->assertInstanceOf(PermissionPacket::class, $permission);
        $this->assertFalse($permission->isActive());
    }

    public function test_deactivate_permission_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $permission = Permission::factory()->create();

        $this->service->deactivate($permission);
        $this->service->deactivate($permission);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_audit_log_inserted_on_permission_deactivation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $name = fake()->unique()->word();
        $permission = Permission::factory()->withName($name)->create();

        $permission = $this->service->deactivate($permission);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $deactivatePermissionLog = $auditLogs->first();
        $this->assertEquals(Action::PERMISSION_DEACTIVATE, $deactivatePermissionLog->action);
        $this->assertEquals($name, $deactivatePermissionLog->metadata['name']);
        $this->assertTrue($this->user->is($deactivatePermissionLog->actionBy));
        $this->assertEquals($permission->getId(), $deactivatePermissionLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_permission_deactivation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $name = fake()->unique()->word();
        $permission = Permission::factory()->withName($name)->create();

        $this->service->deactivate($permission);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_reactivate_permission()
    {
        $name = fake()->unique()->word();
        $permission = Permission::factory()->withName($name)->inactive()->create();

        $permission = $this->service->reactivate($permission);

        $this->assertInstanceOf(PermissionPacket::class, $permission);
        $this->assertTrue($permission->isActive());
    }

    public function test_reactivate_permission_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $permission = Permission::factory()->inactive()->create();

        $this->service->reactivate($permission);
        $this->service->reactivate($permission);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_audit_log_inserted_on_permission_reactivation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $name = fake()->unique()->word();
        $permission = Permission::factory()->withName($name)->inactive()->create();

        $permission = $this->service->reactivate($permission);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $reactivatePermissionLog = $auditLogs->first();
        $this->assertEquals(Action::PERMISSION_REACTIVATE, $reactivatePermissionLog->action);
        $this->assertEquals($name, $reactivatePermissionLog->metadata['name']);
        $this->assertTrue($this->user->is($reactivatePermissionLog->actionBy));
        $this->assertEquals($permission->getId(), $reactivatePermissionLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_permission_reactivation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $name = fake()->unique()->word();
        $permission = Permission::factory()->withName($name)->inactive()->create();

        $this->service->reactivate($permission);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_delete_permission()
    {
        $name = fake()->unique()->word();
        $permission = Permission::factory()->withName($name)->create();

        $deleted = $this->service->delete($permission);

        $this->assertTrue($deleted);
        $this->assertSoftDeleted($permission);
    }

    public function test_delete_permission_deletes_assignments_if_permission_is_assigned_to_model()
    {
        $permission = Permission::factory()->create();
        $user = User::factory()->create();

        $this->service->assignToModel($user, $permission);

        $this->service->delete($permission);

        $this->assertFalse($this->service->exists($permission));
        $this->assertCount(0, $this->service->getDirectForModel($user));
    }

    public function test_audit_log_inserted_on_permission_deletion_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $name = fake()->unique()->word();
        $permission = Permission::factory()->withName($name)->create();

        $this->service->delete($permission);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $deletePermissionLog = $auditLogs->first();
        $this->assertEquals(Action::PERMISSION_DELETE, $deletePermissionLog->action);
        $this->assertEquals($name, $deletePermissionLog->metadata['name']);
        $this->assertEquals($permission->id, $deletePermissionLog->action_to_model_id);
        $this->assertTrue($this->user->is($deletePermissionLog->actionBy));
    }

    public function test_audit_log_not_inserted_on_permission_deletion_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $name = fake()->unique()->word();
        $permission = Permission::factory()->withName($name)->create();

        $this->service->delete($permission);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_assign_permission()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->assertTrue($this->service->assignToModel($user, $permission));
        $this->assertTrue($user->hasPermission($permission));
    }

    public function test_assign_permission_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->assertTrue($this->service->assignToModel($user, $permission));
        $this->assertTrue($this->service->assignToModel($user, $permission));
        $this->assertTrue($user->hasPermission($permission));

        $this->assertCount(1, AuditLog::all());
        $this->assertCount(1, ModelHasPermission::all());
    }

    public function test_audit_log_inserted_on_permission_assignment_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->service->assignToModel($user, $permission->name);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $assignPermissionLog = $auditLogs->first();
        $this->assertEquals(Action::PERMISSION_ASSIGN, $assignPermissionLog->action);
        $this->assertEquals($permission->name, $assignPermissionLog->metadata['name']);
        $this->assertEquals($this->user->id, $assignPermissionLog->actionBy->id);
        $this->assertEquals($user->id, $assignPermissionLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_permission_assignment_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->service->assignToModel($user, $permission);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_assign_multiple_permissions()
    {
        $user = User::factory()->create();
        $permissions = Permission::factory()->count(3)->create();

        $this->assertTrue($this->service->assignAllToModel($user, $permissions));

        $permissions->each(fn (Permission $permission) => $this->assertTrue($user->hasPermission($permission)));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_permission_assignment()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $permissions = Permission::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $permissions);

        $auditLogs = AuditLog::all();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_revoke_permission()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->service->assignToModel($user, $permission);

        $this->assertTrue($this->service->revokeFromModel($user, $permission));
        $this->assertSoftDeleted(Config::get('gatekeeper.tables.model_has_permissions', GatekeeperConfigDefault::TABLES_MODEL_HAS_PERMISSIONS), [
            'model_id' => $user->id,
        ]);
        $this->assertFalse($user->hasPermission($permission));
    }

    public function test_audit_log_inserted_on_permission_revocation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->service->assignToModel($user, $permission);
        $this->service->revokeFromModel($user, $permission);

        $auditLogs = AuditLog::query()->where('action', Action::PERMISSION_REVOKE)->get();
        $this->assertCount(1, $auditLogs);

        $assignPermissionLog = $auditLogs->first();
        $this->assertEquals(Action::PERMISSION_REVOKE, $assignPermissionLog->action);
        $this->assertEquals($permission->name, $assignPermissionLog->metadata['name']);
        $this->assertEquals($this->user->id, $assignPermissionLog->actionBy->id);
        $this->assertEquals($user->id, $assignPermissionLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_permission_revocation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->service->assignToModel($user, $permission);
        $this->service->revokeFromModel($user, $permission);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_revoke_multiple_permissions()
    {
        $user = User::factory()->create();
        $permissions = Permission::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $permissions);
        $this->service->revokeAllFromModel($user, $permissions);

        $this->assertFalse($user->hasAnyPermission($permissions));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_permission_revocation()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $permissions = Permission::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $permissions);
        $this->service->revokeAllFromModel($user, $permissions);

        $auditLogs = AuditLog::query()->where('action', Action::PERMISSION_REVOKE)->get();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_model_has_direct_permission()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->service->assignToModel($user, $permission);

        $this->assertTrue($this->service->modelHas($user, $permission));
    }

    public function test_model_does_not_have_permission_if_inactive()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->inactive()->create();

        $this->service->assignToModel($user, $permission);

        $this->assertFalse($this->service->modelHas($user, $permission));
    }

    public function test_model_has_permission_through_role()
    {
        Config::set('gatekeeper.features.roles.enabled', true);

        $user = User::factory()->create();
        $permission = Permission::factory()->create();
        $role = Role::factory()->create();

        $role->assignPermission($permission);
        $user->assignRole($role);

        $this->assertTrue($this->service->modelHas($user, $permission));
    }

    public function test_model_has_permission_through_team_permission()
    {
        Config::set('gatekeeper.features.teams.enabled', true);

        $user = User::factory()->create();
        $permission = Permission::factory()->create();
        $team = Team::factory()->create();

        $team->assignPermission($permission);
        $user->addToTeam($team);

        $this->assertTrue($this->service->modelHas($user, $permission));
    }

    public function test_model_has_permission_through_team_role_permission()
    {
        Config::set('gatekeeper.features.teams.enabled', true);
        Config::set('gatekeeper.features.roles.enabled', true);

        $user = User::factory()->create();
        $permission = Permission::factory()->create();
        $team = Team::factory()->create();
        $role = Role::factory()->create();

        $role->assignPermission($permission);
        $team->assignRole($role);
        $user->addToTeam($team);

        $this->assertTrue($this->service->modelHas($user, $permission));
    }

    public function test_model_has_any_permission()
    {
        $user = User::factory()->create();
        $permissions = Permission::factory()->count(2)->create();

        $this->service->assignToModel($user, $permissions->first());

        $this->assertTrue($this->service->modelHasAny($user, $permissions));
    }

    public function test_model_has_all_permissions()
    {
        $user = User::factory()->create();
        $permissions = Permission::factory()->count(2)->create();

        $this->service->assignAllToModel($user, $permissions);

        $this->assertTrue($this->service->modelHasAll($user, $permissions));

        $this->service->revokeFromModel($user, $permissions->last());

        $this->assertFalse($this->service->modelHasAll($user, $permissions));
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

    public function test_find_by_name_returns_permission_if_found()
    {
        $permission = Permission::factory()->create();

        $found = $this->service->findByName($permission->name);

        $this->assertInstanceOf(PermissionPacket::class, $found);
        $this->assertEquals($permission->id, $found->getId());
    }

    public function test_find_by_name_returns_null_if_not_found()
    {
        $found = $this->service->findByName('nonexistent-permission');

        $this->assertNull($found);
    }

    public function test_get_all_permissions_returns_collection()
    {
        Permission::factory()->count(3)->create();

        $permissions = $this->service->getAll();

        $this->assertCount(3, $permissions);
        $this->assertInstanceOf(Collection::class, $permissions);
        $this->assertContainsOnlyInstancesOf(PermissionPacket::class, $permissions);
    }

    public function test_get_direct_permissions_for_model()
    {
        $user = User::factory()->create();

        $directPermissions = Permission::factory()->count(2)->create();
        $unrelatedPermission = Permission::factory()->create();

        $this->service->assignAllToModel($user, $directPermissions);

        $direct = $this->service->getDirectForModel($user);

        $this->assertCount(2, $direct);
        $this->assertTrue($direct->contains('id', $directPermissions[0]->id));
        $this->assertTrue($direct->contains('id', $directPermissions[1]->id));
        $this->assertFalse($direct->contains('id', $unrelatedPermission->id));
    }

    public function test_get_effective_permissions_for_model()
    {
        Config::set('gatekeeper.features.roles.enabled', true);
        Config::set('gatekeeper.features.teams.enabled', true);

        $user = User::factory()->create();
        $directPermission = Permission::factory()->create();
        $rolePermission = Permission::factory()->create();
        $teamPermission = Permission::factory()->create();

        $role = Role::factory()->create();
        $role->assignPermission($rolePermission);
        $user->assignRole($role);

        $team = Team::factory()->create();
        $team->assignPermission($teamPermission);
        $user->addToTeam($team);

        $this->service->assignToModel($user, $directPermission);

        $effective = $this->service->getForModel($user);

        $this->assertCount(3, $effective);
        $this->assertTrue($effective->contains('id', $directPermission->id));
        $this->assertTrue($effective->contains('id', $rolePermission->id));
        $this->assertTrue($effective->contains('id', $teamPermission->id));
    }
}
