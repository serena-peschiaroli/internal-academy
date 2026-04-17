<?php

use App\Mail\WorkshopReminderMail;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use App\RoleType;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

function createReminderUserWithRole(RoleType $role): User
{
    $roleModel = Role::query()->firstOrCreate(
        ['key' => $role->value],
        ['name' => Str::title($role->value)],
    );

    return User::factory()->create([
        'role_id' => $roleModel->id,
    ]);
}

test('academy remind sends reminders only to confirmed participants of tomorrow workshops', function () {
    Mail::fake();

    $this->travelTo(now()->startOfDay());

    $admin = createReminderUserWithRole(RoleType::ADMIN);
    $confirmedOne = createReminderUserWithRole(RoleType::EMPLOYEE);
    $confirmedTwo = createReminderUserWithRole(RoleType::EMPLOYEE);
    $waitlisted = createReminderUserWithRole(RoleType::EMPLOYEE);
    $confirmedOtherDay = createReminderUserWithRole(RoleType::EMPLOYEE);

    $workshopTomorrow = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Tomorrow Workshop',
        'description' => 'Reminder target',
        'starts_at' => now()->addDay()->setTime(10, 0),
        'ends_at' => now()->addDay()->setTime(11, 0),
        'capacity' => 2,
    ]);

    $workshopOtherDay = Workshop::query()->create([
        'user_id' => $admin->id,
        'title' => 'Other Day Workshop',
        'description' => 'Should not be mailed',
        'starts_at' => now()->addDays(2)->setTime(10, 0),
        'ends_at' => now()->addDays(2)->setTime(11, 0),
        'capacity' => 2,
    ]);

    Registration::factory()->confirmed()->for($confirmedOne)->for($workshopTomorrow)->create();
    Registration::factory()->confirmed()->for($confirmedTwo)->for($workshopTomorrow)->create();
    Registration::factory()->waitlisted(1)->for($waitlisted)->for($workshopTomorrow)->create();
    Registration::factory()->confirmed()->for($confirmedOtherDay)->for($workshopOtherDay)->create();

    $this->artisan('academy:remind')
        ->assertSuccessful()
        ->expectsOutputToContain('Sent 2 reminder emails');

    Mail::assertSent(WorkshopReminderMail::class, 2);

    Mail::assertSent(WorkshopReminderMail::class, fn (WorkshopReminderMail $mail) => $mail->hasTo($confirmedOne->email));
    Mail::assertSent(WorkshopReminderMail::class, fn (WorkshopReminderMail $mail) => $mail->hasTo($confirmedTwo->email));

    Mail::assertNotSent(WorkshopReminderMail::class, fn (WorkshopReminderMail $mail) => $mail->hasTo($waitlisted->email));
    Mail::assertNotSent(WorkshopReminderMail::class, fn (WorkshopReminderMail $mail) => $mail->hasTo($confirmedOtherDay->email));
});
