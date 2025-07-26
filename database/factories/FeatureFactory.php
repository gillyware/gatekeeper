<?php

namespace Gillyware\Gatekeeper\Database\Factories;

use Gillyware\Gatekeeper\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feature>
 */
class FeatureFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Feature::class;

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
            'default_enabled' => false,
        ];
    }

    /**
     * Specify a name for the feature.
     *
     * @return $this
     */
    public function withName(string $featureName): static
    {
        return $this->state([
            'name' => $featureName,
        ]);
    }

    /**
     * Mark the feature as inactive.
     *
     * @return $this
     */
    public function inactive(): static
    {
        return $this->state([
            'is_active' => false,
        ]);
    }

    /**
     * Mark the feature as disabled by default.
     *
     * @return $this
     */
    public function defaultOn(): static
    {
        return $this->state([
            'default_enabled' => true,
        ]);
    }
}
