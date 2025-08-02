<?php

namespace Gillyware\Gatekeeper\Services;

use Gillyware\Gatekeeper\Enums\EntityUpdateAction;
use Gillyware\Gatekeeper\Enums\FeatureSourceType;
use Gillyware\Gatekeeper\Exceptions\Feature\FeatureAlreadyExistsException;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\AssignFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\CreateFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\DeactivateFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\DeleteFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\DenyFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\GrantedFeatureByDefaultAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\ReactivateFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\RevokedFeatureDefaultGrantAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\UnassignFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\UndenyFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\Feature\UpdateFeatureAuditLogPacket;
use Gillyware\Gatekeeper\Packets\Entities\EntityPagePacket;
use Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket;
use Gillyware\Gatekeeper\Packets\Entities\Feature\UpdateFeaturePacket;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Repositories\FeatureRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasFeatureRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use UnitEnum;

/**
 * @extends AbstractBaseEntityService<Feature, FeaturePacket>
 */
class FeatureService extends AbstractBaseEntityService
{
    public function __construct(
        private readonly FeatureRepository $featureRepository,
        private readonly TeamRepository $teamRepository,
        private readonly ModelHasFeatureRepository $modelHasFeatureRepository,
        private readonly AuditLogRepository $auditLogRepository,
    ) {}

    /**
     * Check if the features table exists.
     */
    public function tableExists(): bool
    {
        return $this->featureRepository->tableExists();
    }

    /**
     * Check if a feature with the given name exists.
     */
    public function exists(string|UnitEnum $featureName): bool
    {
        $featureName = $this->resolveEntityName($featureName);

        return $this->featureRepository->exists($featureName);
    }

    /**
     * Create a new feature.
     */
    public function create(string|UnitEnum $featureName): FeaturePacket
    {
        $this->enforceAuditFeature();
        $this->enforceFeaturesFeature();

        $featureName = $this->resolveEntityName($featureName);

        if ($this->exists($featureName)) {
            throw new FeatureAlreadyExistsException($featureName);
        }

        $createdFeature = $this->featureRepository->create($featureName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(CreateFeatureAuditLogPacket::make($createdFeature));
        }

        return $createdFeature->toPacket();
    }

    /**
     * Update an existing feature.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     * @param UpdateFeaturePacket
     */
    public function update($feature, $packet): FeaturePacket
    {
        return match ($packet->action) {
            EntityUpdateAction::Name->value => $this->updateName($feature, $packet->value),
            EntityUpdateAction::Status->value => $packet->value ? $this->reactivate($feature) : $this->deactivate($feature),
            EntityUpdateAction::DefaultGrant->value => $packet->value ? $this->grantByDefault($feature) : $this->revokeDefaultGrant($feature),
            default => throw new InvalidArgumentException('Invalid update action.'),
        };
    }

    /**
     * Update an existing feature name.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function updateName($feature, string|UnitEnum $newFeatureName): FeaturePacket
    {
        $this->enforceAuditFeature();
        $this->enforceFeaturesFeature();

        $newFeatureName = $this->resolveEntityName($newFeatureName);

        $currentFeature = $this->resolveEntity($feature, orFail: true);

        if ($this->exists($newFeatureName) && $currentFeature->name !== $newFeatureName) {
            throw new FeatureAlreadyExistsException($newFeatureName);
        }

        $oldFeatureName = $currentFeature->name;
        $updatedFeature = $this->featureRepository->updateName($currentFeature, $newFeatureName);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(UpdateFeatureAuditLogPacket::make($updatedFeature, $oldFeatureName));
        }

        return $updatedFeature->toPacket();
    }

    /**
     * Grant a feature to all models that are not explicitly denying it.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function grantByDefault($feature): FeaturePacket
    {
        $this->enforceAuditFeature();
        $this->enforceFeaturesFeature();

        $currentFeature = $this->resolveEntity($feature, orFail: true);

        if ($currentFeature->grant_by_default) {
            return $currentFeature->toPacket();
        }

        $defaultedOnFeature = $this->featureRepository->grantByDefault($currentFeature);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(GrantedFeatureByDefaultAuditLogPacket::make($defaultedOnFeature));
        }

        return $defaultedOnFeature->toPacket();
    }

    /**
     * Revoke a feature's default grant.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function revokeDefaultGrant($feature): FeaturePacket
    {
        $this->enforceAuditFeature();

        $currentFeature = $this->resolveEntity($feature, orFail: true);

        if (! $currentFeature->grant_by_default) {
            return $currentFeature->toPacket();
        }

        $defaultedOffFeature = $this->featureRepository->revokeDefaultGrant($currentFeature);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(RevokedFeatureDefaultGrantAuditLogPacket::make($defaultedOffFeature));
        }

        return $defaultedOffFeature->toPacket();
    }

    /**
     * Deactivate a feature.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function deactivate($feature): FeaturePacket
    {
        $this->enforceAuditFeature();

        $currentFeature = $this->resolveEntity($feature, orFail: true);

        if (! $currentFeature->is_active) {
            return $currentFeature->toPacket();
        }

        $deactivatedFeature = $this->featureRepository->deactivate($currentFeature);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(DeactivateFeatureAuditLogPacket::make($deactivatedFeature));
        }

        return $deactivatedFeature->toPacket();
    }

    /**
     * Reactivate a feature.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function reactivate($feature): FeaturePacket
    {
        $this->enforceAuditFeature();
        $this->enforceFeaturesFeature();

        $currentFeature = $this->resolveEntity($feature, orFail: true);

        if ($currentFeature->is_active) {
            return $currentFeature->toPacket();
        }

        $reactivatedFeature = $this->featureRepository->reactivate($currentFeature);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(ReactivateFeatureAuditLogPacket::make($reactivatedFeature));
        }

        return $reactivatedFeature->toPacket();
    }

    /**
     * Delete a feature.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function delete($feature): bool
    {
        $this->enforceAuditFeature();

        $feature = $this->resolveEntity($feature);

        if (! $feature) {
            return true;
        }

        // Delete any existing assignments for the feature being deleted.
        if ($this->modelHasFeatureRepository->existsForEntity($feature)) {
            $this->modelHasFeatureRepository->deleteForEntity($feature);
        }

        $deleted = $this->featureRepository->delete($feature);

        if ($deleted && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(DeleteFeatureAuditLogPacket::make($feature));
        }

        return (bool) $deleted;
    }

    /**
     * Assign a feature to a model.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function assignToModel(Model $model, $feature): bool
    {
        $this->enforceAuditFeature();
        $this->enforceFeaturesFeature();
        $this->enforceFeatureInteraction($model);
        $this->enforceModelIsNotFeature($model, 'Features cannot be assigned to other features');
        $this->enforceModelIsNotPermission($model, 'Features cannot be assigned to permissions');

        $feature = $this->resolveEntity($feature, orFail: true);

        // If the model already has this feature directly assigned, return true.
        if ($this->modelHasDirectly($model, $feature)) {
            return true;
        }

        $this->modelHasFeatureRepository->assignToModel($model, $feature);

        if ($this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(AssignFeatureAuditLogPacket::make($model, $feature));
        }

        return true;
    }

    /**
     * Assign multiple features to a model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function assignAllToModel(Model $model, array|Arrayable $features): bool
    {
        $result = true;

        $this->resolveEntities($features, orFail: true)->each(function (Feature $feature) use ($model, &$result) {
            $result = $result && $this->assignToModel($model, $feature);
        });

        return $result;
    }

    /**
     * Unassign a feature from a model.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function unassignFromModel(Model $model, $feature): bool
    {
        $this->enforceAuditFeature();

        $feature = $this->resolveEntity($feature, orFail: true);

        $unassigned = $this->modelHasFeatureRepository->unassignFromModel($model, $feature);

        if ($unassigned && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(UnassignFeatureAuditLogPacket::make($model, $feature));
        }

        return $unassigned;
    }

    /**
     * Unassign multiple features from a model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function unassignAllFromModel(Model $model, array|Arrayable $features): bool
    {
        $result = true;

        $this->resolveEntities($features, orFail: true)->each(function (Feature $feature) use ($model, &$result) {
            $result = $result && $this->unassignFromModel($model, $feature);
        });

        return $result;
    }

    /**
     * Deny a feature from a model.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function denyFromModel(Model $model, $feature): bool
    {
        $this->enforceAuditFeature();

        $feature = $this->resolveEntity($feature, orFail: true);

        $denied = $this->modelHasFeatureRepository->denyFromModel($model, $feature);

        if ($denied && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(DenyFeatureAuditLogPacket::make($model, $feature));
        }

        return (bool) $denied;
    }

    /**
     * Deny multiple features from a model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function denyAllFromModel(Model $model, array|Arrayable $features): bool
    {
        $result = true;

        $this->resolveEntities($features, orFail: true)->each(function (Feature $feature) use ($model, &$result) {
            $result = $result && $this->denyFromModel($model, $feature);
        });

        return $result;
    }

    /**
     * Undeny a feature from a model.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function undenyFromModel(Model $model, $feature): bool
    {
        $this->enforceFeaturesFeature();
        $this->enforceAuditFeature();

        $feature = $this->resolveEntity($feature, orFail: true);

        $denied = $this->modelHasFeatureRepository->undenyFromModel($model, $feature);

        if ($denied && $this->auditFeatureEnabled()) {
            $this->auditLogRepository->create(UndenyFeatureAuditLogPacket::make($model, $feature));
        }

        return (bool) $denied;
    }

    /**
     * Undeny multiple features from a model.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function undenyAllFromModel(Model $model, array|Arrayable $features): bool
    {
        $result = true;

        $this->resolveEntities($features, orFail: true)->each(function (Feature $feature) use ($model, &$result) {
            $result = $result && $this->undenyFromModel($model, $feature);
        });

        return $result;
    }

    /**
     * Check if a model has the given feature.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function modelHas(Model $model, $feature): bool
    {
        // If the features feature is disabled or the model is not using the HasFeatures trait, return false.
        if (! $this->featuresFeatureEnabled() || ! $this->modelInteractsWithFeatures($model)) {
            return false;
        }

        $feature = $this->resolveEntity($feature);

        // If the feature does not exist or is inactive, return false.
        if (! $feature || ! $feature->is_active) {
            return false;
        }

        // If the feature is denied from the model, return false.
        if ($this->featureRepository->deniedFromModel($model)->has($feature->name)) {
            return false;
        }

        // If the feature is granted by default, return true.
        if ($feature->grant_by_default) {
            return true;
        }

        // If the feature is directly assigned to the model, return true.
        if ($this->modelHasDirectly($model, $feature)) {
            return true;
        }

        // If teams are enabled and the model is using the HasTeams trait, check if the model has the feature through a team.
        if ($this->teamsFeatureEnabled() && $this->modelInteractsWithTeams($model)) {
            $onTeamWithFeature = $this->teamRepository->all()
                ->filter(fn (Team $team) => $model->onTeam($team))
                ->some(fn (Team $team) => $team->hasFeature($feature));

            if ($onTeamWithFeature) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a model directly has the given feature (not granted through teams).
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    public function modelHasDirectly(Model $model, $feature): bool
    {
        $feature = $this->resolveEntity($feature);

        if (! $feature) {
            return false;
        }

        $foundAssignment = $this->featureRepository->assignedToModel($model)->get($feature->name);

        return $foundAssignment && $foundAssignment->is_active;
    }

    /**
     * Check if a model has any of the given features.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function modelHasAny(Model $model, array|Arrayable $features): bool
    {
        return $this->resolveEntities($features)->filter()->some(
            fn (Feature $feature) => $this->modelHas($model, $feature)
        );
    }

    /**
     * Check if a model has all of the given features.
     *
     * @param  array<Feature|FeaturePacket|string|UnitEnum>|Arrayable<Feature|FeaturePacket|string|UnitEnum>  $features
     */
    public function modelHasAll(Model $model, array|Arrayable $features): bool
    {
        return $this->resolveEntities($features)->every(
            fn (?Feature $feature) => $feature && $this->modelHas($model, $feature)
        );
    }

    /**
     * Find a feature by its name.
     */
    public function findByName(string|UnitEnum $featureName): ?FeaturePacket
    {
        return $this->resolveEntity($featureName)?->toPacket();
    }

    /**
     * Get all features.
     *
     * @return Collection<string, FeaturePacket>
     */
    public function getAll(): Collection
    {
        return $this->featureRepository->all()
            ->map(fn (Feature $feature) => $feature->toPacket());
    }

    /**
     * Get all features directly assigned to a model.
     *
     * @return Collection<string, FeaturePacket>
     */
    public function getDirectForModel(Model $model): Collection
    {
        return $this->featureRepository->assignedToModel($model)
            ->map(fn (Feature $feature) => $feature->toPacket());
    }

    /**
     * Get all features assigned directly or indirectly to a model.
     *
     * @return Collection<string, FeaturePacket>
     */
    public function getForModel(Model $model): Collection
    {
        return $this->featureRepository->all()
            ->filter(fn (Feature $feature) => $this->modelHas($model, $feature))
            ->map(fn (Feature $feature) => $feature->toPacket());
    }

    /**
     * Get all effective features for the given model with the feature source(s).
     */
    public function getVerboseForModel(Model $model): Collection
    {
        $result = collect();
        $sourcesMap = [];

        if (! $this->featuresFeatureEnabled() || ! $this->modelInteractsWithFeatures($model)) {
            return $result;
        }

        $deniedFeatures = $this->featureRepository->deniedFromModel($model);
        $activeUndeniedFeatures = $this->featureRepository->all()
            ->filter(fn (Feature $feature) => ! $deniedFeatures->has($feature->name))
            ->filter(fn (Feature $feature) => $feature->is_active);

        // Features granted by default.
        $activeUndeniedFeatures
            ->filter(fn (Feature $feature) => $feature->grant_by_default)
            ->each(function (Feature $feature) use (&$sourcesMap) {
                $sourcesMap[$feature->name][] = [
                    'type' => FeatureSourceType::DEFAULT,
                ];
            });

        // Features directly assigned.
        $this->featureRepository->assignedToModel($model)
            ->filter(fn (Feature $feature) => $feature->is_active)
            ->each(function (Feature $feature) use (&$sourcesMap) {
                $sourcesMap[$feature->name][] = ['type' => FeatureSourceType::DIRECT];
            });

        if ($this->teamsFeatureEnabled() && $this->modelInteractsWithTeams($model)) {
            // Features through teams.
            $this->teamRepository->all()
                ->filter(fn (Team $team) => $model->onTeam($team))
                ->each(function (Team $team) use (&$sourcesMap, $activeUndeniedFeatures) {
                    $activeUndeniedFeatures
                        ->filter(fn (Feature $feature) => $team->hasFeature($feature))
                        ->each(function (Feature $feature) use (&$sourcesMap, $team) {
                            $sourcesMap[$feature->name][] = [
                                'type' => FeatureSourceType::TEAM,
                                'team' => $team->name,
                            ];
                        });
                });
        }

        foreach ($sourcesMap as $featureName => $sources) {
            $result->push([
                'name' => $featureName,
                'sources' => $sources,
            ]);
        }

        return $result;
    }

    /**
     * Get a page of features.
     */
    public function getPage(EntityPagePacket $packet): LengthAwarePaginator
    {
        return $this->featureRepository->getPage($packet)
            ->through(fn (Feature $feature) => $feature->toPacket());
    }

    /**
     * Get the feature model from the feature or feature name.
     *
     * @param  Feature|FeaturePacket|string|UnitEnum  $feature
     */
    protected function resolveEntity($feature, bool $orFail = false): ?Feature
    {
        if ($feature instanceof Feature) {
            return $feature;
        }

        $featureName = $this->resolveEntityName($feature);

        return $orFail
            ? $this->featureRepository->findOrFailByName($featureName)
            : $this->featureRepository->findByName($featureName);
    }
}
