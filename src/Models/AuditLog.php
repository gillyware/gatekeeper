<?php

namespace Gillyware\Gatekeeper\Models;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

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
        return Config::get('gatekeeper.tables.audit_logs', GatekeeperConfigDefault::TABLES_AUDIT_LOGS);
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
