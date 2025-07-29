<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Services;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Exceptions\Model\ModelDoesNotInteractWithRolesException;
use Gillyware\Gatekeeper\Exceptions\Role\RoleAlreadyExistsException;
use Gillyware\Gatekeeper\Exceptions\Role\RolesFeatureDisabledException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Models\ModelHasRole;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket;
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
    }

    public function test_role_exists()
    {
        $role = Role::factory()->create();

        $this->assertTrue($this->service->exists($role->name));
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

        $this->assertInstanceOf(RolePacket::class, $role);
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

        /** @var AuditLog<User, Role> $createRoleLog */
        $createRoleLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::CreateRole->value, $createRoleLog->action);
        $this->assertEquals($name, $createRoleLog->metadata['name']);
        $this->assertTrue($this->user->is($createRoleLog->actionBy));
        $this->assertEquals($role->id, $createRoleLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_role_creation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $name = fake()->unique()->word();

        $this->service->create($name);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_update_role_name()
    {
        $role = Role::factory()->create();
        $newName = fake()->unique()->word();

        $updatedRole = $this->service->updateName($role, $newName);

        $this->assertInstanceOf(RolePacket::class, $updatedRole);
        $this->assertEquals($newName, $updatedRole->name);
    }

    public function test_update_role_name_fails_if_roles_feature_disabled()
    {
        Config::set('gatekeeper.features.roles.enabled', false);

        $name = fake()->unique()->word();
        $role = Role::factory()->withName($name)->create();

        $this->expectException(RolesFeatureDisabledException::class);
        $this->service->updateName($role, 'new-name');

        $this->assertSame($name, $role->fresh()->name);
    }

    public function test_audit_log_inserted_on_role_name_update_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $role = Role::factory()->create();
        $oldName = $role->name;
        $newName = fake()->unique()->word();

        $this->service->updateName($role, $newName);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Role> $updateRoleLog */
        $updateRoleLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::UpdateRoleName->value, $updateRoleLog->action);
        $this->assertEquals($oldName, $updateRoleLog->metadata['old_name']);
        $this->assertEquals($newName, $updateRoleLog->metadata['name']);
        $this->assertEquals($this->user->id, $updateRoleLog->actionBy->id);
        $this->assertEquals($role->id, $updateRoleLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_role_name_update_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $role = Role::factory()->create();
        $newName = fake()->unique()->word();

        $this->service->updateName($role, $newName);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_grant_role_by_default()
    {
        $role = Role::factory()->create();

        $role = $this->service->grantByDefault($role);

        $this->assertInstanceOf(RolePacket::class, $role);
        $this->assertTrue($role->grantedByDefault);
    }

    public function test_grant_role_by_default_fails_if_roles_feature_disabled()
    {
        Config::set('gatekeeper.features.roles.enabled', false);

        $role = Role::factory()->create();

        $this->expectException(RolesFeatureDisabledException::class);
        $this->service->grantByDefault($role);

        $this->assertFalse($role->fresh()->grant_by_default);
    }

    public function test_grant_role_by_default_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $role = Role::factory()->create();

        $this->service->grantByDefault($role);
        $this->service->grantByDefault($role);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_audit_log_inserted_on_grant_role_by_default_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $role = Role::factory()->create();

        $this->service->grantByDefault($role);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Role> $grantByDefaultRoleLog */
        $grantByDefaultRoleLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::GrantRoleByDefault->value, $grantByDefaultRoleLog->action);
        $this->assertEquals($role->name, $grantByDefaultRoleLog->metadata['name']);
        $this->assertEquals($this->user->id, $grantByDefaultRoleLog->actionBy->id);
        $this->assertEquals($role->id, $grantByDefaultRoleLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_grant_role_by_default_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $role = Role::factory()->create();

        $this->service->grantByDefault($role);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_revoke_role_default_grant()
    {
        $role = Role::factory()->grantByDefault()->create();

        $role = $this->service->revokeDefaultGrant($role);

        $this->assertInstanceOf(RolePacket::class, $role);
        $this->assertFalse($role->grantedByDefault);
    }

    public function test_revoke_role_default_grant_succeeds_if_roles_feature_disabled()
    {
        Config::set('gatekeeper.features.roles.enabled', false);

        $role = Role::factory()->grantByDefault()->create();
        $role = $this->service->revokeDefaultGrant($role);

        $this->assertFalse($role->grantedByDefault);
    }

    public function test_revoke_role_default_grant_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $role = Role::factory()->grantByDefault()->create();

        $role = $this->service->revokeDefaultGrant($role);
        $role = $this->service->revokeDefaultGrant($role);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_audit_log_inserted_on_revoke_role_default_grant_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $role = Role::factory()->grantByDefault()->create();

        $this->service->revokeDefaultGrant($role);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Role> $revokeDefaultGrantAuditLog */
        $revokeDefaultGrantAuditLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::RevokeRoleDefaultGrant->value, $revokeDefaultGrantAuditLog->action);
        $this->assertEquals($role->name, $revokeDefaultGrantAuditLog->metadata['name']);
        $this->assertEquals($this->user->id, $revokeDefaultGrantAuditLog->actionBy->id);
        $this->assertEquals($role->id, $revokeDefaultGrantAuditLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_revoke_role_default_grant_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $role = Role::factory()->grantByDefault()->create();

        $this->service->revokeDefaultGrant($role);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_deactivate_role()
    {
        $role = Role::factory()->create();

        $role = $this->service->deactivate($role);

        $this->assertInstanceOf(RolePacket::class, $role);
        $this->assertFalse($role->isActive);
    }

    public function test_deactivate_role_succeeds_if_roles_feature_disabled()
    {
        Config::set('gatekeeper.features.roles.enabled', false);

        $role = Role::factory()->create();
        $role = $this->service->deactivate($role);

        $this->assertFalse($role->isActive);
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

        /** @var AuditLog<User, Role> $deactivateRoleLog */
        $deactivateRoleLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::DeactivateRole->value, $deactivateRoleLog->action);
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

        $this->assertInstanceOf(RolePacket::class, $role);
        $this->assertTrue($role->isActive);
    }

    public function test_reactivate_role_fails_if_roles_feature_disabled()
    {
        Config::set('gatekeeper.features.roles.enabled', false);

        $role = Role::factory()->inactive()->create();

        $this->expectException(RolesFeatureDisabledException::class);
        $this->service->reactivate($role);

        $this->assertFalse($role->fresh()->is_active);
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

        /** @var AuditLog<User, Role> $reactivateRoleLog */
        $reactivateRoleLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::ReactivateRole->value, $reactivateRoleLog->action);
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

    public function test_delete_role_deletes_assignments_if_role_assigned_to_model()
    {
        $role = Role::factory()->create();
        $user = User::factory()->create();

        $this->service->assignToModel($user, $role);

        $this->service->delete($role);

        $this->assertFalse($this->service->exists($role));
        $this->assertCount(0, $this->service->getDirectForModel($user));
    }

    public function test_audit_log_inserted_on_role_deletion_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $role = Role::factory()->create();

        $this->service->delete($role);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Role> $deleteRoleLog */
        $deleteRoleLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::DeleteRole->value, $deleteRoleLog->action);
        $this->assertEquals($role->name, $deleteRoleLog->metadata['name']);
        $this->assertEquals($this->user->id, $deleteRoleLog->actionBy->id);
        $this->assertEquals($role->id, $deleteRoleLog->action_to_model_id);
    }

    public function test_audit_log_not_inserted_on_role_deletion_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $role = Role::factory()->create();

        $this->service->delete($role);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_assign_role()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->assertTrue($this->service->assignToModel($user, $role));
        $this->assertTrue($user->hasRole($role));
    }

    public function test_assign_role_is_idempotent()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->assertTrue($this->service->assignToModel($user, $role));
        $this->assertTrue($this->service->assignToModel($user, $role));
        $this->assertTrue($user->hasRole($role));

        $this->assertCount(1, AuditLog::all());
        $this->assertCount(1, ModelHasRole::all());
    }

    public function test_audit_log_inserted_on_role_assignment_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->service->assignToModel($user, $role);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, User> $assignRoleLog */
        $assignRoleLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::AssignRole->value, $assignRoleLog->action);
        $this->assertEquals($role->name, $assignRoleLog->metadata['name']);
        $this->assertEquals($this->user->id, $assignRoleLog->actionBy->id);
        $this->assertEquals($user->id, $assignRoleLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_role_assignment_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->service->assignToModel($user, $role);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_assign_duplicate_role_is_ignored()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->assertTrue($this->service->assignToModel($user, $role));
        $this->assertTrue($this->service->assignToModel($user, $role));
    }

    public function test_assign_multiple_roles()
    {
        $user = User::factory()->create();
        $roles = Role::factory()->count(3)->create();

        $this->assertTrue($this->service->assignAllToModel($user, $roles));

        $this->assertTrue($user->hasAllRoles($roles));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_role_assignment()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $roles = Role::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $roles);

        $auditLogs = AuditLog::all();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_unassign_role()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->service->assignToModel($user, $role);

        $this->assertTrue($this->service->unassignFromModel($user, $role));
        $this->assertFalse($user->hasRole($role));
    }

    public function test_audit_log_inserted_on_role_unassignment_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->service->assignToModel($user, $role);
        $this->service->unassignFromModel($user, $role);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::UnassignRole->value)->get();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Role> $unassignRoleLog */
        $unassignRoleLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::UnassignRole->value, $unassignRoleLog->action);
        $this->assertEquals($role->name, $unassignRoleLog->metadata['name']);
        $this->assertEquals($this->user->id, $unassignRoleLog->actionBy->id);
        $this->assertEquals($user->id, $unassignRoleLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_role_unassignment_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->service->assignToModel($user, $role);
        $this->service->unassignFromModel($user, $role);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_unassign_multiple_roles()
    {
        $user = User::factory()->create();
        $roles = Role::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $roles);

        $this->assertTrue($this->service->unassignAllFromModel($user, $roles));

        $this->assertFalse($user->hasAnyRole($roles));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_role_unassignment()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $roles = Role::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $roles);

        $this->service->unassignAllFromModel($user, $roles);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::UnassignRole->value)->get();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_deny_role()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->service->assignToModel($user, $role);

        $this->assertTrue($this->service->denyFromModel($user, $role));
        $this->assertDatabaseHas((new ModelHasRole)->getTable(), [
            'model_id' => $user->id,
            'denied' => true,
        ]);
        $this->assertFalse($user->hasRole($role));
    }

    public function test_audit_log_inserted_on_role_denial_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->service->denyFromModel($user, $role);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::DenyRole->value)->get();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, User> $denyRoleLog */
        $denyRoleLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::DenyRole->value, $denyRoleLog->action);
        $this->assertEquals($role->name, $denyRoleLog->metadata['name']);
        $this->assertEquals($this->user->id, $denyRoleLog->actionBy->id);
        $this->assertEquals($user->id, $denyRoleLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_role_denial_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->service->denyFromModel($user, $role);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_deny_multiple_roles()
    {
        $user = User::factory()->create();
        $roles = Role::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $roles);
        $this->service->denyAllFromModel($user, $roles);

        $this->assertFalse($user->hasAnyRole($roles));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_role_denial()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $roles = Role::factory()->count(3)->create();

        $this->service->denyAllFromModel($user, $roles);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::DenyRole->value)->get();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_undeny_role()
    {
        $user = User::factory()->create();
        $role = Role::factory()->grantByDefault()->create();

        $this->service->denyFromModel($user, $role);
        $this->service->undenyFromModel($user, $role);

        $this->assertTrue($this->service->undenyFromModel($user, $role));

        $this->assertEmpty(ModelHasRole::query()->where([
            'model_id' => $user->id,
            'denied' => true,
        ])->get());

        $this->assertTrue($user->hasRole($role));
    }

    public function test_audit_log_inserted_on_role_undenial_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->service->undenyFromModel($user, $role);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::UndenyRole->value)->get();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, User> $undenyRoleLog */
        $undenyRoleLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::UndenyRole->value, $undenyRoleLog->action);
        $this->assertEquals($role->name, $undenyRoleLog->metadata['name']);
        $this->assertEquals($this->user->id, $undenyRoleLog->actionBy->id);
        $this->assertEquals($user->id, $undenyRoleLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_role_undenial_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->service->undenyFromModel($user, $role);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_undeny_multiple_roles()
    {
        $user = User::factory()->create();
        $roles = Role::factory()->grantByDefault()->count(3)->create();

        $this->service->denyAllFromModel($user, $roles);
        $this->service->undenyAllFromModel($user, $roles);

        $this->assertTrue($user->hasAnyRole($roles));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_role_undenial()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $roles = Role::factory()->count(3)->create();

        $this->service->denyAllFromModel($user, $roles);
        $this->service->undenyAllFromModel($user, $roles);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::UndenyRole->value)->get();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_model_has_role_direct()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->service->assignToModel($user, $role);

        $this->assertTrue($this->service->modelHas($user, $role));
    }

    public function test_model_has_role_through_team()
    {
        Config::set('gatekeeper.features.teams.enabled', true);

        $user = User::factory()->create();
        $role = Role::factory()->create();
        $team = Team::factory()->create();

        $team->assignRole($role);
        $user->addToTeam($team);

        $this->assertTrue($this->service->modelHas($user, $role));
    }

    public function test_model_does_not_have_role_granted_by_default_when_denied()
    {
        $user = User::factory()->create();
        $role = Role::factory()->grantByDefault()->create();

        $user->denyRole($role);

        $this->assertFalse($this->service->modelHas($user, $role));
    }

    public function test_model_has_role_when_granted_by_default()
    {
        $user = User::factory()->create();
        $role = Role::factory()->grantByDefault()->create();

        $this->assertTrue($this->service->modelHas($user, $role));
    }

    public function test_model_does_not_have_role_through_team_role_when_denied()
    {
        Config::set('gatekeeper.features.teams.enabled', true);

        $user = User::factory()->create();
        $role = Role::factory()->create();
        $team = Team::factory()->create();

        $team->assignRole($role);
        $user->addToTeam($team);

        $user->denyRole($role);

        $this->assertFalse($this->service->modelHas($user, $role));
    }

    public function test_model_has_any_role()
    {
        $user = User::factory()->create();
        $roles = Role::factory()->count(3)->create();

        $this->service->assignToModel($user, $roles->first());

        $this->assertTrue($this->service->modelHasAny($user, $roles));
    }

    public function test_model_has_all_roles()
    {
        $user = User::factory()->create();
        $roles = Role::factory()->count(2)->create();

        $this->service->assignAllToModel($user, $roles);

        $this->assertTrue($this->service->modelHasAll($user, $roles));

        $this->service->unassignFromModel($user, $roles->last());

        $this->assertFalse($this->service->modelHasAll($user, $roles));
    }

    public function test_model_has_returns_false_if_role_inactive()
    {
        $user = User::factory()->create();
        $role = Role::factory()->inactive()->create();

        $this->service->assignToModel($user, $role);

        $this->assertFalse($this->service->modelHas($user, $role));
    }

    public function test_model_has_returns_false_if_team_inactive()
    {
        Config::set('gatekeeper.features.teams.enabled', true);

        $user = User::factory()->create();
        $role = Role::factory()->create();
        $team = Team::factory()->inactive()->create();

        $team->assignRole($role);
        $user->addToTeam($team);

        $this->assertFalse($this->service->modelHas($user, $role));
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

        $this->assertInstanceOf(RolePacket::class, $found);
        $this->assertEquals($role->id, $found->id);
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
        $this->assertContainsOnlyInstancesOf(RolePacket::class, $roles);
    }

    public function test_get_direct_roles_for_model()
    {
        $user = User::factory()->create();

        $directRoles = Role::factory()->count(2)->create();
        $unrelatedRole = Role::factory()->create();

        $this->service->assignAllToModel($user, $directRoles);

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
        $team->assignRole($teamRole);
        $user->addToTeam($team);

        $user->assignRole($directRole);

        $effective = $this->service->getForModel($user);

        $this->assertCount(2, $effective);
        $this->assertTrue($effective->contains('id', $directRole->id));
        $this->assertTrue($effective->contains('id', $teamRole->id));
    }
}
