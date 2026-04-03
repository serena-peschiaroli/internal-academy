<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Workshop;
use App\Services\WorkshopRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class WorkshopRegistrationController extends Controller
{
    public function __construct(
        private readonly WorkshopRegistrationService $registrationService,
    ) {}

    /**
     * Register authenticated employee to a workshop.
     */
    public function store(Workshop $workshop): RedirectResponse
    {
        $this->authorize('create', Registration::class);

        try {
            $this->registrationService->register(auth()->user(), $workshop);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back();
    }

    /**
     * Cancel authenticated employee registration for a workshop.
     */
    public function destroy(Workshop $workshop): RedirectResponse
    {
        try {
            $this->registrationService->cancel(auth()->user(), $workshop);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back();
    }
}
