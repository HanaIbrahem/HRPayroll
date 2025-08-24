<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Zone>
 */
class ZoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         return [
            'from_zone'     => substr($this->faker->city(), 0, 20),
            'to_zone'       => substr($this->faker->city(), 0, 20),
            'code'          => (string) $this->faker->numberBetween(100, 99999),
            'km'            => $this->faker->randomFloat(1, 1, 500),   // e.g., 123.4
            'fixed_rate'    => $this->faker->numberBetween(0, 50000),  // IQD
            'between_zone'  => $this->faker->numberBetween(0, 50000),  // IQD
            'description'   => $this->faker->sentence(8),
        ];
    }
}
