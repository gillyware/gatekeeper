<?php

namespace Gillyware\Gatekeeper\Tests\Fixtures;

use Gillyware\Gatekeeper\Database\Factories\UserFactory;
use Gillyware\Gatekeeper\Traits\HasPermissions;
use Gillyware\Gatekeeper\Traits\HasRoles;
use Gillyware\Gatekeeper\Traits\HasTeams;
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
