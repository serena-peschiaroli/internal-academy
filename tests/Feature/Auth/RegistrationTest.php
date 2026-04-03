<?php

use App\Models\User;
use App\RoleType;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyFeature(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => RoleType::EMPLOYEE->value,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('workshops.index', absolute: false));
});

test('public registration always creates employee role', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'role' => RoleType::ADMIN->value,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('workshops.index', absolute: false));

    $createdUser = User::query()->where('email', 'admin@example.com')->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->role?->key)->toBe(RoleType::EMPLOYEE);
});
