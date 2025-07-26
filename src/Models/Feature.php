<?php

namespace Gillyware\Gatekeeper\Models;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Database\Factories\FeatureFactory;
use Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket;
use Gillyware\Gatekeeper\Traits\HasPermissions;
use Illuminate\Support\Facades\Config;

/**
 * @extends AbstractBaseEntityModel<FeatureFactory, FeaturePacket>
 *
 * @property bool $default_enabled
 */
class Feature extends AbstractBaseEntityModel
{
    use HasPermissions;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'default_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function newFactory(): FeatureFactory
    {
        return FeatureFactory::new();
    }

    protected static function packetClass(): string
    {
        return FeaturePacket::class;
    }

    public function getTable(): string
    {
        return Config::get('gatekeeper.tables.features', GatekeeperConfigDefault::TABLES_FEATURES);
    }
}
