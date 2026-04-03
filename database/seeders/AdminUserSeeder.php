<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\RoleType;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed a default admin user for local/dev usage.
     */
    public function run(): void
    {
        $adminRole = Role::query()->firstOrCreate(
            ['key' => RoleType::ADMIN->value],
            ['name' => 'Admin'],
        );

        User::query()->updateOrCreate([
            'email' => 'test-admin@internal-academy.com',
        ], [
            'name' => 'Test Admin',
            'password' => 'Password1',
            'role_id' => $adminRole->id,
        ]);
    }
}
