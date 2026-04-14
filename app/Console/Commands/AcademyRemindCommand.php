<?php

namespace App\Console\Commands;

use App\Mail\WorkshopReminderMail;
use App\Models\Workshop;
use App\RegistrationStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AcademyRemindCommand extends Command
{
    protected $signature = 'academy:remind {--date= : Target date in Y-m-d format}';

    protected $description = 'Send reminder emails to confirmed participants of tomorrow workshops';

    public function handle(): int
    {
        $targetDate = $this->option('date') ?: now()->addDay()->toDateString();

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $targetDate)) {
            $this->error('Invalid --date format. Use Y-m-d.');

            return self::FAILURE;
        }

        $workshops = Workshop::query()
            ->whereDate('starts_at', $targetDate)
            ->with([
                'participants' => fn ($query) => $query
                    ->wherePivot('status', RegistrationStatus::CONFIRMED->value),
            ])
            ->orderBy('starts_at')
            ->get();

        $sentMails = 0;

        foreach ($workshops as $workshop) {
            foreach ($workshop->participants as $participant) {
                Mail::to($participant)->send(new WorkshopReminderMail($workshop, $participant));
                $sentMails++;
            }
        }

        $this->info(sprintf(
            'Sent %d reminder %s for %d workshop %s scheduled on %s.',
            $sentMails,
            Str::plural('email', $sentMails),
            $workshops->count(),
            Str::plural('instance', $workshops->count()),
            $targetDate,
        ));

        return self::SUCCESS;
    }
}
