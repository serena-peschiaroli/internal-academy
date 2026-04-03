<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use App\RoleType;
use Illuminate\Database\Seeder;

class WorkshopSeeder extends Seeder
{
    /**
     * Seed workshops for demo and development.
     */
    public function run(): void
    {
        $adminRole = Role::query()->firstOrCreate(
            ['key' => RoleType::ADMIN->value],
            ['name' => 'Admin'],
        );

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Academy Admin',
                'password' => 'password',
                'role_id' => $adminRole->id,
            ],
        );

        Workshop::query()->where('user_id', $admin->id)->delete();

        $now = now();

        Workshop::query()->create([
            'user_id' => $admin->id,
            'title' => 'Legacy Refactoring Clinic',
            'description' => 'Hands-on refactoring on real legacy snippets.',
            'starts_at' => $now->copy()->subDays(2)->setTime(15, 0),
            'ends_at' => $now->copy()->subDays(2)->setTime(17, 0),
            'capacity' => 20,
        ]);

        Workshop::query()->create([
            'user_id' => $admin->id,
            'title' => 'Laravel Queue Patterns',
            'description' => 'Retry strategies, backoff, and failure handling.',
            'starts_at' => $now->copy()->addDays(1)->setTime(10, 0),
            'ends_at' => $now->copy()->addDays(1)->setTime(12, 0),
            'capacity' => 25,
        ]);

        Workshop::query()->create([
            'user_id' => $admin->id,
            'title' => 'Pragmatic SQL Tuning',
            'description' => 'Indexes, query plans, and safe performance wins.',
            'starts_at' => $now->copy()->addDays(3)->setTime(14, 30),
            'ends_at' => $now->copy()->addDays(3)->setTime(16, 30),
            'capacity' => 30,
        ]);

        Workshop::query()->create([
            'user_id' => $admin->id,
            'title' => 'Testing in Layers',
            'description' => 'Unit, feature and integration boundaries in Laravel.',
            'starts_at' => $now->copy()->addDays(7)->setTime(9, 30),
            'ends_at' => $now->copy()->addDays(7)->setTime(11, 30),
            'capacity' => 18,
        ]);
    }
}

