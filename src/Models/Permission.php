<?php

namespace Braxey\Gatekeeper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
  use SoftDeletes;

  /**
   * The database table used by the model.
   */
  protected $table;

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
    return config('gatekeeper.tables.permissions', 'permissions');
  }
}
