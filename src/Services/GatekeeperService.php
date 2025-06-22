<?php

namespace Braxey\Gatekeeper\Services;

use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Models\Role;
use Braxey\Gatekeeper\Models\Team;

class GatekeeperService
{
    /**
     * Create a new permission.
     */
    public function createPermission(string $name): Permission
    {
        return Permission::create([
            'name' => $name,
        ]);
    }

    /**
     * Create a new role.
     */
    public function createRole(string $name): Role
    {
        if (! config('gatekeeper.features.roles', false)) {
            throw new \RuntimeException('Roles feature is disabled.');
        }

        return Role::create([
            'name' => $name,
        ]);
    }

    /**
     * Create a new team.
     */
    public function createTeam(string $name): Team
    {
        if (! config('gatekeeper.features.teams', false)) {
            throw new \RuntimeException('Teams feature is disabled.');
        }

        return Team::create([
            'name' => $name,
        ]);
    }
}
