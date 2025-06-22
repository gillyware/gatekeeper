<?php

namespace Braxey\Gatekeeper\Tests\Fixtures;

use Braxey\Gatekeeper\Database\Factories\UserFactory;
use Braxey\Gatekeeper\Traits\HasPermissions;
use Braxey\Gatekeeper\Traits\HasRoles;
use Braxey\Gatekeeper\Traits\HasTeams;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Model;

class User extends Model
{
    use HasFactory;
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
