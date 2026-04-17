<?php

namespace Database\Factories;

use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use App\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    /**
     * Default state: a confirmed registration for a new user on a new workshop.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'workshop_id' => Workshop::factory(),
            'status' => RegistrationStatus::CONFIRMED->value,
            'waitlist_position' => null,
        ];
    }

    /**
     * Confirmed registration state (explicit alias for clarity in tests).
     */
    public function confirmed(): static
    {
        return $this->state([
            'status' => RegistrationStatus::CONFIRMED->value,
            'waitlist_position' => null,
        ]);
    }

    /**
     * Waitlisted registration state with an explicit queue position.
     */
    public function waitlisted(int $position = 1): static
    {
        return $this->state([
            'status' => RegistrationStatus::WAITLISTED->value,
            'waitlist_position' => $position,
        ]);
    }
}
