<?php

namespace Gillyware\Gatekeeper\Models;

use Gillyware\Postal\Contracts\PacketableInterface;
use Gillyware\Postal\Packet;
use Gillyware\Postal\Traits\Packetable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @template TFactory as Factory
 * @template TPacket as Packet
 *
 * @property int $id // PK
 * @property string $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
abstract class AbstractBaseEntityModel extends Model implements PacketableInterface
{
    /** @use HasFactory<TFactory> */
    use HasFactory;

    /** @use Packetable<TPacket> */
    use Packetable;

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
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
