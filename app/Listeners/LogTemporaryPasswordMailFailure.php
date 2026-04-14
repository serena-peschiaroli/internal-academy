<?php

namespace App\Listeners;

use App\Mail\TemporaryPasswordMail;
use Illuminate\Mail\Events\MessageFailed;
use Illuminate\Support\Facades\Log;

class LogTemporaryPasswordMailFailure
{
    /**
     * Handle the event.
     */
    public function handle(MessageFailed $event): void
    {
        $data = $event->data;
        $message = $data['mailable'] ?? null;

        if (! $message instanceof TemporaryPasswordMail) {
            return;
        }

        Log::error('Temporary password email failed', [
            'user_id' => $message->user->id ?? null,
            'email' => $message->user->email ?? null,
            'mailer' => config('mail.default'),
        ]);
    }
}
