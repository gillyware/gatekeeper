<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Services;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Exceptions\Feature\FeatureAlreadyExistsException;
use Gillyware\Gatekeeper\Exceptions\Feature\FeaturesFeatureDisabledException;
use Gillyware\Gatekeeper\Exceptions\Model\ModelDoesNotInteractWithFeaturesException;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\ModelHasFeature;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket;
use Gillyware\Gatekeeper\Services\FeatureService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class FeatureServiceTest extends TestCase
{
    protected FeatureService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.features.features.enabled', true);

        $this->user = User::factory()->create();
        Gatekeeper::setActor($this->user);

        $this->service = app(FeatureService::class);
    }

    public function test_feature_exists()
    {
        $feature = Feature::factory()->create();

        $this->assertTrue($this->service->exists($feature->name));
    }

    public function test_feature_does_not_exist()
    {
        $name = fake()->unique()->word();

        $this->assertFalse($this->service->exists($name));
    }

    public function test_create_feature()
    {
        $name = fake()->unique()->word();

        $feature = $this->service->create($name);

        $this->assertInstanceOf(FeaturePacket::class, $feature);
        $this->assertEquals($name, $feature->name);
    }

    public function test_create_fails_if_feature_already_exists()
    {
        $existing = Feature::factory()->create();

        $this->expectException(FeatureAlreadyExistsException::class);

        $this->service->create($existing->name);
    }

    public function test_create_feature_fails_if_features_feature_disabled()
    {
        Config::set('gatekeeper.features.features.enabled', false);

        $name = fake()->unique()->word();

        $this->expectException(FeaturesFeatureDisabledException::class);

        $this->service->create($name);
    }

    public function test_audit_log_inserted_on_feature_creation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $name = fake()->unique()->word();
        $feature = $this->service->create($name);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Feature> $createFeatureLog */
        $createFeatureLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::CreateFeature->value, $createFeatureLog->action);
        $this->assertEquals($name, $createFeatureLog->metadata['name']);
        $this->assertTrue($this->user->is($createFeatureLog->actionBy));
        $this->assertEquals($feature->id, $createFeatureLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_feature_creation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $name = fake()->unique()->word();

        $this->service->create($name);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_update_feature_name()
    {
        $feature = Feature::factory()->create();
        $newName = fake()->unique()->word();

        $updatedFeature = $this->service->updateName($feature, $newName);

        $this->assertInstanceOf(FeaturePacket::class, $updatedFeature);
        $this->assertEquals($newName, $updatedFeature->name);
    }

    public function test_update_feature_name_fails_if_features_feature_disabled()
    {
        Config::set('gatekeeper.features.features.enabled', false);

        $name = fake()->unique()->word();
        $feature = Feature::factory()->withName($name)->create();

        $this->expectException(FeaturesFeatureDisabledException::class);
        $this->service->updateName($feature, 'new-name');

        $this->assertSame($name, $feature->fresh()->name);
    }

    public function test_audit_log_inserted_on_feature_update_name_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $feature = Feature::factory()->create();
        $oldName = $feature->name;
        $newName = fake()->unique()->word();

        $this->service->updateName($feature, $newName);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Feature> $updateFeatureLog */
        $updateFeatureLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::UpdateFeatureName->value, $updateFeatureLog->action);
        $this->assertEquals($oldName, $updateFeatureLog->metadata['old_name']);
        $this->assertEquals($newName, $updateFeatureLog->metadata['name']);
        $this->assertEquals($this->user->id, $updateFeatureLog->actionBy->id);
        $this->assertEquals($feature->id, $updateFeatureLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_feature_update_name_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $feature = Feature::factory()->create();
        $newName = fake()->unique()->word();

        $this->service->updateName($feature, $newName);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_grant_feature_by_default()
    {
        $feature = Feature::factory()->create();

        $feature = $this->service->grantByDefault($feature);

        $this->assertInstanceOf(FeaturePacket::class, $feature);
        $this->assertTrue($feature->grantedByDefault);
    }

    public function test_grant_feature_by_default_fails_if_features_feature_disabled()
    {
        Config::set('gatekeeper.features.features.enabled', false);

        $feature = Feature::factory()->create();

        $this->expectException(FeaturesFeatureDisabledException::class);
        $this->service->grantByDefault($feature);

        $this->assertFalse($feature->fresh()->grant_by_default);
    }

    public function test_grant_feature_by_default_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $feature = Feature::factory()->create();

        $this->service->grantByDefault($feature);
        $this->service->grantByDefault($feature);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_audit_log_inserted_on_grant_feature_by_default_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $feature = Feature::factory()->create();

        $this->service->grantByDefault($feature);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Feature> $grantByDefaultFeatureLog */
        $grantByDefaultFeatureLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::GrantFeatureByDefault->value, $grantByDefaultFeatureLog->action);
        $this->assertEquals($feature->name, $grantByDefaultFeatureLog->metadata['name']);
        $this->assertEquals($this->user->id, $grantByDefaultFeatureLog->actionBy->id);
        $this->assertEquals($feature->id, $grantByDefaultFeatureLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_grant_feature_by_default_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $feature = Feature::factory()->create();

        $this->service->grantByDefault($feature);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_revoke_feature_default_grant()
    {
        $feature = Feature::factory()->grantByDefault()->create();

        $feature = $this->service->revokeDefaultGrant($feature);

        $this->assertInstanceOf(FeaturePacket::class, $feature);
        $this->assertFalse($feature->grantedByDefault);
    }

    public function test_revoke_feature_default_grant_succeeds_if_features_feature_disabled()
    {
        Config::set('gatekeeper.features.features.enabled', false);

        $feature = Feature::factory()->grantByDefault()->create();
        $feature = $this->service->revokeDefaultGrant($feature);

        $this->assertFalse($feature->grantedByDefault);
    }

    public function test_revoke_feature_default_grant_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $feature = Feature::factory()->grantByDefault()->create();

        $feature = $this->service->revokeDefaultGrant($feature);
        $feature = $this->service->revokeDefaultGrant($feature);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_audit_log_inserted_on_revoke_feature_default_grant_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $feature = Feature::factory()->grantByDefault()->create();

        $this->service->revokeDefaultGrant($feature);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Feature> $revokeDefaultGrantAuditLog */
        $revokeDefaultGrantAuditLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::RevokeFeatureDefaultGrant->value, $revokeDefaultGrantAuditLog->action);
        $this->assertEquals($feature->name, $revokeDefaultGrantAuditLog->metadata['name']);
        $this->assertEquals($this->user->id, $revokeDefaultGrantAuditLog->actionBy->id);
        $this->assertEquals($feature->id, $revokeDefaultGrantAuditLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_revoke_feature_default_grant_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $feature = Feature::factory()->grantByDefault()->create();

        $this->service->revokeDefaultGrant($feature);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_deactivate_feature()
    {
        $feature = Feature::factory()->create();

        $feature = $this->service->deactivate($feature);

        $this->assertInstanceOf(FeaturePacket::class, $feature);
        $this->assertFalse($feature->isActive);
    }

    public function test_deactivate_feature_succeeds_if_features_feature_disabled()
    {
        Config::set('gatekeeper.features.features.enabled', false);

        $feature = Feature::factory()->create();
        $feature = $this->service->deactivate($feature);

        $this->assertFalse($feature->isActive);
    }

    public function test_deactivate_feature_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $feature = Feature::factory()->create();

        $feature = $this->service->deactivate($feature);
        $feature = $this->service->deactivate($feature);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_audit_log_inserted_on_feature_deactivation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $feature = Feature::factory()->create();

        $this->service->deactivate($feature);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Feature> $deactivateFeatureLog */
        $deactivateFeatureLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::DeactivateFeature->value, $deactivateFeatureLog->action);
        $this->assertEquals($feature->name, $deactivateFeatureLog->metadata['name']);
        $this->assertEquals($this->user->id, $deactivateFeatureLog->actionBy->id);
        $this->assertEquals($feature->id, $deactivateFeatureLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_feature_deactivation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $feature = Feature::factory()->create();

        $this->service->deactivate($feature);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_reactivate_feature()
    {
        $feature = Feature::factory()->inactive()->create();

        $feature = $this->service->reactivate($feature);

        $this->assertInstanceOf(FeaturePacket::class, $feature);
        $this->assertTrue($feature->isActive);
    }

    public function test_reactivate_feature_fails_if_features_feature_disabled()
    {
        Config::set('gatekeeper.features.features.enabled', false);

        $feature = Feature::factory()->inactive()->create();

        $this->expectException(FeaturesFeatureDisabledException::class);
        $this->service->reactivate($feature);

        $this->assertFalse($feature->fresh()->is_active);
    }

    public function test_reactivate_feature_is_idempotent()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $feature = Feature::factory()->inactive()->create();

        $this->service->reactivate($feature);
        $this->service->reactivate($feature);

        $this->assertCount(1, AuditLog::all());
    }

    public function test_audit_log_inserted_on_feature_reactivation_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $feature = Feature::factory()->inactive()->create();

        $this->service->reactivate($feature);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Feature> $reactivateFeatureLog */
        $reactivateFeatureLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::ReactivateFeature->value, $reactivateFeatureLog->action);
        $this->assertEquals($feature->name, $reactivateFeatureLog->metadata['name']);
        $this->assertEquals($this->user->id, $reactivateFeatureLog->actionBy->id);
        $this->assertEquals($feature->id, $reactivateFeatureLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_feature_reactivation_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $feature = Feature::factory()->inactive()->create();

        $this->service->reactivate($feature);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_delete_feature()
    {
        $name = fake()->unique()->word();
        $feature = Feature::factory()->withName($name)->create();

        $deleted = $this->service->delete($feature);

        $this->assertTrue($deleted);
        $this->assertSoftDeleted($feature);
    }

    public function test_delete_feature_deletes_assignments_if_feature_assigned_to_model()
    {
        $feature = Feature::factory()->create();
        $user = User::factory()->create();

        $this->service->assignToModel($user, $feature);

        $this->service->delete($feature);

        $this->assertFalse($this->service->exists($feature));
        $this->assertCount(0, $this->service->getDirectForModel($user));
    }

    public function test_audit_log_inserted_on_feature_deletion_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $feature = Feature::factory()->create();

        $this->service->delete($feature);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Feature> $deleteFeatureLog */
        $deleteFeatureLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::DeleteFeature->value, $deleteFeatureLog->action);
        $this->assertEquals($feature->name, $deleteFeatureLog->metadata['name']);
        $this->assertEquals($this->user->id, $deleteFeatureLog->actionBy->id);
        $this->assertEquals($feature->id, $deleteFeatureLog->action_to_model_id);
    }

    public function test_audit_log_not_inserted_on_feature_deletion_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $feature = Feature::factory()->create();

        $this->service->delete($feature);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_assign_feature()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->assertTrue($this->service->assignToModel($user, $feature));
        $this->assertTrue($user->hasFeature($feature));
    }

    public function test_assign_feature_is_idempotent()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->assertTrue($this->service->assignToModel($user, $feature));
        $this->assertTrue($this->service->assignToModel($user, $feature));
        $this->assertTrue($user->hasFeature($feature));

        $this->assertCount(1, AuditLog::all());
        $this->assertCount(1, ModelHasFeature::all());
    }

    public function test_audit_log_inserted_on_feature_assignment_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->service->assignToModel($user, $feature);

        $auditLogs = AuditLog::all();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, User> $assignFeatureLog */
        $assignFeatureLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::AssignFeature->value, $assignFeatureLog->action);
        $this->assertEquals($feature->name, $assignFeatureLog->metadata['name']);
        $this->assertEquals($this->user->id, $assignFeatureLog->actionBy->id);
        $this->assertEquals($user->id, $assignFeatureLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_feature_assignment_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->service->assignToModel($user, $feature);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_assign_duplicate_feature_is_ignored()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->assertTrue($this->service->assignToModel($user, $feature));
        $this->assertTrue($this->service->assignToModel($user, $feature));
    }

    public function test_assign_multiple_features()
    {
        $user = User::factory()->create();
        $features = Feature::factory()->count(3)->create();

        $this->assertTrue($this->service->assignAllToModel($user, $features));

        $this->assertTrue($user->hasAllFeatures($features));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_feature_assignment()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $features = Feature::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $features);

        $auditLogs = AuditLog::all();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_unassign_feature()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->service->assignToModel($user, $feature);

        $this->assertTrue($this->service->unassignFromModel($user, $feature));
        $this->assertFalse($user->hasFeature($feature));
    }

    public function test_audit_log_inserted_on_feature_unassignment_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->service->assignToModel($user, $feature);
        $this->service->unassignFromModel($user, $feature);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::UnassignFeature->value)->get();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, Feature> $unassignFeatureLog */
        $unassignFeatureLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::UnassignFeature->value, $unassignFeatureLog->action);
        $this->assertEquals($feature->name, $unassignFeatureLog->metadata['name']);
        $this->assertEquals($this->user->id, $unassignFeatureLog->actionBy->id);
        $this->assertEquals($user->id, $unassignFeatureLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_feature_unassignment_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->service->assignToModel($user, $feature);
        $this->service->unassignFromModel($user, $feature);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_unassign_multiple_features()
    {
        $user = User::factory()->create();
        $features = Feature::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $features);

        $this->assertTrue($this->service->unassignAllFromModel($user, $features));

        $this->assertFalse($user->hasAnyFeature($features));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_feature_unassignment()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $features = Feature::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $features);

        $this->service->unassignAllFromModel($user, $features);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::UnassignFeature->value)->get();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_deny_feature()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->service->assignToModel($user, $feature);

        $this->assertTrue($this->service->denyFromModel($user, $feature));
        $this->assertDatabaseHas((new ModelHasFeature)->getTable(), [
            'model_id' => $user->id,
            'denied' => true,
        ]);
        $this->assertFalse($user->hasFeature($feature));
    }

    public function test_audit_log_inserted_on_feature_denial_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->service->denyFromModel($user, $feature);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::DenyFeature->value)->get();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, User> $denyFeatureLog */
        $denyFeatureLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::DenyFeature->value, $denyFeatureLog->action);
        $this->assertEquals($feature->name, $denyFeatureLog->metadata['name']);
        $this->assertEquals($this->user->id, $denyFeatureLog->actionBy->id);
        $this->assertEquals($user->id, $denyFeatureLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_feature_denial_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->service->denyFromModel($user, $feature);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_deny_multiple_features()
    {
        $user = User::factory()->create();
        $features = Feature::factory()->count(3)->create();

        $this->service->assignAllToModel($user, $features);
        $this->service->denyAllFromModel($user, $features);

        $this->assertFalse($user->hasAnyFeature($features));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_feature_denial()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $features = Feature::factory()->count(3)->create();

        $this->service->denyAllFromModel($user, $features);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::DenyFeature->value)->get();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_undeny_feature()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->grantByDefault()->create();

        $this->service->denyFromModel($user, $feature);
        $this->service->undenyFromModel($user, $feature);

        $this->assertTrue($this->service->undenyFromModel($user, $feature));

        $this->assertEmpty(ModelHasFeature::query()->where([
            'model_id' => $user->id,
            'denied' => true,
        ])->get());

        $this->assertTrue($user->hasFeature($feature));
    }

    public function test_audit_log_inserted_on_feature_undenial_when_auditing_enabled()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->service->undenyFromModel($user, $feature);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::UndenyFeature->value)->get();
        $this->assertCount(1, $auditLogs);

        /** @var AuditLog<User, User> $undenyFeatureLog */
        $undenyFeatureLog = $auditLogs->first();
        $this->assertEquals(AuditLogAction::UndenyFeature->value, $undenyFeatureLog->action);
        $this->assertEquals($feature->name, $undenyFeatureLog->metadata['name']);
        $this->assertEquals($this->user->id, $undenyFeatureLog->actionBy->id);
        $this->assertEquals($user->id, $undenyFeatureLog->actionTo->id);
    }

    public function test_audit_log_not_inserted_on_feature_undenial_when_auditing_disabled()
    {
        Config::set('gatekeeper.features.audit.enabled', false);

        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->service->undenyFromModel($user, $feature);

        $this->assertCount(0, AuditLog::all());
    }

    public function test_undeny_multiple_features()
    {
        $user = User::factory()->create();
        $features = Feature::factory()->grantByDefault()->count(3)->create();

        $this->service->denyAllFromModel($user, $features);
        $this->service->undenyAllFromModel($user, $features);

        $this->assertTrue($user->hasAnyFeature($features));
    }

    public function test_all_audit_log_lifecycle_ids_match_on_bulk_feature_undenial()
    {
        Config::set('gatekeeper.features.audit.enabled', true);

        $user = User::factory()->create();
        $features = Feature::factory()->count(3)->create();

        $this->service->denyAllFromModel($user, $features);
        $this->service->undenyAllFromModel($user, $features);

        $auditLogs = AuditLog::query()->where('action', AuditLogAction::UndenyFeature->value)->get();
        $this->assertCount(3, $auditLogs);
        $this->assertTrue($auditLogs->every(fn (AuditLog $log) => $log->metadata['lifecycle_id'] === Gatekeeper::getLifecycleId()));
    }

    public function test_model_has_feature_direct()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $this->service->assignToModel($user, $feature);

        $this->assertTrue($this->service->modelHas($user, $feature));
    }

    public function test_model_has_feature_through_team()
    {
        Config::set('gatekeeper.features.teams.enabled', true);

        $user = User::factory()->create();
        $feature = Feature::factory()->create();
        $team = Team::factory()->create();

        $team->assignFeature($feature);
        $user->addToTeam($team);

        $this->assertTrue($this->service->modelHas($user, $feature));
    }

    public function test_model_does_not_have_feature_granted_by_default_when_denied()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->grantByDefault()->create();

        $user->denyFeature($feature);

        $this->assertFalse($this->service->modelHas($user, $feature));
    }

    public function test_model_has_feature_when_granted_by_default()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->grantByDefault()->create();

        $this->assertTrue($this->service->modelHas($user, $feature));
    }

    public function test_model_does_not_have_feature_through_team_feature_when_denied()
    {
        Config::set('gatekeeper.features.teams.enabled', true);

        $user = User::factory()->create();
        $feature = Feature::factory()->create();
        $team = Team::factory()->create();

        $team->assignFeature($feature);
        $user->addToTeam($team);

        $user->denyFeature($feature);

        $this->assertFalse($this->service->modelHas($user, $feature));
    }

    public function test_model_has_any_feature()
    {
        $user = User::factory()->create();
        $features = Feature::factory()->count(3)->create();

        $this->service->assignToModel($user, $features->first());

        $this->assertTrue($this->service->modelHasAny($user, $features));
    }

    public function test_model_has_all_features()
    {
        $user = User::factory()->create();
        $features = Feature::factory()->count(2)->create();

        $this->service->assignAllToModel($user, $features);

        $this->assertTrue($this->service->modelHasAll($user, $features));

        $this->service->unassignFromModel($user, $features->last());

        $this->assertFalse($this->service->modelHasAll($user, $features));
    }

    public function test_model_has_returns_false_if_feature_inactive()
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->inactive()->create();

        $this->service->assignToModel($user, $feature);

        $this->assertFalse($this->service->modelHas($user, $feature));
    }

    public function test_model_has_returns_false_if_team_inactive()
    {
        Config::set('gatekeeper.features.teams.enabled', true);

        $user = User::factory()->create();
        $feature = Feature::factory()->create();
        $team = Team::factory()->inactive()->create();

        $team->assignFeature($feature);
        $user->addToTeam($team);

        $this->assertFalse($this->service->modelHas($user, $feature));
    }

    public function test_throws_if_model_does_not_use_trait()
    {
        $model = new class extends \Illuminate\Database\Eloquent\Model
        {
            protected $table = 'users';
        };

        $this->expectException(ModelDoesNotInteractWithFeaturesException::class);

        $this->service->assignToModel($model, 'any');
    }

    public function test_throws_if_features_feature_disabled()
    {
        Config::set('gatekeeper.features.features.enabled', false);

        $this->expectException(FeaturesFeatureDisabledException::class);

        $user = User::factory()->create();
        $this->service->assignToModel($user, 'any');
    }

    public function test_find_by_name_returns_feature_if_found()
    {
        $feature = Feature::factory()->create();

        $found = $this->service->findByName($feature->name);

        $this->assertInstanceOf(FeaturePacket::class, $found);
        $this->assertEquals($feature->id, $found->id);
    }

    public function test_find_by_name_returns_null_if_not_found()
    {
        $found = $this->service->findByName('nonexistent-feature');

        $this->assertNull($found);
    }

    public function test_get_all_features_returns_collection()
    {
        Feature::factory()->count(3)->create();

        $features = $this->service->getAll();

        $this->assertCount(3, $features);
        $this->assertInstanceOf(Collection::class, $features);
        $this->assertContainsOnlyInstancesOf(FeaturePacket::class, $features);
    }

    public function test_get_direct_features_for_model()
    {
        $user = User::factory()->create();

        $directFeatures = Feature::factory()->count(2)->create();
        $unrelatedFeature = Feature::factory()->create();

        $this->service->assignAllToModel($user, $directFeatures);

        $direct = $this->service->getDirectForModel($user);

        $this->assertCount(2, $direct);
        $this->assertTrue($direct->contains('id', $directFeatures[0]->id));
        $this->assertTrue($direct->contains('id', $directFeatures[1]->id));
        $this->assertFalse($direct->contains('id', $unrelatedFeature->id));
    }

    public function test_get_effective_features_for_model()
    {
        Config::set('gatekeeper.features.features.enabled', true);
        Config::set('gatekeeper.features.teams.enabled', true);

        $user = User::factory()->create();
        $directFeature = Feature::factory()->create();
        $teamFeature = Feature::factory()->create();

        $team = Team::factory()->create();
        $team->assignFeature($teamFeature);
        $user->addToTeam($team);

        $user->assignFeature($directFeature);

        $effective = $this->service->getForModel($user);

        $this->assertCount(2, $effective);
        $this->assertTrue($effective->contains('id', $directFeature->id));
        $this->assertTrue($effective->contains('id', $teamFeature->id));
    }
}
