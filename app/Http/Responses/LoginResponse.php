<?php

namespace App\Http\Responses;

use App\RoleType;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request): mixed
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('account-inactive');
        }

        if ($user?->first_access) {
            if ($user->temporary_password_expires_at?->isPast()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('password.request')
                    ->with('status', 'Temporary password expired. Request a password reset link.');
            }

            return redirect()->route('secure-password.edit');
        }

        $target = $user?->role?->key === RoleType::ADMIN
            ? route('admin.workshops.index')
            : route('workshops.index');

        return redirect()->intended($target);
    }
}
