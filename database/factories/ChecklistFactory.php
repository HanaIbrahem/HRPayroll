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
            'user_id'=>123,
            'employee_id'=>Employee::factory(),
            'filename'=>'checklists/67EhMGgYaTs6U9cY6vU48x7cBmdpa9FhGsZPjGCZ.xlsx',
            'status'=>fake()->randomElement($arr),
            'start_date'=>now(),
            'end_date'=>now(),

        ];
    }
}
