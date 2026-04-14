<?php

use App\Models\User;
use App\Mail\TemporaryPasswordMail;
use App\RoleType;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyFeature(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    Mail::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => RoleType::EMPLOYEE->value,
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login', absolute: false));

    $createdUser = User::query()->where('email', 'test@example.com')->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->first_access)->toBeTrue()
        ->and($createdUser->is_active)->toBeTrue()
        ->and($createdUser->temporary_password_expires_at)->not->toBeNull();

    Mail::assertQueued(TemporaryPasswordMail::class, function (TemporaryPasswordMail $mail) use ($createdUser): bool {
        return $mail->hasTo('test@example.com') && $mail->user->is($createdUser);
    });
});

test('public registration always creates employee role', function () {
    Mail::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'role' => RoleType::ADMIN->value,
    ]);

    $response->assertRedirect(route('login', absolute: false));

    $createdUser = User::query()->where('email', 'admin@example.com')->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->role?->key)->toBe(RoleType::EMPLOYEE)
        ->and($createdUser->first_access)->toBeTrue()
        ->and($createdUser->is_active)->toBeTrue()
        ->and($createdUser->temporary_password_expires_at)->not->toBeNull();

    Mail::assertQueued(TemporaryPasswordMail::class, function (TemporaryPasswordMail $mail) use ($createdUser): bool {
        return $mail->hasTo('admin@example.com') && $mail->user->is($createdUser);
    });
});
