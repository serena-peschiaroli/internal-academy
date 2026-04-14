<?php

namespace App\Http\Controllers\Auth;

use App\Concerns\PasswordValidationRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SecurePasswordController extends Controller
{
    use PasswordValidationRules;

    /**
     * Show first-access secure password page.
     */
    public function edit(Request $request): Response
    {
        abort_unless($request->user(), 403);

        return Inertia::render('auth/SecurePassword');
    }

    /**
     * Save new password for first access and force a fresh login.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => $this->passwordRules(),
        ]);

        $user = $request->user();
        abort_unless($user, 403);

        $user->forceFill([
            'password' => $request->string('password')->toString(),
            'first_access' => false,
            'temporary_password_expires_at' => null,
            'email_verified_at' => now(),
        ])->save();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Password updated. Please log in with your new password.');
    }
}
