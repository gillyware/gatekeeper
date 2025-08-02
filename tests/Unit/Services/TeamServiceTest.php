<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Services;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Exceptions\Model\ModelDoesNotInteractWithTeamsException;
use Gillyware\Gatekeeper\Exceptions\Team\TeamAlreadyExistsException;
use Gillyware\Gatekeeper\Exceptions\Team\TeamsFeatureDisabledException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Models\ModelHasTeam;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket;
use Gillyware\Gatekeeper\Services\TeamService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class TeamServiceTest extends TestCase
{
    protected TeamService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.teams.enabled', true);

        $this->user = User::factory()->create();
        Gatekeeper::setActor($this->user);

        $this->service = app(TeamService::class);
    }

    public function test_create_team()
    {
        $name = fake()->unique()->word();

        $team = $this->service->create($name);

        $this->assertInstanceOf(TeamPacket::class, $team);
        $this->assertEquals($name, $team->name);
    }

    public function test_create_team_fails_if_teams_feature_disabled()
    {
        Config::set('gatekeeper.features.teams.enabled', false);

        $this->expectException(TeamsFeatureDisabledException::class);

        $this->service->create(fake()->unique()->word());
    }

    public function test_create_fails_if_team_already_exists()
    {
        $existing = Team::factory()->create();

        $this->expectException(TeamAlreadyExistsException::class);

        $this->service->create($existing->name);
    }

    public function test_audit_log_inserted_on_team_creation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $name = fake()->unique()->word();

        $team = $this->service->create($name);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Team> $createTeamLog */
        $createTeamLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::CreateTeam->value, $createTeamLog->action);
        $this->assertEquals($name, $createTeamLog->metadata['name']);
        $this->assertTrue($this->user->is($createTeamLog->actionBy));
        $this->assertEquals($team->id, $createTeamLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_team_creation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $name = fake()->unique()->word();

        $this->service->create($name);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_update_team_name()
    {
        $team = Team::factory()->create();
        $newName = fake()->unique()->word();

        $updatedTeam = $this->service->updateName($team, $newName);

        $this->assertInstanceOf(TeamPacket::class, $updatedTeam);
        $this->assertEquals($newName, $updatedTeam->name);
    }

    public function test_update_team_name_fails_if_teams_feature_disabled()
    {
        Config::set('gatekeeper.features.teams.enabled', false);

        $this->expectException(TeamsFeatureDisabledException::class);

        $team = Team::factory()->create();
        $this->service->updateName($team, fake()->unique()->word());
    }

    public function test_audit_log_inserted_on_team_name_update_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $name = fake()->unique()->word();
        $team = Team::factory()->withName($name)->create();
        $newName = fake()->unique()->word();

        $this->service->updateName($team, $newName);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Team> $updateTeamLog */
        $updateTeamLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::UpdateTeamName->value, $updateTeamLog->action);
        $this->assertEquals($newName, $updateTeamLog->metadata['name']);
        $this->assertEquals($name, $updateTeamLog->metadata['old_name']);
        $this->assertTrue($this->user->is($updateTeamLog->actionBy));
        $this->assertTrue($team->is($updateTeamLog->actionTo));
    }

    public function test_audit_log_not_inserted_on_team_name_update_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $team = Team::factory()->create();
        $newName = fake()->unique()->word();

        $this->service->updateName($team, $newName);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_grant_team_by_default()
    {
        $team = Team::factory()->create();

        $team = $this->service->grantByDefault($team);

        $this->assertInstanceOf(TeamPacket::class, $team);
        $this->assertTrue($team->grantedByDefault);
    }

    public function test_grant_team_by_default_fails_if_teams_feature_disabled()
    {
        Config::set('gatekeeper.features.teams.enabled', false);

        $team = Team::factory()->create();

        $this->expectException(TeamsFeatureDisabledException::class);
        $this->service->grantByDefault($team);

        $this->assertFalse($team->fresh()->grant_by_default);
    }

    public function test_grant_team_by_default_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $team = Team::factory()->create();

        $this->service->grantByDefault($team);
        $this->service->grantByDefault($team);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_audit_log_inserted_on_grant_team_by_default_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $team = Team::factory()->create();

        $this->service->grantByDefault($team);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Team> $grantByDefaultTeamLog */
        $grantByDefaultTeamLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::GrantTeamByDefault->value, $grantByDefaultTeamLog->action);
        $this->assertEquals($team->name, $grantByDefaultTeamLog->metadata['name']);
        $this->assertEquals($this->user->id, $grantByDefaultTeamLog->actionBy->id);
        $this->assertEquals($team->id, $grantByDefaultTeamLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_grant_team_by_default_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $team = Team::factory()->create();

        $this->service->grantByDefault($team);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_revoke_team_default_grant()
    {
        $team = Team::factory()->grantByDefault()->create();

        $team = $this->service->revokeDefaultGrant($team);

        $this->assertInstanceOf(TeamPacket::class, $team);
        $this->assertFalse($team->grantedByDefault);
    }

    public function test_revoke_team_default_grant_succeeds_if_teams_feature_disabled()
    {
        Config::set('gatekeeper.features.teams.enabled', false);

        $team = Team::factory()->grantByDefault()->create();
        $team = $this->service->revokeDefaultGrant($team);

        $this->assertFalse($team->grantedByDefault);
    }

    public function test_revoke_team_default_grant_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $team = Team::factory()->grantByDefault()->create();

        $team = $this->service->revokeDefaultGrant($team);
        $team = $this->service->revokeDefaultGrant($team);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_audit_log_inserted_on_revoke_team_default_grant_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $team = Team::factory()->grantByDefault()->create();

        $this->service->revokeDefaultGrant($team);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Team> $revokeDefaultGrantAuditLog */
        $revokeDefaultGrantAuditLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::RevokeTeamDefaultGrant->value, $revokeDefaultGrantAuditLog->action);
        $this->assertEquals($team->name, $revokeDefaultGrantAuditLog->metadata['name']);
        $this->assertEquals($this->user->id, $revokeDefaultGrantAuditLog->actionBy->id);
        $this->assertEquals($team->id, $revokeDefaultGrantAuditLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_revoke_team_default_grant_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $team = Team::factory()->grantByDefault()->create();

        $this->service->revokeDefaultGrant($team);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_deactivate_team()
    {
        $team = Team::factory()->create();

        $deactivatedTeam = $this->service->deactivate($team);

        $this->assertInstanceOf(TeamPacket::class, $deactivatedTeam);
        $this->assertFalse($deactivatedTeam->isActive);
    }

    public function test_deactivate_team_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $team = Team::factory()->create();

        $this->service->deactivate($team);
        $this->service->deactivate($team);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_deactivate_team_succeeds_if_teams_feature_disabled()
    {
        Config::set('gatekeeper.features.teams.enabled', false);
        $team = Team::factory()->create();

        $deactivatedTeam = $this->service->deactivate($team);

        $this->assertInstanceOf(TeamPacket::class, $deactivatedTeam);
        $this->assertFalse($deactivatedTeam->isActive);
    }

    public function test_audit_log_inserted_on_team_deactivation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $team = Team::factory()->create();

        $this->service->deactivate($team);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Team> $deactivateTeamLog */
        $deactivateTeamLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::DeactivateTeam->value, $deactivateTeamLog->action);
        $this->assertEquals($team->name, $deactivateTeamLog->metadata['name']);
        $this->assertEquals($this->user->id, $deactivateTeamLog->actionBy->id);
        $this->assertEquals($team->id, $deactivateTeamLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_team_deactivation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $team = Team::factory()->create();

        $this->service->deactivate($team);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_reactivate_team()
    {
        $team = Team::factory()->inactive()->create();

        $reactivatedTeam = $this->service->reactivate($team);

        $this->assertInstanceOf(TeamPacket::class, $reactivatedTeam);
        $this->assertTrue($reactivatedTeam->isActive);
    }

    public function test_reactivate_team_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $team = Team::factory()->inactive()->create();

        $this->service->reactivate($team);
        $this->service->reactivate($team);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_reactivate_team_fails_if_teams_feature_disabled()
    {
        Config::set('gatekeeper.features.teams.enabled', false);

        $team = Team::factory()->inactive()->create();

        $this->expectException(TeamsFeatureDisabledException::class);
        $this->service->reactivate($team);
    }

    public function test_audit_log_inserted_on_team_reactivation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $team = Team::factory()->inactive()->create();

        $this->service->reactivate($team);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Team> $reactivateTeamLog */
        $reactivateTeamLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::ReactivateTeam->value, $reactivateTeamLog->action);
        $this->assertEquals($team->name, $reactivateTeamLog->metadata['name']);
        $this->assertEquals($this->user->id, $reactivateTeamLog->actionBy->id);
        $this->assertEquals($team->id, $reactivateTeamLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_team_reactivation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $team = Team::factory()->inactive()->create();

        $this->service->reactivate($team);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_delete_team()
    {
        $team = Team::factory()->create();

        $result = $this->service->delete($team);

        $this->assertTrue($result);
        $this->assertSoftDeleted($team);
    }

    public function test_delete_team_succeeds_if_teams_feature_disabled()
    {
        Config::set('gatekeeper.features.teams.enabled', false);

        $team = Team::factory()->create();

        $result = $this->service->delete($team);

        $this->assertTrue($result);
        $this->assertSoftDeleted($team);
    }

    public function test_delete_team_deletes_assignments_if_team_has_models()
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();

        $this->service->assignToModel($user, $team->name);

        $this->service->delete($team);

        $this->assertFalse($this->service->exists($team));
        $this->assertCount(0, $this->service->getDirectForModel($user));
    }

    public function test_audit_log_inserted_on_team_deletion_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $team = Team::factory()->create();

        $this->service->delete($team);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Team> $deleteTeamLog */
        $deleteTeamLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::DeleteTeam->value, $deleteTeamLog->action);
        $this->assertEquals($team->name, $deleteTeamLog->metadata['name']);
        $this->assertEquals($this->user->id, $deleteTeamLog->actionBy->id);
        $this->assertEquals($team->id, $deleteTeamLog->action_to_model_id);
    }

    public function test_audit_log_not_inserted_on_team_deletion_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $team = Team::factory()->create();

        $this->service->delete($team);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_add_model_to_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $result = $this->service->assignToModel($user, $team->name);

        $this->assertTrue($result);
        $this->assertDatabaseHas(Config::get('gatekeeper.tables.model_has_teams', GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS), [
            'model_id' => $user->id,
            'model_type' => $user->getMorphClass(),
            'team_id' => $team->id,
            'deleted_at' => null,
        ]);
    }

    public function test_assign_team_is_idempotent()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->assertTrue($this->service->assignToModel($user, $team));
        $this->assertTrue($this->service->assignToModel($user, $team));
        $this->assertTrue($user->onTeam($team));

        $this->assertCount(1, AuditLog::all());
        $this->assertCount(1, ModelHasTeam::all());
    }

    public function test_audit_log_inserted_on_team_assignment_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->assignToModel($user, $team);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, User> $assignTeamLog */
        $assignTeamLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::AssignTeam->value, $assignTeamLog->action);
        $this->assertEquals($team->name, $assignTeamLog->metadata['name']);
        $this->assertEquals($this->user->id, $assignTeamLog->actionBy->id);
        $this->assertEquals($user->id, $assignTeamLog->action_to_model_id);
    }

    public function test_audit_log_not_inserted_on_team_assignment_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->assignToModel($user, $team);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_add_model_to_team_is_idempotent()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->assignToModel($user, $team);
        $result = $this->service->assignToModel($user, $team);

        $this->assertTrue($result);
    }

    public function test_add_model_to_multiple_teams()
    {
        $user = User::factory()->create();
        $teams = Team::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $teams);

        $this->assertTrue($user->onAllTeams($teams));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_team_assignment()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $teams = Team::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $teams);

        $auditLogs = AuditLog::all();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_remove_model_from_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->assignToModel($user, $team);
        $result = $this->service->unassignFromModel($user, $team);

        $this->assertTrue($result);
        $this->assertSoftDeleted(Config::get('gatekeeper.tables.model_has_teams', GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS), [
            'team_id' => $team->id,
            'model_id' => $user->id,
        ]);
    }

    public function test_audit_log_inserted_on_team_unassignment_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->assignToModel($user, $team);
        $this->service->unassignFromModel($user, $team);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::UnassignTeam->value)->get();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, User> $unassignTeamLog */
        $unassignTeamLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::UnassignTeam->value, $unassignTeamLog->action);
        $this->assertEquals($team->name, $unassignTeamLog->metadata['name']);
        $this->assertEquals($this->user->id, $unassignTeamLog->actionBy->id);
        $this->assertEquals($user->id, $unassignTeamLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_team_unassignment_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->assignToModel($user, $team);
        $this->service->unassignFromModel($user, $team);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_remove_model_from_multiple_teams()
    {
        $user = User::factory()->create();
        $teams = Team::factory()->count(2)->create();

        $this->service->assignAllToModel($user, $teams);
        $result = $this->service->unassignAllFromModel($user, $teams);

        $this->assertTrue($result);
        $teams->each(function (Team $team) use ($user) {
            $this->assertSoftDeleted(Config::get('gatekeeper.tables.model_has_teams', GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS), [
                'team_id' => $team->id,
                'model_id' => $user->id,
            ]);
        });
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_team_unassignment()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $teams = Team::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $teams);

        $this->service->unassignAllFromModel($user, $teams);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::UnassignTeam->value)->get();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_deny_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->assignToModel($user, $team);

        $this->assertTrue($this->service->denyFromModel($user, $team));
        $this->assertDatabaseHas((new ModelHasTeam)->getTable(), [
            'model_id' => $user->id,
            'denied' => true,
        ]);
        $this->assertFalse($user->onTeam($team));
    }

    public function test_audit_log_inserted_on_team_denial_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->denyFromModel($user, $team);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::DenyTeam->value)->get();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, User> $denyTeamLog */
        $denyTeamLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::DenyTeam->value, $denyTeamLog->action);
        $this->assertEquals($team->name, $denyTeamLog->metadata['name']);
        $this->assertEquals($this->user->id, $denyTeamLog->actionBy->id);
        $this->assertEquals($user->id, $denyTeamLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_team_denial_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->denyFromModel($user, $team);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_deny_multiple_teams()
    {
        $user = User::factory()->create();
        $teams = Team::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $teams);
        $this->service->denyAllFromModel($user, $teams);

        $this->assertFalse($user->onAnyTeam($teams));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_team_denial()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $teams = Team::factory()->count(3)->create();

        $this->service->denyAllFromModel($user, $teams);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::DenyTeam->value)->get();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_undeny_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->grantByDefault()->create();

        $this->service->denyFromModel($user, $team);
        $this->service->undenyFromModel($user, $team);

        $this->assertTrue($this->service->undenyFromModel($user, $team));

        $this->assertEmpty(ModelHasTeam::query()->where([
            'model_id' => $user->id,
            'denied' => true,
        ])->get());

        $this->assertTrue($user->onTeam($team));
    }

    public function test_audit_log_inserted_on_team_undenial_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->undenyFromModel($user, $team);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::UndenyTeam->value)->get();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, User> $undenyTeamLog */
        $undenyTeamLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::UndenyTeam->value, $undenyTeamLog->action);
        $this->assertEquals($team->name, $undenyTeamLog->metadata['name']);
        $this->assertEquals($this->user->id, $undenyTeamLog->actionBy->id);
        $this->assertEquals($user->id, $undenyTeamLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_team_undenial_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->undenyFromModel($user, $team);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_undeny_multiple_teams()
    {
        $user = User::factory()->create();
        $teams = Team::factory()->grantByDefault()->count(3)->create();

        $this->service->denyAllFromModel($user, $teams);
        $this->service->undenyAllFromModel($user, $teams);

        $this->assertTrue($user->onAnyTeam($teams));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_team_undenial()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $teams = Team::factory()->count(3)->create();

        $this->service->denyAllFromModel($user, $teams);
        $this->service->undenyAllFromModel($user, $teams);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::UndenyTeam->value)->get();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_model_on_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->assignToModel($user, $team);

        $this->assertTrue($this->service->modelHas($user, $team));
    }

    public function test_model_on_team_returns_false_if_team_inactive()
    {
        $user = User::factory()->create();
        $team = Team::factory()->inactive()->create();

        $this->service->assignToModel($user, $team);

        $this->assertFalse($this->service->modelHas($user, $team));
    }

    public function test_model_is_not_on_team_granted_by_default_when_denied()
    {
        $user = User::factory()->create();
        $team = Team::factory()->grantByDefault()->create();

        $user->denyTeam($team);

        $this->assertFalse($this->service->modelHas($user, $team));
    }

    public function test_model_on_team_when_granted_by_default()
    {
        $user = User::factory()->create();
        $team = Team::factory()->grantByDefault()->create();

        $this->assertTrue($this->service->modelHas($user, $team));
    }

    public function test_model_on_any_team()
    {
        $user = User::factory()->create();
        $teams = Team::factory()->count(2)->create();

        $this->service->assignToModel($user, $teams->first());

        $this->assertTrue($this->service->modelHasAny($user, $teams));
    }

    public function test_model_on_all_teams()
    {
        $user = User::factory()->create();
        $teams = Team::factory()->count(2)->create();

        $this->service->assignAllToModel($user, $teams);

        $this->assertTrue($this->service->modelHasAll($user, $teams));
    }

    public function test_model_on_all_teams_fails_if_one_missing()
    {
        $user = User::factory()->create();
        $teams = Team::factory()->count(2)->create();

        $this->service->assignToModel($user, $teams->first());

        $this->assertFalse($this->service->modelHasAll($user, $teams));
    }

    public function test_add_model_to_team_throws_if_feature_disabled()
    {
        Config::set('gatekeeper.features.teams.enabled', false);

        $this->expectException(TeamsFeatureDisabledException::class);

        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->assignToModel($user, $team->name);
    }

    public function test_add_model_to_team_throws_if_model_does_not_interact()
    {
        $model = new class extends \Illuminate\Database\Eloquent\Model
        {
            protected $table = 'users';
        };

        $team = Team::factory()->create();

        $this->expectException(ModelDoesNotInteractWithTeamsException::class);

        $this->service->assignToModel($model, $team->name);
    }

    public function test_find_by_name_returns_team_if_found()
    {
        $team = Team::factory()->create();

        $found = $this->service->findByName($team->name);

        $this->assertInstanceOf(TeamPacket::class, $found);
        $this->assertSame($team->id, $found->id);
    }

    public function test_find_by_name_returns_null_if_not_found()
    {
        $found = $this->service->findByName('nonexistent-team');

        $this->assertNull($found);
    }

    public function test_get_all_teams_returns_collection()
    {
        Team::factory()->count(3)->create();

        $teams = $this->service->getAll();

        $this->assertCount(3, $teams);
        $this->assertInstanceOf(Collection::class, $teams);
        $this->assertContainsOnlyInstancesOf(TeamPacket::class, $teams);
    }

    public function test_get_direct_teams_for_model()
    {
        $user = User::factory()->create();

        $directTeams = Team::factory()->count(2)->create();
        $unrelatedTeam = Team::factory()->create();

        $this->service->assignAllToModel($user, $directTeams);

        $direct = $this->service->getDirectForModel($user);

        $this->assertCount(2, $direct);
        $this->assertTrue($direct->contains('id', $directTeams[0]->id));
        $this->assertTrue($direct->contains('id', $directTeams[1]->id));
        $this->assertFalse($direct->contains('id', $unrelatedTeam->id));
    }
}
