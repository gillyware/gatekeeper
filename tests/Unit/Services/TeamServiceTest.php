<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Services;

use Gillyware\Gatekeeper\Constants\Action;
use Gillyware\Gatekeeper\Exceptions\Model\ModelDoesNotInteractWithTeamsException;
use Gillyware\Gatekeeper\Exceptions\Team\DeletingAssignedTeamException;
use Gillyware\Gatekeeper\Exceptions\Team\TeamAlreadyExistsException;
use Gillyware\Gatekeeper\Exceptions\Team\TeamsFeatureDisabledException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Models\ModelHasTeam;
use Gillyware\Gatekeeper\Models\Team;
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

        Config::set('gatekeeper.features.teams', true);

        $this->user = User::factory()->create();
        Gatekeeper::setActor($this->user);

        $this->service = app(TeamService::class);
        $this->service->actingAs($this->user);
    }

    public function test_create_team()
    {
        $name = fake()->unique()->word();

        $team = $this->service->create($name);

        $this->assertInstanceOf(Team::class, $team);
        $this->assertEquals($name, $team->name);
    }

    public function test_create_team_fails_if_teams_feature_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

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
        Config::set('gatekeeper.features.audit', true);

        $name = fake()->unique()->word();

        $team = $this->service->create($name);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $createTeamLog = $auditLogs->first();
        $this->assertEquals(Action::TEAM_CREATE, $createTeamLog->action);
        $this->assertEquals($name, $createTeamLog->metadata['name']);
        $this->assertTrue($this->user->is($createTeamLog->actionBy));
        $this->assertTrue($team->is($createTeamLog->actionTo));
    }

    public function test_audit_log_not_inserted_on_team_creation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit', false);

        $name = fake()->unique()->word();

        $this->service->create($name);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_update_team()
    {
        $team = Team::factory()->create();
        $newName = fake()->unique()->word();

        $updatedTeam = $this->service->update($team, $newName);

        $this->assertInstanceOf(Team::class, $updatedTeam);
        $this->assertEquals($newName, $updatedTeam->name);
    }

    public function test_update_team_fails_if_teams_feature_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $this->expectException(TeamsFeatureDisabledException::class);

        $team = Team::factory()->create();
        $this->service->update($team, fake()->unique()->word());
    }

    public function test_audit_log_inserted_on_team_update_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit', true);

        $name = fake()->unique()->word();
        $team = Team::factory()->withName($name)->create();
        $newName = fake()->unique()->word();

        $this->service->update($team, $newName);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $updateTeamLog = $auditLogs->first();
        $this->assertEquals(Action::TEAM_UPDATE, $updateTeamLog->action);
        $this->assertEquals($newName, $updateTeamLog->metadata['name']);
        $this->assertEquals($name, $updateTeamLog->metadata['old_name']);
        $this->assertTrue($this->user->is($updateTeamLog->actionBy));
        $this->assertTrue($team->is($updateTeamLog->actionTo));
    }

    public function test_audit_log_not_inserted_on_team_update_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit', false);

        $team = Team::factory()->create();
        $newName = fake()->unique()->word();

        $this->service->update($team, $newName);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_deactivate_team()
    {
        $team = Team::factory()->create();

        $deactivatedTeam = $this->service->deactivate($team);

        $this->assertInstanceOf(Team::class, $deactivatedTeam);
        $this->assertFalse($deactivatedTeam->is_active);
    }

    public function test_deactivate_team_is_idempotent()
    {
        Config::set('gatekeeper.features.audit', true);

        $team = Team::factory()->create();

        $this->service->deactivate($team);
        $this->service->deactivate($team);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_deactivate_team_succeeds_if_teams_feature_disabled()
    {
        Config::set('gatekeeper.features.teams', false);
        $team = Team::factory()->create();

        $deactivatedTeam = $this->service->deactivate($team);

        $this->assertInstanceOf(Team::class, $deactivatedTeam);
        $this->assertFalse($deactivatedTeam->is_active);
    }

    public function test_audit_log_inserted_on_team_deactivation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit', true);

        $team = Team::factory()->create();

        $this->service->deactivate($team);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $deactivateTeamLog = $auditLogs->first();
        $this->assertEquals(Action::TEAM_DEACTIVATE, $deactivateTeamLog->action);
        $this->assertEquals($team->name, $deactivateTeamLog->metadata['name']);
        $this->assertEquals($this->user->id, $deactivateTeamLog->actionBy->id);
        $this->assertEquals($team->id, $deactivateTeamLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_team_deactivation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit', false);

        $team = Team::factory()->create();

        $this->service->deactivate($team);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_reactivate_team()
    {
        $team = Team::factory()->inactive()->create();

        $reactivatedTeam = $this->service->reactivate($team);

        $this->assertInstanceOf(Team::class, $reactivatedTeam);
        $this->assertTrue($reactivatedTeam->is_active);
    }

    public function test_reactivate_team_is_idempotent()
    {
        Config::set('gatekeeper.features.audit', true);

        $team = Team::factory()->inactive()->create();

        $this->service->reactivate($team);
        $this->service->reactivate($team);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_reactivate_team_fails_if_teams_feature_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $team = Team::factory()->inactive()->create();

        $this->expectException(TeamsFeatureDisabledException::class);
        $this->service->reactivate($team);
    }

    public function test_audit_log_inserted_on_team_reactivation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit', true);

        $team = Team::factory()->inactive()->create();

        $this->service->reactivate($team);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $reactivateTeamLog = $auditLogs->first();
        $this->assertEquals(Action::TEAM_REACTIVATE, $reactivateTeamLog->action);
        $this->assertEquals($team->name, $reactivateTeamLog->metadata['name']);
        $this->assertEquals($this->user->id, $reactivateTeamLog->actionBy->id);
        $this->assertEquals($team->id, $reactivateTeamLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_team_reactivation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit', false);

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
        Config::set('gatekeeper.features.teams', false);

        $team = Team::factory()->create();

        $result = $this->service->delete($team);

        $this->assertTrue($result);
        $this->assertSoftDeleted($team);
    }

    public function test_delete_team_fails_if_team_has_models()
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();

        $this->service->addModelTo($user, $team->name);

        $this->expectException(DeletingAssignedTeamException::class);

        $this->service->delete($team);
    }

    public function test_audit_log_inserted_on_team_deletion_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit', true);

        $name = fake()->unique()->word();
        $team = Team::factory()->withName($name)->create();

        $this->service->delete($team);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $deleteTeamLog = $auditLogs->first();
        $this->assertEquals(Action::TEAM_DELETE, $deleteTeamLog->action);
        $this->assertEquals($name, $deleteTeamLog->metadata['name']);
        $this->assertEquals($this->user->id, $deleteTeamLog->actionBy->id);
        $this->assertEquals($team->id, $deleteTeamLog->action_to_model_id);
    }

    public function test_audit_log_not_inserted_on_team_deletion_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit', false);

        $name = fake()->unique()->word();
        $team = Team::factory()->withName($name)->create();

        $this->service->delete($team);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_add_model_to_team()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        $team = Team::factory()->withName($name)->create();

        $result = $this->service->addModelTo($user, $name);

        $this->assertTrue($result);
        $this->assertDatabaseHas(Config::get('gatekeeper.tables.model_has_teams'), [
            'model_id' => $user->id,
            'model_type' => $user->getMorphClass(),
            'team_id' => $team->id,
            'deleted_at' => null,
        ]);
    }

    public function test_assign_team_is_idempotent()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Team::factory()->withName($name)->create();

        $this->assertTrue($this->service->addModelTo($user, $name));
        $this->assertTrue($this->service->addModelTo($user, $name));
        $this->assertTrue($user->onTeam($name));

        $this->assertCount(1, AuditLog::all());
        $this->assertCount(1, ModelHasTeam::all());
    }

    public function test_audit_log_inserted_on_team_assignment_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit', true);

        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Team::factory()->withName($name)->create();

        $this->service->addModelTo($user, $name);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        $assignTeamLog = $auditLogs->first();
        $this->assertEquals(Action::TEAM_ADD, $assignTeamLog->action);
        $this->assertEquals($name, $assignTeamLog->metadata['name']);
        $this->assertEquals($this->user->id, $assignTeamLog->actionBy->id);
        $this->assertEquals($user->id, $assignTeamLog->action_to_model_id);
    }

    public function test_audit_log_not_inserted_on_team_assignment_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit', false);

        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Team::factory()->withName($name)->create();

        $this->service->addModelTo($user, $name);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_add_model_to_team_is_idempotent()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Team::factory()->withName($name)->create();

        $this->service->addModelTo($user, $name);
        $result = $this->service->addModelTo($user, $name);

        $this->assertTrue($result);
    }

    public function test_add_model_to_multiple_teams()
    {
        $user = User::factory()->create();
        $teams = Team::factory()->count(3)->create();

        $this->service->addModelToAll($user, $teams);

        $this->assertTrue($user->onAllTeams($teams));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_team_assignment()
    {
        Config::set('gatekeeper.features.audit', true);

        $user = User::factory()->create();
        $teams = Team::factory()->count(3)->create();

        $this->service->addModelToAll($user, $teams);

        $auditLogs = AuditLog::all();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_remove_model_from_team()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        $team = Team::factory()->withName($name)->create();

        $this->service->addModelTo($user, $name);
        $result = $this->service->removeModelFrom($user, $name);

        $this->assertTrue($result);
        $this->assertSoftDeleted(Config::get('gatekeeper.tables.model_has_teams'), [
            'team_id' => $team->id,
            'model_id' => $user->id,
        ]);
    }

    public function test_audit_log_inserted_on_team_revocation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit', true);

        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Team::factory()->withName($name)->create();

        $this->service->addModelTo($user, $name);
        $this->service->removeModelFrom($user, $name);

        $auditLogs = AuditLog::query()->where('action', Action::TEAM_REMOVE)->get();
        $this->assertCount(1, $auditLogs);

        $assignTeamLog = $auditLogs->first();
        $this->assertEquals(Action::TEAM_REMOVE, $assignTeamLog->action);
        $this->assertEquals($name, $assignTeamLog->metadata['name']);
        $this->assertEquals($this->user->id, $assignTeamLog->actionBy->id);
        $this->assertEquals($user->id, $assignTeamLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_team_revocation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit', false);

        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Team::factory()->withName($name)->create();

        $this->service->addModelTo($user, $name);
        $this->service->removeModelFrom($user, $name);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_remove_model_from_multiple_teams()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        $teams = collect($names)->map(fn ($name) => Team::factory()->withName($name)->create());

        $this->service->addModelToAll($user, $names);
        $result = $this->service->removeModelFromAll($user, $names);

        $this->assertTrue($result);
        $teams->each(function ($team) use ($user) {
            $this->assertSoftDeleted(Config::get('gatekeeper.tables.model_has_teams'), [
                'team_id' => $team->id,
                'model_id' => $user->id,
            ]);
        });
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_team_revocation()
    {
        Config::set('gatekeeper.features.audit', true);

        $user = User::factory()->create();
        $teams = Team::factory()->count(3)->create();

        $this->service->addModelToAll($user, $teams);

        $this->service->removeModelFromAll($user, $teams);

        $auditLogs = AuditLog::query()->where('action', Action::TEAM_REMOVE)->get();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_model_on_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->addModelTo($user, $team);

        $this->assertTrue($this->service->modelOn($user, $team));
    }

    public function test_model_on_team_returns_false_if_team_inactive()
    {
        $user = User::factory()->create();
        $name = fake()->unique()->word();
        Team::factory()->withName($name)->inactive()->create();

        $this->service->addModelTo($user, $name);

        $this->assertFalse($this->service->modelOn($user, $name));
    }

    public function test_model_on_any_team()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        Team::factory()->withName($names[0])->create();
        Team::factory()->withName($names[1])->create();

        $this->service->addModelTo($user, $names[1]);

        $this->assertTrue($this->service->modelOnAny($user, $names));
    }

    public function test_model_on_all_teams()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        Team::factory()->withName($names[0])->create();
        Team::factory()->withName($names[1])->create();

        $this->service->addModelToAll($user, $names);

        $this->assertTrue($this->service->modelOnAll($user, $names));
    }

    public function test_model_on_all_teams_fails_if_one_missing()
    {
        $user = User::factory()->create();
        $names = [fake()->unique()->word(), fake()->unique()->word()];
        Team::factory()->withName($names[0])->create();
        Team::factory()->withName($names[1])->create();

        $this->service->addModelTo($user, $names[0]);

        $this->assertFalse($this->service->modelOnAll($user, $names));
    }

    public function test_add_model_to_team_throws_if_feature_disabled()
    {
        Config::set('gatekeeper.features.teams', false);

        $this->expectException(TeamsFeatureDisabledException::class);

        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->service->addModelTo($user, $team->name);
    }

    public function test_add_model_to_team_throws_if_model_does_not_interact()
    {
        $model = new class extends \Illuminate\Database\Eloquent\Model
        {
            protected $table = 'users';
        };

        $team = Team::factory()->create();

        $this->expectException(ModelDoesNotInteractWithTeamsException::class);

        $this->service->addModelTo($model, $team->name);
    }

    public function test_find_by_name_returns_team_if_found()
    {
        $team = Team::factory()->create();

        $found = $this->service->findByName($team->name);

        $this->assertInstanceOf(Team::class, $found);
        $this->assertTrue($team->is($found));
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
    }

    public function test_get_direct_teams_for_model()
    {
        $user = User::factory()->create();

        $directTeams = Team::factory()->count(2)->create();
        $unrelatedTeam = Team::factory()->create();

        $this->service->addModelToAll($user, $directTeams);

        $direct = $this->service->getDirectForModel($user);

        $this->assertCount(2, $direct);
        $this->assertTrue($direct->contains('id', $directTeams[0]->id));
        $this->assertTrue($direct->contains('id', $directTeams[1]->id));
        $this->assertFalse($direct->contains('id', $unrelatedTeam->id));
    }
}
