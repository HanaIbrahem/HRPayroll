<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        static $seq = 1; // simple sequence for codes

        $first = $this->faker->firstName();
        $last  = $this->faker->lastName();

        $user = User::inRandomOrder()->with('department')->first();

return [
    'first_name'    => $first,
    'last_name'     => $last,
    'location_id' => Location::inRandomOrder()->value('id') ?? Location::factory(),
    'department_id' => $user?->department_id ?? 233, // fallback if user is null
    'user_id'       => $user?->id ?? 2,
    'position'      => $this->faker->randomElement([
        'Manager','Staff','Intern','Officer','Supervisor','Specialist','Coordinator'
    ]),
    'code' => fake()->unique()->numberBetween(1, 200000) . str_pad((string)$seq++, 5, '0', STR_PAD_LEFT),
];
    }
}
