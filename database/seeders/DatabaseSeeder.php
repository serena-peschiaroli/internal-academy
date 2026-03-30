<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\RoleType;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::query()->firstOrCreate(
            ['key' => RoleType::ADMIN->value],
            ['name' => 'Admin'],
        );

        $employeeRole = Role::query()->firstOrCreate(
            ['key' => RoleType::EMPLOYEE->value],
            ['name' => 'Employee'],
        );

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role_id' => $employeeRole->id,
        ]);
    }
}
