<?php

use App\Models\Role;
use App\Models\User;
use App\RoleType;
use Illuminate\Support\Str;

function createFirstAccessUser(string $password = 'temp-password-123'): User
{
    $role = Role::query()->firstOrCreate(
        ['key' => RoleType::EMPLOYEE->value],
        ['name' => Str::title(RoleType::EMPLOYEE->value)],
    );

    return User::factory()->create([
        'email' => 'first-access@example.com',
        'password' => $password,
        'first_access' => true,
        'temporary_password_expires_at' => now()->addDay(),
        'role_id' => $role->id,
    ]);
}

test('first access user is redirected to secure password page after login', function () {
    $user = createFirstAccessUser();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'temp-password-123',
    ]);

    $response->assertRedirect(route('secure-password.edit', absolute: false));
});

test('first access user can set password and is logged out', function () {
    $user = createFirstAccessUser();

    $this->actingAs($user)
        ->put(route('secure-password.update'), [
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])
        ->assertRedirect(route('login', absolute: false));

    $this->assertGuest();

    $user->refresh();
    expect($user->first_access)->toBeFalse()
        ->and($user->email_verified_at)->not->toBeNull();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'new-password-456',
    ])->assertRedirect(route('workshops.index', absolute: false));
});

test('expired temporary password redirects to password reset request', function () {
    $user = createFirstAccessUser();
    $user->update([
        'temporary_password_expires_at' => now()->subMinute(),
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'temp-password-123',
    ]);

    $response->assertRedirect(route('password.request', absolute: false));
    $this->assertGuest();
});
