<?php

use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use App\RegistrationStatus;
use App\RoleType;
use App\Services\WorkshopRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createServiceUserWithRole(RoleType $role): User
{
    $roleModel = Role::query()->firstOrCreate(
        ['key' => $role->value],
        ['name' => Str::title($role->value)],
    );

    return User::factory()->create([
        'role_id' => $roleModel->id,
    ]);
}

test('register throws validation exception when workshop overlaps with an already confirmed one', function () {
    $service = app(WorkshopRegistrationService::class);
    $admin = createServiceUserWithRole(RoleType::ADMIN);
    $employee = createServiceUserWithRole(RoleType::EMPLOYEE);

    $confirmedWorkshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Confirmed Workshop',
        'description' => 'Already booked by employee',
        'starts_at' => now()->addDays(3)->setTime(10, 0),
        'ends_at' => now()->addDays(3)->setTime(11, 0),
        'capacity' => 10,
    ]);

    $overlappingWorkshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Overlapping Workshop',
        'description' => 'Target registration',
        'starts_at' => now()->addDays(3)->setTime(10, 30),
        'ends_at' => now()->addDays(3)->setTime(11, 30),
        'capacity' => 10,
    ]);

    Registration::factory()->confirmed()->for($employee)->for($confirmedWorkshop)->create();

    expect(fn () => $service->register($employee, $overlappingWorkshop))
        ->toThrow(ValidationException::class, 'You cannot register for overlapping workshops.');
});

test('cancel promotes first non-overlapping waitlisted user when head of queue has conflict', function () {
    $service = app(WorkshopRegistrationService::class);
    $admin = createServiceUserWithRole(RoleType::ADMIN);
    $confirmed = createServiceUserWithRole(RoleType::EMPLOYEE);
    $waitlistedConflicting = createServiceUserWithRole(RoleType::EMPLOYEE);
    $waitlistedEligible = createServiceUserWithRole(RoleType::EMPLOYEE);

    $targetWorkshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Target Workshop',
        'description' => 'Promotion target',
        'starts_at' => now()->addDays(2)->setTime(10, 0),
        'ends_at' => now()->addDays(2)->setTime(11, 0),
        'capacity' => 1,
    ]);

    $conflictingWorkshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Conflicting Workshop',
        'description' => 'Conflicts with target time',
        'starts_at' => now()->addDays(2)->setTime(10, 30),
        'ends_at' => now()->addDays(2)->setTime(11, 30),
        'capacity' => 5,
    ]);

    Registration::factory()->confirmed()->for($confirmed)->for($targetWorkshop)->create();
    Registration::factory()->waitlisted(1)->for($waitlistedConflicting)->for($targetWorkshop)->create();
    Registration::factory()->waitlisted(2)->for($waitlistedEligible)->for($targetWorkshop)->create();
    Registration::factory()->confirmed()->for($waitlistedConflicting)->for($conflictingWorkshop)->create();

    $service->cancel($confirmed, $targetWorkshop);

    $this->assertDatabaseHas('registrations', [
        'user_id' => $waitlistedConflicting->id,
        'workshop_id' => $targetWorkshop->id,
        'status' => RegistrationStatus::WAITLISTED->value,
        'waitlist_position' => 1,
    ]);

    $this->assertDatabaseHas('registrations', [
        'user_id' => $waitlistedEligible->id,
        'workshop_id' => $targetWorkshop->id,
        'status' => RegistrationStatus::CONFIRMED->value,
        'waitlist_position' => null,
    ]);
});

test('cancel does not promote anyone when all waitlisted users overlap', function () {
    $service = app(WorkshopRegistrationService::class);
    $admin = createServiceUserWithRole(RoleType::ADMIN);
    $confirmed = createServiceUserWithRole(RoleType::EMPLOYEE);
    $waitlistedOne = createServiceUserWithRole(RoleType::EMPLOYEE);
    $waitlistedTwo = createServiceUserWithRole(RoleType::EMPLOYEE);

    $targetWorkshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Target Workshop',
        'description' => 'No eligible promotion',
        'starts_at' => now()->addDays(2)->setTime(14, 0),
        'ends_at' => now()->addDays(2)->setTime(15, 0),
        'capacity' => 1,
    ]);

    $conflictingWorkshopOne = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Conflict A',
        'description' => 'Overlap A',
        'starts_at' => now()->addDays(2)->setTime(14, 10),
        'ends_at' => now()->addDays(2)->setTime(15, 10),
        'capacity' => 5,
    ]);

    $conflictingWorkshopTwo = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Conflict B',
        'description' => 'Overlap B',
        'starts_at' => now()->addDays(2)->setTime(13, 30),
        'ends_at' => now()->addDays(2)->setTime(14, 30),
        'capacity' => 5,
    ]);

    Registration::factory()->confirmed()->for($confirmed)->for($targetWorkshop)->create();
    Registration::factory()->waitlisted(1)->for($waitlistedOne)->for($targetWorkshop)->create();
    Registration::factory()->waitlisted(2)->for($waitlistedTwo)->for($targetWorkshop)->create();
    Registration::factory()->confirmed()->for($waitlistedOne)->for($conflictingWorkshopOne)->create();
    Registration::factory()->confirmed()->for($waitlistedTwo)->for($conflictingWorkshopTwo)->create();

    $service->cancel($confirmed, $targetWorkshop);

    $this->assertDatabaseMissing('registrations', [
        'workshop_id' => $targetWorkshop->id,
        'status' => RegistrationStatus::CONFIRMED->value,
    ]);

    $this->assertDatabaseHas('registrations', [
        'user_id' => $waitlistedOne->id,
        'workshop_id' => $targetWorkshop->id,
        'status' => RegistrationStatus::WAITLISTED->value,
    ]);

    $this->assertDatabaseHas('registrations', [
        'user_id' => $waitlistedTwo->id,
        'workshop_id' => $targetWorkshop->id,
        'status' => RegistrationStatus::WAITLISTED->value,
    ]);
});
