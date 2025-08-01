<?php

namespace Gillyware\Gatekeeper\Tests\Fixtures;

use Gillyware\Gatekeeper\Database\Factories\UserFactory;
use Gillyware\Gatekeeper\Traits\HasFeatures;
use Gillyware\Gatekeeper\Traits\HasPermissions;
use Gillyware\Gatekeeper\Traits\HasRoles;
use Gillyware\Gatekeeper\Traits\HasTeams;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Model;

/**
 * @property int $id // PK
 */
class User extends Model
{
    use Authenticatable;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasFeatures;
    use HasPermissions;
    use HasRoles;
    use HasTeams;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
