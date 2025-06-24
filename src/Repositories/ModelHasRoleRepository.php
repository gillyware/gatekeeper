<?php

namespace Braxey\Gatekeeper\Repositories;

use Braxey\Gatekeeper\Models\ModelHasRole;
use Braxey\Gatekeeper\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ModelHasRoleRepository
{
    /**
     * Create a new ModelHasRole instance.
     */
    public function create(Model $model, Role $role): ModelHasRole
    {
        return ModelHasRole::create([
            'role_id' => $role->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ]);
    }

    /**
     * Get all ModelHasRole instances for a given model and role.
     */
    public function getForModelAndRole(Model $model, Role $role): Collection
    {
        return ModelHasRole::forModel($model)->where('role_id', $role->id)->get();
    }

    /**
     * Get the most recent ModelHasRole instance for a given model and role, including trashed instances.
     */
    public function getRecentForModelAndRoleIncludingTrashed(Model $model, Role $role): ?ModelHasRole
    {
        return ModelHasRole::forModel($model)
            ->where('role_id', $role->id)
            ->withTrashed()
            ->latest()
            ->first();
    }

    /**
     * Delete all ModelHasRole instances for a given model and role.
     */
    public function deleteForModelAndRole(Model $model, Role $role): bool
    {
        $this->getForModelAndRole($model, $role)->each(function (ModelHasRole $modelHasRole) {
            $modelHasRole->delete();
        });

        return true;
    }
}
