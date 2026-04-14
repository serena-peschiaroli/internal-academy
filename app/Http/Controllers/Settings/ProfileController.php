<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'socials' => [
                    'reddit' => $user->socials['reddit'] ?? null,
                    'linkedin' => $user->socials['linkedin'] ?? null,
                    'facebook' => $user->socials['facebook'] ?? null,
                    'instagram' => $user->socials['instagram'] ?? null,
                    'website' => $user->socials['website'] ?? null,
                ],
                'avatar' => $user->avatar,
                'avatar_url' => $user->avatar ? Storage::disk('public')->url($user->avatar) : null,
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();
        $incomingAvatar = $request->file('avatar');

        Log::info('Profile update request received', [
            'user_id' => $user->id,
            'method' => $request->method(),
            'content_type' => $request->header('content-type'),
            'has_avatar_key' => $request->has('avatar'),
            'has_file_avatar' => $request->hasFile('avatar'),
            'remove_avatar' => $request->boolean('remove_avatar'),
            'avatar_is_valid' => $incomingAvatar?->isValid(),
            'avatar_upload_error' => $incomingAvatar?->getError(),
            'avatar_upload_error_message' => $incomingAvatar?->getErrorMessage(),
            'avatar_original_name' => $incomingAvatar?->getClientOriginalName(),
            'avatar_mime' => $incomingAvatar?->getClientMimeType(),
            'avatar_size' => $incomingAvatar?->getSize(),
            'validated_keys' => array_keys($validated),
        ]);

        $payload = Arr::only($validated, ['name', 'email']);
        $payload['phone'] = filled($validated['phone'] ?? null) ? $validated['phone'] : null;

        $socials = Arr::only($validated['socials'] ?? [], ['reddit', 'linkedin', 'facebook', 'instagram', 'website']);
        $socials = array_filter($socials, static fn ($value) => filled($value));
        $payload['socials'] = $socials !== [] ? $socials : null;

        if ($request->boolean('remove_avatar') && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $payload['avatar'] = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $payload['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($payload);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Log::info('Profile update saved', [
            'user_id' => $user->id,
            'saved_phone' => $user->phone,
            'saved_avatar' => $user->avatar,
            'saved_social_keys' => array_keys($user->socials ?? []),
        ]);

        return to_route('profile.edit', ['user' => $user->id]);
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
