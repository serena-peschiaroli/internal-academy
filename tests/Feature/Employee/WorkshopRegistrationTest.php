<?php

use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use App\RegistrationStatus;
use App\RoleType;
use Illuminate\Support\Str;

function createRegistrationUserWithRole(RoleType $role): User
{
    $roleModel = Role::query()->firstOrCreate(
        ['key' => $role->value],
        ['name' => Str::title($role->value)],
    );

    return User::factory()->create([
        'role_id' => $roleModel->id,
    ]);
}

test('employee can register to a future workshop when seats are available', function () {
    $employee = createRegistrationUserWithRole(RoleType::EMPLOYEE);
    $admin = createRegistrationUserWithRole(RoleType::ADMIN);

    $workshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Future Workshop',
        'description' => 'Open workshop',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
        'capacity' => 2,
    ]);

    $response = $this->actingAs($employee)->post(route('workshops.registrations.store', $workshop));
    $response->assertRedirect();

    $this->assertDatabaseHas('registrations', [
        'user_id' => $employee->id,
        'workshop_id' => $workshop->id,
        'status' => 'confirmed',
    ]);
});

test('employee cannot register twice to the same workshop', function () {
    $employee = createRegistrationUserWithRole(RoleType::EMPLOYEE);
    $admin = createRegistrationUserWithRole(RoleType::ADMIN);

    $workshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Single Registration Workshop',
        'description' => 'No duplicate registrations',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
        'capacity' => 5,
    ]);

    Registration::query()->create([
        'user_id' => $employee->id,
        'workshop_id' => $workshop->id,
        'status' => 'confirmed',
    ]);

    $response = $this->actingAs($employee)->from(route('workshops.index'))
        ->post(route('workshops.registrations.store', $workshop));

    $response->assertSessionHasErrors('workshop')
        ->assertRedirect(route('workshops.index'));

    expect(Registration::query()
        ->where('user_id', $employee->id)
        ->where('workshop_id', $workshop->id)
        ->count())->toBe(1);
});

test('employee is waitlisted when workshop is full', function () {
    $employee = createRegistrationUserWithRole(RoleType::EMPLOYEE);
    $admin = createRegistrationUserWithRole(RoleType::ADMIN);
    $otherEmployee = createRegistrationUserWithRole(RoleType::EMPLOYEE);

    $workshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Full Workshop',
        'description' => 'No seats left',
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(2)->addHour(),
        'capacity' => 1,
    ]);

    Registration::query()->create([
        'user_id' => $otherEmployee->id,
        'workshop_id' => $workshop->id,
        'status' => RegistrationStatus::CONFIRMED->value,
    ]);

    $response = $this->actingAs($employee)->from(route('workshops.index'))
        ->post(route('workshops.registrations.store', $workshop));

    $response->assertRedirect(route('workshops.index'));

    $this->assertDatabaseHas('registrations', [
        'user_id' => $employee->id,
        'workshop_id' => $workshop->id,
        'status' => RegistrationStatus::WAITLISTED->value,
        'waitlist_position' => 1,
    ]);
});

test('employee can cancel own registration', function () {
    $employee = createRegistrationUserWithRole(RoleType::EMPLOYEE);
    $admin = createRegistrationUserWithRole(RoleType::ADMIN);

    $workshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Cancelable Workshop',
        'description' => 'Cancel me',
        'starts_at' => now()->addDays(3),
        'ends_at' => now()->addDays(3)->addHour(),
        'capacity' => 10,
    ]);

    $registration = Registration::query()->create([
        'user_id' => $employee->id,
        'workshop_id' => $workshop->id,
        'status' => 'confirmed',
    ]);

    $response = $this->actingAs($employee)
        ->delete(route('workshops.registrations.destroy', $workshop));

    $response->assertRedirect();

    $this->assertDatabaseMissing('registrations', [
        'id' => $registration->id,
    ]);
});

test('employee cannot register to a past workshop', function () {
    $employee = createRegistrationUserWithRole(RoleType::EMPLOYEE);
    $admin = createRegistrationUserWithRole(RoleType::ADMIN);

    $workshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Past Workshop',
        'description' => 'Past event',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->subDay()->addHour(),
        'capacity' => 10,
    ]);

    $response = $this->actingAs($employee)->from(route('workshops.index'))
        ->post(route('workshops.registrations.store', $workshop));

    $response->assertSessionHasErrors('workshop')
        ->assertRedirect(route('workshops.index'));
});

test('first user in waiting list is promoted when a confirmed participant cancels', function () {
    $admin = createRegistrationUserWithRole(RoleType::ADMIN);
    $confirmed = createRegistrationUserWithRole(RoleType::EMPLOYEE);
    $waitlistedOne = createRegistrationUserWithRole(RoleType::EMPLOYEE);
    $waitlistedTwo = createRegistrationUserWithRole(RoleType::EMPLOYEE);

    $workshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Promotion Workshop',
        'description' => 'Waitlist FIFO',
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(2)->addHour(),
        'capacity' => 1,
    ]);

    Registration::query()->create([
        'user_id' => $confirmed->id,
        'workshop_id' => $workshop->id,
        'status' => RegistrationStatus::CONFIRMED->value,
    ]);

    Registration::query()->create([
        'user_id' => $waitlistedOne->id,
        'workshop_id' => $workshop->id,
        'status' => RegistrationStatus::WAITLISTED->value,
        'waitlist_position' => 1,
    ]);

    Registration::query()->create([
        'user_id' => $waitlistedTwo->id,
        'workshop_id' => $workshop->id,
        'status' => RegistrationStatus::WAITLISTED->value,
        'waitlist_position' => 2,
    ]);

    $this->actingAs($confirmed)
        ->delete(route('workshops.registrations.destroy', $workshop))
        ->assertRedirect();

    $this->assertDatabaseMissing('registrations', [
        'user_id' => $confirmed->id,
        'workshop_id' => $workshop->id,
    ]);

    $this->assertDatabaseHas('registrations', [
        'user_id' => $waitlistedOne->id,
        'workshop_id' => $workshop->id,
        'status' => RegistrationStatus::CONFIRMED->value,
        'waitlist_position' => null,
    ]);

    $this->assertDatabaseHas('registrations', [
        'user_id' => $waitlistedTwo->id,
        'workshop_id' => $workshop->id,
        'status' => RegistrationStatus::WAITLISTED->value,
        'waitlist_position' => 2,
    ]);
});

test('employee cannot register to overlapping workshops when trying to confirm', function () {
    $admin = createRegistrationUserWithRole(RoleType::ADMIN);
    $employee = createRegistrationUserWithRole(RoleType::EMPLOYEE);

    $firstWorkshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'First Workshop',
        'description' => 'Already booked',
        'starts_at' => now()->addDays(3)->setTime(10, 0),
        'ends_at' => now()->addDays(3)->setTime(11, 0),
        'capacity' => 10,
    ]);

    $overlappingWorkshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Overlapping Workshop',
        'description' => 'Should be rejected',
        'starts_at' => now()->addDays(3)->setTime(10, 30),
        'ends_at' => now()->addDays(3)->setTime(11, 30),
        'capacity' => 10,
    ]);

    Registration::query()->create([
        'user_id' => $employee->id,
        'workshop_id' => $firstWorkshop->id,
        'status' => RegistrationStatus::CONFIRMED->value,
    ]);

    $response = $this->actingAs($employee)->from(route('workshops.index'))
        ->post(route('workshops.registrations.store', $overlappingWorkshop));

    $response->assertSessionHasErrors('workshop')
        ->assertRedirect(route('workshops.index'));

    $this->assertDatabaseMissing('registrations', [
        'user_id' => $employee->id,
        'workshop_id' => $overlappingWorkshop->id,
    ]);
});
