<?php

use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use App\RegistrationStatus;
use App\RoleType;
use Illuminate\Support\Str;

function createStatsUserWithRole(RoleType $role): User
{
    $roleModel = Role::query()->firstOrCreate(
        ['key' => $role->value],
        ['name' => Str::title($role->value)],
    );

    return User::factory()->create([
        'role_id' => $roleModel->id,
    ]);
}

test('admin can fetch workshop stats for dashboard polling', function () {
    $admin = createStatsUserWithRole(RoleType::ADMIN);
    $employeeOne = createStatsUserWithRole(RoleType::EMPLOYEE);
    $employeeTwo = createStatsUserWithRole(RoleType::EMPLOYEE);
    $employeeThree = createStatsUserWithRole(RoleType::EMPLOYEE);

    $popularWorkshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Popular Workshop',
        'description' => 'Most booked one',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
        'capacity' => 10,
    ]);

    $secondWorkshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Second Workshop',
        'description' => 'Less booked',
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(2)->addHour(),
        'capacity' => 10,
    ]);

    $pastWorkshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Past Workshop',
        'description' => 'Should be excluded',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->subDay()->addHour(),
        'capacity' => 10,
    ]);

    Registration::query()->create([
        'user_id' => $employeeOne->id,
        'workshop_id' => $popularWorkshop->id,
        'status' => RegistrationStatus::CONFIRMED->value,
    ]);

    Registration::query()->create([
        'user_id' => $employeeTwo->id,
        'workshop_id' => $popularWorkshop->id,
        'status' => RegistrationStatus::CONFIRMED->value,
    ]);

    Registration::query()->create([
        'user_id' => $employeeThree->id,
        'workshop_id' => $popularWorkshop->id,
        'status' => RegistrationStatus::WAITLISTED->value,
        'waitlist_position' => 1,
    ]);

    Registration::query()->create([
        'user_id' => $employeeOne->id,
        'workshop_id' => $secondWorkshop->id,
        'status' => RegistrationStatus::CONFIRMED->value,
    ]);

    Registration::query()->create([
        'user_id' => $employeeTwo->id,
        'workshop_id' => $pastWorkshop->id,
        'status' => RegistrationStatus::CONFIRMED->value,
    ]);

    $this->actingAs($admin)
        ->getJson(route('admin.stats.workshops'))
        ->assertOk()
        ->assertJsonPath('workshops_count', 2)
        ->assertJsonPath('confirmed_registrations_count', 3)
        ->assertJsonPath('waitlisted_registrations_count', 1)
        ->assertJsonPath('most_popular_workshop.id', $popularWorkshop->id)
        ->assertJsonPath('most_popular_workshop.title', 'Popular Workshop')
        ->assertJsonPath('most_popular_workshop.confirmed_count', 2);
});

test('employee cannot fetch admin workshop stats endpoint', function () {
    $employee = createStatsUserWithRole(RoleType::EMPLOYEE);

    $this->actingAs($employee)
        ->getJson(route('admin.stats.workshops'))
        ->assertForbidden();
});
