<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Services;

use Gillyware\Gatekeeper\Constants\Action;
use Gillyware\Gatekeeper\Exceptions\Model\ModelDoesNotInteractWithRolesException;
use Gillyware\Gatekeeper\Exceptions\Role\DeletingAssignedRoleException;
use Gillyware\Gatekeeper\Exceptions\Role\RoleAlreadyExistsException;
use Gillyware\Gatekeeper\Exceptions\Role\RolesFeatureDisabledException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Models\ModelHasRole;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Services\RoleService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class RoleServiceTest extends TestCase
{
    protected RoleService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.roles.enabled', true);

        $this->user = User::factory()->create();
        Gatekeeper::setActor($this->user);

        $this->service = app(RoleService::class);
        $this->service->actingAs($this->user);
    }

    public function test_role_exists()
    {
        $name = fake()->unique()->word();
        Role::factory()->withName($name)->create();

        $this->assertTrue($this->service->exists($name));
    }

    public function test_role_does_not_exist()
    {
        $name = fake()->unique()->word();

        $this->assertFalse($this->service->exists($name));
    }

    public function test_create_role()
    {
        $name = fake()->unique()->word();

        $role = $this->service->create($name);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($name, $role->name);
    }

    public function test_create_fails_if_role_already_exists()
    {
        $existing = Role::factory()->create();

        $this->expectException(RoleAlreadyExistsException::class);

        $this->service->create($existing->name);
    }

    public function test_create_role_fails_if_roles_feature_disabled()
    {
        Config::set('gatekeeper.features.roles.enabled', false);

        $name = fake()->unique()->word();

        $this->expectException(RolesFeatureDisabledException::class);
        $this->service->create($name);
    }

    public function test_audit_log_inserted_on_role_creation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $name = fake()->unique()->word();

        $role = $this->service->create($name);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $createRoleLog = $auditLogs->first();
        $this->assertEquals(Action::ROLE_CREATE, $createRoleLog->action);
        $this->assertEquals($name, $createRoleLog->metadata['name']);
        $this->assertTrue($this->user->is($createRoleLog->actionBy));
        $this->assertTrue($role->is($createRoleLog->actionTo));
    }

    public function test_audit_log_not_inserted_on_role_creation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $name = fake()->unique()->word();

        $this->service->create($name);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_update_role()
    {
        $role = Role::factory()->create();
        $newName = fake()->unique()->word();

        $updatedRole = $this->service->update($role, $newName);

        $this->assertInstanceOf(Role::class, $updatedRole);
        $this->assertEquals($newName, $updatedRole->name);
    }

    public function test_update_role_fails_if_roles_feature_disabled()
    {
        Config::set('gatekeeper.features.roles.enabled', false);

        $name = fake()->unique()->word();
        $role = Role::factory()->withName($name)->create();

        $this->expectException(RolesFeatureDisabledException::class);
        $this->service->update($role, 'new-name');

        $this->assertSame($name, $role->fresh()->name);
    }

    public function test_audit_log_inserted_on_role_update_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $role = Role::factory()->create();
        $oldName = $role->name;
        $newName = fake()->unique()->word();

        $this->service->update($role, $newName);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $updateRoleLog = $auditLogs->first();
        $this->assertEquals(Action::ROLE_UPDATE, $updateRoleLog->action);
        $this->assertEquals($oldName, $updateRoleLog->metadata['old_name']);
        $this->assertEquals($newName, $updateRoleLog->metadata['name']);
        $this->assertEquals($this->user->id, $updateRoleLog->actionBy->id);
        $this->assertEquals($role->id, $updateRoleLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_role_update_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $role = Role::factory()->create();
        $newName = fake()->unique()->word();

        $this->service->update($role, $newName);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_deactivate_role()
    {
        $role = Role::factory()->create();

        $role = $this->service->deactivate($role);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertFalse($role->is_active);
        $this->assertFalse($role->fresh()->is_active);
    }

    public function test_deactivate_role_succeeds_if_roles_feature_disabled()
    {
        Config::set('gatekeeper.features.roles.enabled', false);

        $role = Role::factory()->create();
        $role = $this->service->deactivate($role);

        $this->assertFalse($role->is_active);
    }

    public function test_deactivate_role_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $role = Role::factory()->create();

        $role = $this->service->deactivate($role);
        $role = $this->service->deactivate($role);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_audit_log_inserted_on_role_deactivation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $role = Role::factory()->create();

        $this->service->deactivate($role);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $deactivateRoleLog = $auditLogs->first();
        $this->assertEquals(Action::ROLE_DEACTIVATE, $deactivateRoleLog->action);
        $this->assertEquals($role->name, $deactivateRoleLog->metadata['name']);
        $this->assertEquals($this->user->id, $deactivateRoleLog->actionBy->id);
        $this->assertEquals($role->id, $deactivateRoleLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_role_deactivation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $role = Role::factory()->create();

        $this->service->deactivate($role);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_reactivate_role()
    {
        $role = Role::factory()->inactive()->create();

        $role = $this->service->reactivate($role);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertTrue($role->is_active);
        $this->assertTrue($role->fresh()->is_active);
    }

    public function test_reactivate_role_fails_if_roles_feature_disabled()
    {
        Config::set('gatekeeper.features.roles.enabled', false);

        $role = Role::factory()->inactive()->create();

        $this->expectException(RolesFeatureDisabledException::class);
        $this->service->reactivate($role);

        $this->assertFalse($role->fresh()->active);
    }

    public function test_reactivate_role_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $role = Role::factory()->inactive()->create();

        $this->service->reactivate($role);
        $this->service->reactivate($role);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_audit_log_inserted_on_role_reactivation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $role = Role::factory()->inactive()->create();

        $this->service->reactivate($role);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $reactivateRoleLog = $auditLogs->first();
        $this->assertEquals(Action::ROLE_REACTIVATE, $reactivateRoleLog->action);
        $this->assertEquals($role->name, $reactivateRoleLog->metadata['name']);
        $this->assertEquals($this->user->id, $reactivateRoleLog->actionBy->id);
        $this->assertEquals($role->id, $reactivateRoleLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_role_reactivation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $role = Role::factory()->inactive()->create();

        $this->service->reactivate($role);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_delete_role()
    {
        $name = fake()->unique()->word();
        $role = Role::factory()->withName($name)->create();

        $deleted = $this->service->delete($role);

        $this->assertTrue($deleted);
        $this->assertSoftDeleted($role);
    }

    public function test_delete_role_fails_if_role_assigned_to_model()
    {
        $name = fake()->unique()->word();
        $role = Role::factory()->withName($name)->create();

        $user = User::factory()->create();
        $this->service->assignToModel($user, $name);

        $this->expectException(DeletingAssignedRoleException::class);
        $this->service->delete($role);

        $this->assertTrue($this->service->exists($name));
    }

    public function test_audit_log_inserted_on_role_deletion_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $name = fake()->unique()->word();
        $role = Role::factory()->withName($name)->create();

        $this->service->delete($role);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $deleteRoleLog = $auditLogs->first();
        $this->assertEquals(Action::ROLE_DELETE, $deleteRoleLog->action);
        $this->assertEquals($name, $deleteRoleLog->metadata['name']);
        $this->assertEquals($this->user->id, $deleteRoleLog->actionBy->id);
        $this->assertEquals($role->id, $deleteRoleLog->action_to_model_id);
    }

    public function test_audit_log_not_inserted_on_role_deletion_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $name = fake()->unique()->word();
        $role = Role::factory()->withName($name)->create();

        $this->service->delete($role);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_assign_role()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Role::factory()->withName($name)->create();

        $this->assertTrue($this->service->assignToModel($user, $name));
        $this->assertTrue($user->hasRole($name));
    }

    public function test_assign_role_is_idempotent()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Role::factory()->withName($name)->create();

        $this->assertTrue($this->service->assignToModel($user, $name));
        $this->assertTrue($this->service->assignToModel($user, $name));
        $this->assertTrue($user->hasRole($name));

        $this->assertCount(1, AuditLog::all());
        $this->assertCount(1, ModelHasRole::all());
    }

    public function test_audit_log_inserted_on_role_assignment_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Role::factory()->withName($name)->create();

        $this->service->assignToModel($user, $name);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $assignRoleLog = $auditLogs->first();
        $this->assertEquals(Action::ROLE_ASSIGN, $assignRoleLog->action);
        $this->assertEquals($name, $assignRoleLog->metadata['name']);
        $this->assertEquals($this->user->id, $assignRoleLog->actionBy->id);
        $this->assertEquals($user->id, $assignRoleLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_role_assignment_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Role::factory()->withName($name)->create();

        $this->service->assignToModel($user, $name);

        $this->assertCount(0, AuditLog::all());
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

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_role_assignment()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $roles = Role::factory()->count(3)->create();

        $this->service->assignMultipleToModel($user, $roles);

        $auditLogs = AuditLog::all();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_revoke_role()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Role::factory()->withName($name)->create();

        $this->service->assignToModel($user, $name);

        $this->assertTrue($this->service->revokeFromModel($user, $name));
        $this->assertFalse($user->hasRole($name));
    }

    public function test_audit_log_inserted_on_role_revocation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Role::factory()->withName($name)->create();

        $this->service->assignToModel($user, $name);
        $this->service->revokeFromModel($user, $name);

        $auditLogs = AuditLog::query()->where('action', Action::ROLE_REVOKE)->get();
        $this->assertCount(1, $auditLogs);

        $assignRoleLog = $auditLogs->first();
        $this->assertEquals(Action::ROLE_REVOKE, $assignRoleLog->action);
        $this->assertEquals($name, $assignRoleLog->metadata['name']);
        $this->assertEquals($this->user->id, $assignRoleLog->actionBy->id);
        $this->assertEquals($user->id, $assignRoleLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_role_revocation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Role::factory()->withName($name)->create();

        $this->service->assignToModel($user, $name);
        $this->service->revokeFromModel($user, $name);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_revoke_multiple_roles()
    {
        $user = User::factory()->create();
        $roles = Role::factory()->count(3)->create();

        $this->service->assignMultipleToModel($user, $roles);

        $this->assertTrue($this->service->revokeMultipleFromModel($user, $roles));

        $this->assertFalse($user->hasAnyRole($roles));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_role_revocation()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $roles = Role::factory()->count(3)->create();

        $this->service->assignMultipleToModel($user, $roles);

        $this->service->revokeMultipleFromModel($user, $roles);

        $auditLogs = AuditLog::query()->where('action', Action::ROLE_REVOKE)->get();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
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
        Config::set('gatekeeper.features.teams.enabled', true);

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
        Config::set('gatekeeper.features.teams.enabled', true);

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
        Config::set('gatekeeper.features.roles.enabled', false);

        $this->expectException(RolesFeatureDisabledException::class);

        $user = User::factory()->create();
        $this->service->assignToModel($user, 'any');
    }

    public function test_find_by_name_returns_role_if_found()
    {
        $role = Role::factory()->create();

        $found = $this->service->findByName($role->name);

        $this->assertInstanceOf(Role::class, $found);
        $this->assertTrue($role->is($found));
    }

    public function test_find_by_name_returns_null_if_not_found()
    {
        $found = $this->service->findByName('nonexistent-role');

        $this->assertNull($found);
    }

    public function test_get_all_roles_returns_collection()
    {
        Role::factory()->count(3)->create();

        $roles = $this->service->getAll();

        $this->assertCount(3, $roles);
        $this->assertInstanceOf(Collection::class, $roles);
    }

    public function test_get_direct_roles_for_model()
    {
        $user = User::factory()->create();

        $directRoles = Role::factory()->count(2)->create();
        $unrelatedRole = Role::factory()->create();

        $this->service->assignMultipleToModel($user, $directRoles);

        $direct = $this->service->getDirectForModel($user);

        $this->assertCount(2, $direct);
        $this->assertTrue($direct->contains('id', $directRoles[0]->id));
        $this->assertTrue($direct->contains('id', $directRoles[1]->id));
        $this->assertFalse($direct->contains('id', $unrelatedRole->id));
    }

    public function test_get_effective_roles_for_model()
    {
        Config::set('gatekeeper.features.roles.enabled', true);
        Config::set('gatekeeper.features.teams.enabled', true);

        $user = User::factory()->create();
        $directRole = Role::factory()->create();
        $teamRole = Role::factory()->create();

        $team = Team::factory()->create();
        $team->roles()->attach($teamRole);
        $user->teams()->attach($team);

        $this->service->assignToModel($user, $directRole);

        $effective = $this->service->getEffectiveForModel($user);

        $this->assertCount(2, $effective);
        $this->assertTrue($effective->contains('id', $directRole->id));
        $this->assertTrue($effective->contains('id', $teamRole->id));
    }
}
