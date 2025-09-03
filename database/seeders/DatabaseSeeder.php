<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use App\Models\Employee;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Zone;
use App\Models\Checklist;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Department::factory(count: 10)->create();
        //Zone::factory(count: 300)->create();
        // User::factory(20)->create();
        //Employee::factory(400)->create();

        Checklist::factory( 50)->create();
        // User::factory()->create(attributes: [
        //     'first_name' => 'admin',
        //     'last_name' => 'admin',
        //     'username' => 'admin',
        //     'role'=>'admin'
        // ]);
    }
}
