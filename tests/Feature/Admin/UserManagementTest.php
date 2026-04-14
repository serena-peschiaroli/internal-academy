<?php

use App\Models\Role;
use App\Models\User;
use App\Mail\TemporaryPasswordMail;
use App\RoleType;
use Illuminate\Support\Facades\Mail;
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
        ->get(route('admin.users.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.users.create'))
        ->assertOk();
});

test('admin can view user detail and edit pages', function () {
    $this->withoutVite();

    $admin = createAdminUserForManagement();
    $targetUser = createEmployeeUserForManagement();

    $this->actingAs($admin)
        ->get(route('admin.users.show', $targetUser))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $targetUser))
        ->assertOk();
});

test('admin can create another admin user', function () {
    Mail::fake();

    $admin = createAdminUserForManagement();

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'New Admin',
        'email' => 'new-admin@example.com',
        'role' => RoleType::ADMIN->value,
    ]);

    $response->assertRedirect(route('admin.users.index'));

    $created = User::query()->where('email', 'new-admin@example.com')->first();

    expect($created)->not->toBeNull()
        ->and($created->role?->key)->toBe(RoleType::ADMIN)
        ->and($created->first_access)->toBeTrue()
        ->and($created->is_active)->toBeTrue()
        ->and($created->temporary_password_expires_at)->not->toBeNull();

    Mail::assertQueued(TemporaryPasswordMail::class, function (TemporaryPasswordMail $mail) use ($created): bool {
        return $mail->hasTo('new-admin@example.com') && $mail->user->is($created);
    });
});

test('admin can update and delete a user', function () {
    $admin = createAdminUserForManagement();
    $targetUser = createEmployeeUserForManagement();

    $updateResponse = $this->actingAs($admin)->patch(route('admin.users.update', $targetUser), [
        'name' => 'Updated Employee',
        'email' => 'updated-employee@example.com',
        'role' => RoleType::ADMIN->value,
        'is_active' => false,
        'password' => '',
        'password_confirmation' => '',
    ]);

    $updateResponse->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.users.index'));

    $targetUser->refresh();

    expect($targetUser->name)->toBe('Updated Employee')
        ->and($targetUser->email)->toBe('updated-employee@example.com')
        ->and($targetUser->role?->key)->toBe(RoleType::ADMIN)
        ->and($targetUser->is_active)->toBeFalse();

    $deleteResponse = $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $targetUser));

    $deleteResponse->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseMissing('users', [
        'id' => $targetUser->id,
    ]);
});

test('employee cannot access admin user creation flow', function () {
    $employee = createEmployeeUserForManagement();

    $this->actingAs($employee)
        ->get(route('admin.users.index'))
        ->assertForbidden();

    $this->actingAs($employee)
        ->get(route('admin.users.show', $employee))
        ->assertForbidden();

    $this->actingAs($employee)
        ->get(route('admin.users.edit', $employee))
        ->assertForbidden();

    $this->actingAs($employee)
        ->get(route('admin.users.create'))
        ->assertForbidden();

    $this->actingAs($employee)
        ->post(route('admin.users.store'), [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'role' => RoleType::ADMIN->value,
        ])
        ->assertForbidden();

    $this->actingAs($employee)
        ->patch(route('admin.users.update', $employee), [
            'name' => 'Forbidden Update',
            'email' => $employee->email,
            'role' => RoleType::EMPLOYEE->value,
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertForbidden();

    $this->actingAs($employee)
        ->delete(route('admin.users.destroy', $employee))
        ->assertForbidden();
});
