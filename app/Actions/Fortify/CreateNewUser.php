<?php

namespace App\Actions\Fortify;

use App\Concerns\ProfileValidationRules;
use App\Mail\TemporaryPasswordMail;
use App\Models\Role;
use App\Models\User;
use App\RoleType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
        ])->validate();

        $roleId = Role::query()->firstOrCreate(
            ['key' => RoleType::EMPLOYEE->value],
            ['name' => 'Employee'],
        )->id;

        $temporaryPassword = Str::password(12);

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $temporaryPassword,
            'role_id' => $roleId,
            'first_access' => true,
            'is_active' => true,
            'temporary_password_expires_at' => now()->addDay(),
        ]);

        Log::info('Queueing temporary password email from public registration', [
            'user_id' => $user->id,
            'email' => $user->email,
            'queue_connection' => config('queue.default'),
            'mail_mailer' => config('mail.default'),
        ]);

        Mail::to($user->email)->queue(new TemporaryPasswordMail($user, $temporaryPassword));

        return $user;
    }
}
