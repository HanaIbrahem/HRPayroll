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
        
        $manager = User::where('role', 'manager')->inRandomOrder()->first();

        // Find an employee assigned to that manager
        $employee = Employee::where('user_id', $manager?->id)->inRandomOrder()->first();

        // Random status
        $status = $this->faker->randomElement(['pending', 'approved', 'rejected']);

        // If approved, pick HR user
        $approvedBy = null;
        $approvedAt = null;

        $calculated=0;
        if ($status === 'approved') {
            $calculated=$this->faker->numberBetween(1000, 100000);
            $approvedBy = User::where('role', 'hr')->inRandomOrder()->value('id');
            $approvedAt = now()->subDays(rand(1, 30));
        }

        return [
            'user_id'        => $manager?->id,
            'employee_id'    => $employee?->id,
            'filename'       => $this->faker->unique()->word . '.xlsx',
            'note'           => $this->faker->optional()->sentence(),
            'hr_note'        => $this->faker->optional()->sentence(),
            'status'         => $status,
            'calculated_cost'=> $calculated,
            'start_date'     => $this->faker->date(),
            'end_date'       => $this->faker->date(),
            'approved_by'    => $approvedBy,
            'approved_at'    => $approvedAt,
        ];
    }
}
