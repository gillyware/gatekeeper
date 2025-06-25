<?php

namespace Braxey\Gatekeeper\Database\Factories;

use Braxey\Gatekeeper\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
        ];
    }

    /**
     * Specify a name for the role.
     *
     * @return $this
     */
    public function withName(string $roleName): static
    {
        return $this->state([
            'name' => $roleName,
        ]);
    }

    /**
     * Mark the role as inactive.
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
