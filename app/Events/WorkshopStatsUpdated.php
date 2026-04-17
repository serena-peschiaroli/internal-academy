<?php

namespace App\Events;

use App\Models\Registration;
use App\Models\Workshop;
use App\RegistrationStatus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkshopStatsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $stats;

    public function __construct()
    {
        $upcomingWorkshopsQuery = Workshop::query()->where('starts_at', '>=', now());

        $mostPopularWorkshop = (clone $upcomingWorkshopsQuery)
            ->withCount([
                'registrations as confirmed_count' => fn ($query) => $query->where('status', RegistrationStatus::CONFIRMED->value),
            ])
            ->orderByDesc('confirmed_count')
            ->orderBy('starts_at')
            ->first();

        $this->stats = [
            'workshops_count' => (clone $upcomingWorkshopsQuery)->count(),
            'confirmed_registrations_count' => Registration::query()
                ->where('status', RegistrationStatus::CONFIRMED->value)
                ->whereHas('workshop', fn ($q) => $q->where('starts_at', '>=', now()))
                ->count(),
            'waitlisted_registrations_count' => Registration::query()
                ->where('status', RegistrationStatus::WAITLISTED->value)
                ->whereHas('workshop', fn ($q) => $q->where('starts_at', '>=', now()))
                ->count(),
            'most_popular_workshop' => $mostPopularWorkshop ? [
                'id' => $mostPopularWorkshop->id,
                'title' => $mostPopularWorkshop->title,
                'starts_at' => $mostPopularWorkshop->starts_at,
                'confirmed_count' => $mostPopularWorkshop->confirmed_count,
            ] : null,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Only admins can subscribe to this private channel.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.workshop-stats'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'stats.updated';
    }
}
