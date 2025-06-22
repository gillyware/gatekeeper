<?php

namespace Braxey\Gatekeeper\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<\Braxey\Gatekeeper\Models\ModelHasRole> forModel(Model $model)
 */
class ModelHasRole extends Model
{
  use SoftDeletes;

  /**
   * The attributes that are not mass assignable.
   *
   * @var array
   */
  protected $guarded = [];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  /**
   * Get the table associated with the model.
   */
  public function getTable()
  {
    return config('gatekeeper.tables.model_has_roles', 'model_has_roles');
  }

  /**
   * Scope a query to the given model (polymorphic).
   *
   * @param  Builder  $query
   * @param  Model    $model
   * @return Builder
   */
  public function scopeForModel(Builder $query, Model $model): Builder
  {
    return $query
      ->where('model_type', $model->getMorphClass())
      ->where('model_id', $model->getKey());
  }
}
