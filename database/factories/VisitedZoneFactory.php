<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Zone;
use App\Models\Checklist;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VisitedZone>
 */
class VisitedZoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        
          $zoneCount = $this->faker->numberBetween(1, 12);
        $repeatCount = $this->faker->numberBetween(1, 6);

        return [
            'checklist_id'     => Checklist::inRandomOrder()->value('id') ?? Checklist::factory(),
            'zone_id'          => Zone::inRandomOrder()->value('id') ?? Zone::factory(),
            'zone_count'       => $zoneCount,
            'repeat_count'     => $repeatCount,
            'calculated_cost'  => $zoneCount * $repeatCount * $this->faker->numberBetween(1000, 5000),
        ];
    }
}
