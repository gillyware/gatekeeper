<?php

namespace Braxey\Gatekeeper\Database\Factories;

use Braxey\Gatekeeper\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Permission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'is_active' => true,
        ];
    }

    /**
     * Specify a name for the permission.
     *
     * @return $this
     */
    public function withName(string $permissionName): static
    {
        return $this->state([
            'name' => $permissionName,
        ]);
    }

    /**
     * Mark the permission as inactive.
     *
     * @return $this
     */
    public function inactive(): static
    {
        return $this->state([
            'is_active' => false,
        ]);
    }
}
