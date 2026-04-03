<?php

use App\Models\Role;
use App\Models\User;
use App\RoleType;
use Illuminate\Support\Str;

function createAdminUserForManagement(): User
{
    $role = Role::query()->firstOrCreate(
        ['key' => RoleType::ADMIN->value],
        ['name' => Str::title(RoleType::ADMIN->value)],
    );

    return User::factory()->create([
        'role_id' => $role->id,
    ]);
}

function createEmployeeUserForManagement(): User
{
    $role = Role::query()->firstOrCreate(
        ['key' => RoleType::EMPLOYEE->value],
        ['name' => Str::title(RoleType::EMPLOYEE->value)],
    );

    return User::factory()->create([
        'role_id' => $role->id,
    ]);
}

test('admin can view create user page', function () {
    $this->withoutVite();

    $admin = createAdminUserForManagement();

    $this->actingAs($admin)
        ->get(route('admin.users.create'))
        ->assertOk();
});

test('admin can create another admin user', function () {
    $admin = createAdminUserForManagement();

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'New Admin',
        'email' => 'new-admin@example.com',
        'role' => RoleType::ADMIN->value,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('admin.users.create'));

    $created = User::query()->where('email', 'new-admin@example.com')->first();

    expect($created)->not->toBeNull()
        ->and($created->role?->key)->toBe(RoleType::ADMIN);
});

test('employee cannot access admin user creation flow', function () {
    $employee = createEmployeeUserForManagement();

    $this->actingAs($employee)
        ->get(route('admin.users.create'))
        ->assertForbidden();

    $this->actingAs($employee)
        ->post(route('admin.users.store'), [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'role' => RoleType::ADMIN->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertForbidden();
});
