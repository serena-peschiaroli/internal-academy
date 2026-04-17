<?php

use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
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

    Registration::factory()->confirmed()->for($employeeOne)->for($popularWorkshop)->create();
    Registration::factory()->confirmed()->for($employeeTwo)->for($popularWorkshop)->create();
    Registration::factory()->waitlisted(1)->for($employeeThree)->for($popularWorkshop)->create();
    Registration::factory()->confirmed()->for($employeeOne)->for($secondWorkshop)->create();
    Registration::factory()->confirmed()->for($employeeTwo)->for($pastWorkshop)->create();

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

test('stats endpoint returns zeros and null most_popular when no workshops exist', function () {
    $admin = createStatsUserWithRole(RoleType::ADMIN);

    $this->actingAs($admin)
        ->getJson(route('admin.stats.workshops'))
        ->assertOk()
        ->assertJsonPath('workshops_count', 0)
        ->assertJsonPath('confirmed_registrations_count', 0)
        ->assertJsonPath('waitlisted_registrations_count', 0)
        ->assertJsonPath('most_popular_workshop', null);
});

test('stats endpoint excludes past workshops and their registrations from all counts', function () {
    $admin = createStatsUserWithRole(RoleType::ADMIN);
    $employeeOne = createStatsUserWithRole(RoleType::EMPLOYEE);
    $employeeTwo = createStatsUserWithRole(RoleType::EMPLOYEE);

    $pastWorkshop = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Past Workshop',
        'description' => 'Already over',
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->subDay(),
        'capacity' => 10,
    ]);

    Registration::factory()->confirmed()->for($employeeOne)->for($pastWorkshop)->create();
    Registration::factory()->waitlisted(1)->for($employeeTwo)->for($pastWorkshop)->create();

    $this->actingAs($admin)
        ->getJson(route('admin.stats.workshops'))
        ->assertOk()
        ->assertJsonPath('workshops_count', 0)
        ->assertJsonPath('confirmed_registrations_count', 0)
        ->assertJsonPath('waitlisted_registrations_count', 0)
        ->assertJsonPath('most_popular_workshop', null);
});

test('stats most_popular_workshop breaks ties by earliest start date', function () {
    $admin = createStatsUserWithRole(RoleType::ADMIN);
    $employeeOne = createStatsUserWithRole(RoleType::EMPLOYEE);
    $employeeTwo = createStatsUserWithRole(RoleType::EMPLOYEE);

    // Two workshops with identical confirmed count — earlier one must win.
    $earlier = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Earlier Workshop',
        'description' => 'Starts first',
        'starts_at' => now()->addDays(1),
        'ends_at' => now()->addDays(1)->addHour(),
        'capacity' => 10,
    ]);

    $later = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Later Workshop',
        'description' => 'Starts second',
        'starts_at' => now()->addDays(5),
        'ends_at' => now()->addDays(5)->addHour(),
        'capacity' => 10,
    ]);

    Registration::factory()->confirmed()->for($employeeOne)->for($earlier)->create();
    Registration::factory()->confirmed()->for($employeeTwo)->for($later)->create();

    $this->actingAs($admin)
        ->getJson(route('admin.stats.workshops'))
        ->assertOk()
        ->assertJsonPath('most_popular_workshop.id', $earlier->id);
});

test('unauthenticated request to stats endpoint is redirected', function () {
    $this->getJson(route('admin.stats.workshops'))
        ->assertUnauthorized();
});
