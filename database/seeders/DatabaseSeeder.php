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
        $employeeRole = Role::query()->firstOrCreate(
            ['key' => RoleType::EMPLOYEE->value],
            ['name' => 'Employee'],
        );

        User::query()->firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => 'password',
            'role_id' => $employeeRole->id,
        ]);

        $this->call(AdminUserSeeder::class);
        $this->call(WorkshopSeeder::class);
    }
}
