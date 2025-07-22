<?php

namespace Gillyware\Gatekeeper\Models;

use Carbon\Carbon;
use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Packets\AuditLog\AuditLogPacket;
use Gillyware\Gatekeeper\Services\AuditLogService;
use Gillyware\Postal\Contracts\PacketableInterface;
use Gillyware\Postal\Traits\Packetable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

/**
 * @template TFactory as Factory
 * @template TPacket as Packet
 *
 * @property int $id // PK
 * @property string $action
 * @property ?string $action_by_model_type
 * @property ?int $action_by_model_id
 * @property ?string $action_to_model_type
 * @property ?int $action_to_model_id
 * @property array $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class AuditLog extends Model implements PacketableInterface
{
    /** @use Packetable<AuditLogPacket> */
    use Packetable;

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
        return Config::get('gatekeeper.tables.audit_log', GatekeeperConfigDefault::TABLES_AUDIT_LOG);
    }

    public function actionBy(): MorphTo
    {
        return $this->morphTo('action_by_model');
    }

    public function actionTo(): MorphTo
    {
        return $this->morphTo('action_to_model');
    }

    protected static function packetClass(): string
    {
        return AuditLogPacket::class;
    }

    protected function packetData(): array
    {
        $auditLogService = app(AuditLogService::class);
        $displayTimezone = Config::get('gatekeeper.timezone', GatekeeperConfigDefault::TIMEZONE);

        return [
            'id' => $this->id,
            'message' => $auditLogService->getMessageForAuditLog($this),
            'created_at' => $this->created_at->timezone($displayTimezone)->format('Y-m-d H:i:s T'),
        ];
    }
}
