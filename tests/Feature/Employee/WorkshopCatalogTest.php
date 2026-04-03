<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use App\RoleType;
use Illuminate\Support\Str;

function createCatalogUserWithRole(RoleType $role): User
{
    $roleModel = Role::query()->firstOrCreate(
        ['key' => $role->value],
        ['name' => Str::title($role->value)],
    );

    return User::factory()->create([
        'role_id' => $roleModel->id,
    ]);
}

test('employee can view only future workshops list', function () {
    $this->withoutVite();

    $employee = createCatalogUserWithRole(RoleType::EMPLOYEE);
    $admin = createCatalogUserWithRole(RoleType::ADMIN);

    Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Past Workshop',
        'description' => 'Should not be visible',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->subDay()->addHour(),
        'capacity' => 10,
    ]);

    Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Future Workshop',
        'description' => 'Should be visible',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
        'capacity' => 20,
    ]);

    $response = $this->actingAs($employee)->get(route('workshops.index'));

    $response->assertOk()
        ->assertSee('Future Workshop')
        ->assertDontSee('Past Workshop');
});

test('admin cannot access employee workshop catalog route', function () {
    $admin = createCatalogUserWithRole(RoleType::ADMIN);

    $this->actingAs($admin)
        ->get(route('workshops.index'))
        ->assertForbidden();
});

