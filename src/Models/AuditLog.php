<?php

namespace Braxey\Gatekeeper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditLog extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'action',
        'action_by_model_type',
        'action_by_model_id',
        'action_to_model_type',
        'action_to_model_id',
        'metadata',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'metadata' => 'json',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('gatekeeper.tables.audit_log', 'gatekeeper_audit_log');
    }

    public function actionBy(): MorphTo
    {
        return $this->morphTo('action_by_model');
    }

    public function actionTo(): MorphTo
    {
        return $this->morphTo('action_to_model');
    }
}
