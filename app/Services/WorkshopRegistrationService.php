<?php

namespace App\Services;

use App\RegistrationStatus;
use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkshopRegistrationService
{
    /**
     * Register user to workshop using a transaction and row locks.
     *
     * @throws ValidationException
     */
    public function register(User $user, Workshop $workshop): void
    {
        DB::transaction(function () use ($user, $workshop): void {
            $lockedWorkshop = Workshop::query()
                ->whereKey($workshop->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedWorkshop->starts_at->isPast()) {
                throw ValidationException::withMessages([
                    'workshop' => 'Cannot register to a workshop that has already started.',
                ]);
            }

            $alreadyRegistered = Registration::query()
                ->where('workshop_id', $lockedWorkshop->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->exists();

            if ($alreadyRegistered) {
                throw ValidationException::withMessages([
                    'workshop' => 'You are already registered for this workshop.',
                ]);
            }

            $confirmedCount = Registration::query()
                ->where('workshop_id', $lockedWorkshop->id)
                ->where('status', RegistrationStatus::CONFIRMED->value)
                ->lockForUpdate()
                ->count();

            $overlappingConfirmedWorkshop = Registration::query()
                ->where('user_id', $user->id)
                ->where('status', RegistrationStatus::CONFIRMED->value)
                ->whereHas('workshop', function ($query) use ($lockedWorkshop): void {
                    $query->where('starts_at', '<', $lockedWorkshop->ends_at)
                        ->where('ends_at', '>', $lockedWorkshop->starts_at);
                })
                ->lockForUpdate()
                ->exists();

            if ($overlappingConfirmedWorkshop) {
                throw ValidationException::withMessages([
                    'workshop' => 'You cannot register for overlapping workshops.',
                ]);
            }

            if ($confirmedCount >= $lockedWorkshop->capacity) {
                $nextWaitlistPosition = (Registration::query()
                    ->where('workshop_id', $lockedWorkshop->id)
                    ->where('status', RegistrationStatus::WAITLISTED->value)
                    ->lockForUpdate()
                    ->max('waitlist_position') ?? 0) + 1;

                Registration::query()->create([
                    'user_id' => $user->id,
                    'workshop_id' => $lockedWorkshop->id,
                    'status' => RegistrationStatus::WAITLISTED->value,
                    'waitlist_position' => $nextWaitlistPosition,
                ]);

                return;
            }

            Registration::query()->create([
                'user_id' => $user->id,
                'workshop_id' => $lockedWorkshop->id,
                'status' => RegistrationStatus::CONFIRMED->value,
                'waitlist_position' => null,
            ]);
        }, 3);
    }

    /**
     * Cancel user registration using a transaction and row locks.
     *
     * @throws ValidationException
     */
    public function cancel(User $user, Workshop $workshop): void
    {
        DB::transaction(function () use ($user, $workshop): void {
            $lockedWorkshop = Workshop::query()
                ->whereKey($workshop->id)
                ->lockForUpdate()
                ->firstOrFail();

            $registration = Registration::query()
                ->where('workshop_id', $lockedWorkshop->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $registration) {
                throw ValidationException::withMessages([
                    'workshop' => 'No registration found for this workshop.',
                ]);
            }

            if ($lockedWorkshop->starts_at->isPast()) {
                throw ValidationException::withMessages([
                    'workshop' => 'Cannot cancel a workshop that has already started.',
                ]);
            }

            $wasConfirmed = $registration->status === RegistrationStatus::CONFIRMED->value;
            $registration->delete();

            if (! $wasConfirmed) {
                return;
            }

            $nextWaitlistedRegistration = Registration::query()
                ->where('workshop_id', $lockedWorkshop->id)
                ->where('status', RegistrationStatus::WAITLISTED->value)
                ->orderBy('waitlist_position')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $nextWaitlistedRegistration) {
                return;
            }

            $nextWaitlistedRegistration->update([
                'status' => RegistrationStatus::CONFIRMED->value,
                'waitlist_position' => null,
            ]);
        }, 3);
    }
}
