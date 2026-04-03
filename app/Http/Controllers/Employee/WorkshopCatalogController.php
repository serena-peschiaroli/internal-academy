<?php

namespace App\Http\Controllers\Employee;

use App\Models\Registration;
use App\Http\Controllers\Controller;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkshopCatalogController extends Controller
{
    /**
     * Display future workshops for employees.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Workshop::class);

        $workshops = Workshop::query()
            ->with('creator:id,name')
            ->withCount([
                'registrations as confirmed_count' => fn ($query) => $query->where('status', 'confirmed'),
            ])
            ->addSelect([
                'user_registration_status' => Registration::query()
                    ->select('status')
                    ->whereColumn('workshop_id', 'workshops.id')
                    ->where('user_id', $request->user()->id)
                    ->limit(1),
                'user_waitlist_position' => Registration::query()
                    ->select('waitlist_position')
                    ->whereColumn('workshop_id', 'workshops.id')
                    ->where('user_id', $request->user()->id)
                    ->limit(1),
            ])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->paginate(10)
            ->through(fn (Workshop $workshop) => [
                'id' => $workshop->id,
                'title' => $workshop->title,
                'description' => $workshop->description,
                'starts_at' => $workshop->starts_at,
                'ends_at' => $workshop->ends_at,
                'capacity' => $workshop->capacity,
                'confirmed_count' => $workshop->confirmed_count,
                'available_seats' => max(0, $workshop->capacity - $workshop->confirmed_count),
                'registration_status' => $workshop->user_registration_status,
                'user_waitlist_position' => $workshop->user_waitlist_position,
                'creator' => $workshop->creator ? [
                    'id' => $workshop->creator->id,
                    'name' => $workshop->creator->name,
                ] : null,
            ]);

        return Inertia::render('employee/workshops/Index', [
            'workshops' => $workshops,
        ]);
    }
}
