<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Workshop;
use App\RegistrationStatus;
use Illuminate\Http\JsonResponse;

class WorkshopStatsController extends Controller
{
    /**
     * Return admin workshop stats for dashboard polling.
     */
    public function __invoke(): JsonResponse
    {
        $this->authorize('viewAny', Workshop::class);

        $upcomingWorkshopsQuery = Workshop::query()
            ->where('starts_at', '>=', now());

        $mostPopularWorkshop = (clone $upcomingWorkshopsQuery)
            ->withCount([
                'registrations as confirmed_count' => fn ($query) => $query->where('status', RegistrationStatus::CONFIRMED->value),
            ])
            ->orderByDesc('confirmed_count')
            ->orderBy('starts_at')
            ->first();

        $confirmedRegistrationsCount = Registration::query()
            ->where('status', RegistrationStatus::CONFIRMED->value)
            ->whereHas('workshop', fn ($query) => $query->where('starts_at', '>=', now()))
            ->count();

        $waitlistedRegistrationsCount = Registration::query()
            ->where('status', RegistrationStatus::WAITLISTED->value)
            ->whereHas('workshop', fn ($query) => $query->where('starts_at', '>=', now()))
            ->count();

        return response()->json([
            'workshops_count' => (clone $upcomingWorkshopsQuery)->count(),
            'confirmed_registrations_count' => $confirmedRegistrationsCount,
            'waitlisted_registrations_count' => $waitlistedRegistrationsCount,
            'most_popular_workshop' => $mostPopularWorkshop ? [
                'id' => $mostPopularWorkshop->id,
                'title' => $mostPopularWorkshop->title,
                'starts_at' => $mostPopularWorkshop->starts_at,
                'confirmed_count' => $mostPopularWorkshop->confirmed_count,
            ] : null,
            'generated_at' => now()->toIso8601String(),
        ]);
    }
}
