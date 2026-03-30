<?php

use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use App\RoleType;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

function userWithRole(RoleType $role): User
{
    $roleModel = Role::query()->firstOrCreate(
        ['key' => $role->value],
        ['name' => Str::title($role->value)],
    );

    return User::factory()->create([
        'role_id' => $roleModel->id,
    ]);
}

test('admin can access admin-only middleware route', function () {
    Route::middleware(['web', 'auth', 'role:admin'])->get('/_test/admin-only', fn () => response('ok'));

    $admin = userWithRole(RoleType::ADMIN);
    $this->actingAs($admin);

    $this->get('/_test/admin-only')->assertOk();
});

test('employee is forbidden on admin-only middleware route', function () {
    Route::middleware(['web', 'auth', 'role:admin'])->get('/_test/admin-only-forbidden', fn () => response('ok'));

    $employee = userWithRole(RoleType::EMPLOYEE);
    $this->actingAs($employee);

    $this->get('/_test/admin-only-forbidden')->assertForbidden();
});

test('named gates separate admin and employee areas', function () {
    $admin = userWithRole(RoleType::ADMIN);
    $employee = userWithRole(RoleType::EMPLOYEE);

    expect(Gate::forUser($admin)->allows('access-admin-area'))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('access-employee-area'))->toBeFalse()
        ->and(Gate::forUser($employee)->allows('access-admin-area'))->toBeFalse()
        ->and(Gate::forUser($employee)->allows('access-employee-area'))->toBeTrue();
});

test('workshop and registration policies enforce role capabilities', function () {
    $admin = userWithRole(RoleType::ADMIN);
    $employee = userWithRole(RoleType::EMPLOYEE);
    $otherEmployee = userWithRole(RoleType::EMPLOYEE);

    $workshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'DDD Foundations',
        'description' => 'Workshop intro',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
        'capacity' => 20,
    ]);

    $registration = Registration::query()->create([
        'user_id' => $employee->id,
        'workshop_id' => $workshop->id,
        'status' => 'confirmed',
    ]);

    expect(Gate::forUser($admin)->allows('create', Workshop::class))->toBeTrue()
        ->and(Gate::forUser($employee)->allows('create', Workshop::class))->toBeFalse()
        ->and(Gate::forUser($employee)->allows('create', Registration::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', Registration::class))->toBeFalse()
        ->and(Gate::forUser($employee)->allows('delete', $registration))->toBeTrue()
        ->and(Gate::forUser($otherEmployee)->allows('delete', $registration))->toBeFalse();
});

