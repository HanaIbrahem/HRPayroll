<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Employee;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Checklist>
 */
class ChecklistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arr=['open','pending','approved','rejected'];
        return [
            //
            'user_id'=>15,
            'employee_id'=>12,
            'filename'=>fake()->filePath(),
            'status'=>fake()->randomElement($arr),

        ];
    }
}
