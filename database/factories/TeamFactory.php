<?php

namespace Gillyware\Gatekeeper\Database\Factories;

use Gillyware\Gatekeeper\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Team::class;

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
     * Specify a name for the team.
     *
     * @return $this
     */
    public function withName(string $teamName): static
    {
        return $this->state([
            'name' => $teamName,
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
