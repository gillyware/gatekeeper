<?php

namespace Braxey\Gatekeeper\Repositories;

use Braxey\Gatekeeper\Models\ModelHasRole;
use Braxey\Gatekeeper\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ModelHasRoleRepository
{
    public function create(Model $model, Role $role): ModelHasRole
    {
        return ModelHasRole::create([
            'role_id' => $role->id,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
        ]);
    }

    public function getForModelAndRole(Model $model, Role $role): Collection
    {
        return ModelHasRole::forModel($model)->where('role_id', $role->id)->get();
    }

    public function getRecentForModelAndRoleIncludingTrashed(Model $model, Role $role): ?ModelHasRole
    {
        return ModelHasRole::forModel($model)
            ->where('role_id', $role->id)
            ->withTrashed()
            ->latest()
            ->first();
    }

    public function deleteForModelAndRole(Model $model, Role $role): bool
    {
        $this->getForModelAndRole($model, $role)->each(function (ModelHasRole $modelHasRole) {
            $modelHasRole->delete();
        });

        return true;
    }
}
