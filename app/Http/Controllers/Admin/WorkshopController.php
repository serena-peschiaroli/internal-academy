<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWorkshopRequest;
use App\Http\Requests\Admin\UpdateWorkshopRequest;
use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkshopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Workshop::class);

        $search = trim((string) $request->string('q'));
        $futureOnly = $request->boolean('future_only', true);

        $workshops = Workshop::query()
            ->with('creator:id,name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($futureOnly, fn ($query) => $query->where('starts_at', '>=', now()))
            ->orderBy('starts_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/workshops/Index', [
            'workshops' => $workshops,
            'filters' => [
                'q' => $search,
                'future_only' => $futureOnly,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Workshop::class);

        return Inertia::render('admin/workshops/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkshopRequest $request): RedirectResponse
    {
        Workshop::query()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return to_route('admin.workshops.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Workshop $workshop): Response
    {
        $this->authorize('update', $workshop);

        return Inertia::render('admin/workshops/Edit', [
            'workshop' => $workshop->load('creator:id,name'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkshopRequest $request, Workshop $workshop): RedirectResponse
    {
        $workshop->update($request->validated());

        return to_route('admin.workshops.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workshop $workshop): RedirectResponse
    {
        $this->authorize('delete', $workshop);
        $workshop->delete();

        return to_route('admin.workshops.index');
    }
}
