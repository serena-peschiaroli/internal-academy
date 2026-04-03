<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use App\RoleType;
use Illuminate\Support\Str;

function createUserWithRole(RoleType $role): User
{
    $roleModel = Role::query()->firstOrCreate(
        ['key' => $role->value],
        ['name' => Str::title($role->value)],
    );

    return User::factory()->create([
        'role_id' => $roleModel->id,
    ]);
}

test('admin can access workshop index, create and edit pages', function () {
    $this->withoutVite();

    $admin = createUserWithRole(RoleType::ADMIN);
    $workshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Laravel Performance',
        'description' => 'Tune queue and DB usage',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
        'capacity' => 30,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.workshops.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.workshops.create'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.workshops.edit', $workshop))
        ->assertOk();
});

test('employee cannot access admin workshop routes', function () {
    $employee = createUserWithRole(RoleType::EMPLOYEE);
    $workshop = Workshop::query()->create([
        'user_id' => $employee->id,
        'title' => 'Domain Events',
        'description' => 'Event-driven design',
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(2)->addHour(),
        'capacity' => 15,
    ]);

    $this->actingAs($employee)
        ->get(route('admin.workshops.index'))
        ->assertForbidden();

    $this->actingAs($employee)
        ->post(route('admin.workshops.store'), [
            'title' => 'Unauthorized',
            'description' => 'Should fail',
            'starts_at' => now()->addDays(3)->toDateTimeString(),
            'ends_at' => now()->addDays(3)->addHour()->toDateTimeString(),
            'capacity' => 20,
        ])
        ->assertForbidden();

    $this->actingAs($employee)
        ->patch(route('admin.workshops.update', $workshop), [
            'title' => 'Updated',
            'description' => 'Attempt',
            'starts_at' => now()->addDays(2)->toDateTimeString(),
            'ends_at' => now()->addDays(2)->addHour()->toDateTimeString(),
            'capacity' => 10,
        ])
        ->assertForbidden();
});

test('admin can store a workshop with valid data', function () {
    $admin = createUserWithRole(RoleType::ADMIN);

    $response = $this->actingAs($admin)->post(route('admin.workshops.store'), [
        'title' => 'Advanced SQL',
        'description' => 'Indexes, plans and optimization',
        'starts_at' => now()->addDay()->toDateTimeString(),
        'ends_at' => now()->addDay()->addHours(2)->toDateTimeString(),
        'capacity' => 25,
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.workshops.index'));

    $this->assertDatabaseHas('workshops', [
        'title' => 'Advanced SQL',
        'capacity' => 25,
        'user_id' => $admin->id,
    ]);
});

test('workshop store validates required fields and date rules', function () {
    $admin = createUserWithRole(RoleType::ADMIN);

    $response = $this->actingAs($admin)->from(route('admin.workshops.create'))
        ->post(route('admin.workshops.store'), [
            'title' => '',
            'description' => '',
            'starts_at' => now()->addDay()->toDateTimeString(),
            'ends_at' => now()->subDay()->toDateTimeString(),
            'capacity' => 0,
        ]);

    $response->assertSessionHasErrors([
        'title',
        'description',
        'ends_at',
        'capacity',
    ])->assertRedirect(route('admin.workshops.create'));
});

test('admin can update and delete workshop', function () {
    $admin = createUserWithRole(RoleType::ADMIN);
    $workshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Original title',
        'description' => 'Original description',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
        'capacity' => 12,
    ]);

    $updateResponse = $this->actingAs($admin)->patch(route('admin.workshops.update', $workshop), [
        'title' => 'Updated title',
        'description' => 'Updated description',
        'starts_at' => now()->addDays(2)->toDateTimeString(),
        'ends_at' => now()->addDays(2)->addHours(2)->toDateTimeString(),
        'capacity' => 18,
    ]);

    $updateResponse->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.workshops.index'));

    $this->assertDatabaseHas('workshops', [
        'id' => $workshop->id,
        'title' => 'Updated title',
        'capacity' => 18,
    ]);

    $deleteResponse = $this->actingAs($admin)->delete(route('admin.workshops.destroy', $workshop));
    $deleteResponse->assertRedirect(route('admin.workshops.index'));

    $this->assertDatabaseMissing('workshops', [
        'id' => $workshop->id,
    ]);
});
