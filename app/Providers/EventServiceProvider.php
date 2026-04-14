<?php

namespace App\Providers;

use App\Listeners\LogTemporaryPasswordMailFailure;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageFailed;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        MessageFailed::class => [
            LogTemporaryPasswordMailFailure::class,
        ],
    ];
}
