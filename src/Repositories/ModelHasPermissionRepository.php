<?php

namespace Braxey\Gatekeeper\Repositories;

use Braxey\Gatekeeper\Models\ModelHasPermission;
use Braxey\Gatekeeper\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ModelHasPermissionRepository
{
    /**
     * Create a new ModelHasPermission instance.
     */
    public function create(Model $model, Permission $permission): ModelHasPermission
    {
        return ModelHasPermission::create([
            'permission_id' => $permission->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ]);
    }

    /**
     * Get all ModelHasPermission instances for a given model and permission.
     */
    public function getForModelAndPermission(Model $model, Permission $permission): Collection
    {
        return ModelHasPermission::forModel($model)->where('permission_id', $permission->id)->get();
    }

    /**
     * Get the most recent ModelHasPermission instance for a given model and permission, including trashed instances.
     */
    public function getRecentForModelAndPermissionIncludingTrashed(Model $model, Permission $permission): ?ModelHasPermission
    {
        return ModelHasPermission::forModel($model)
            ->where('permission_id', $permission->id)
            ->withTrashed()
            ->latest()
            ->first();
    }

    /**
     * Delete all ModelHasPermission instances for a given model and permission.
     */
    public function deleteForModelAndPermission(Model $model, Permission $permission): bool
    {
        $this->getForModelAndPermission($model, $permission)->each(function (ModelHasPermission $modelHasPermission) {
            $modelHasPermission->delete();
        });

        return true;
    }
}
