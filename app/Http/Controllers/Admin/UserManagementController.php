<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Mail\TemporaryPasswordMail;
use App\Models\Role;
use App\Models\User;
use App\RoleType;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class UserManagementController extends Controller
{
    /**
     * Show admin/employee user lists.
     */
    public function index(): Response
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $users = User::query()
            ->with('role:id,key,name')
            ->select(['id', 'name', 'email', 'phone', 'socials', 'avatar', 'role_id', 'is_active', 'email_verified_at', 'created_at'])
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/users/Index', [
            'admins' => $users
                ->filter(fn (User $user) => $user->role?->key === RoleType::ADMIN)
                ->values()
                ->map(fn (User $user) => $this->mapUser($user)),
            'employees' => $users
                ->filter(fn (User $user) => $user->role?->key === RoleType::EMPLOYEE)
                ->values()
                ->map(fn (User $user) => $this->mapUser($user)),
        ]);
    }

    /**
     * Show the user creation form for admins.
     */
    public function create(): Response
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return Inertia::render('admin/users/Create');
    }

    /**
     * Store a newly created user by admin.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $role = Role::query()->firstOrCreate(
            ['key' => $request->string('role')->toString()],
            ['name' => ucfirst($request->string('role')->toString())],
        );

        $temporaryPassword = Str::password(12);

        $user = User::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => filled($request->input('phone')) ? $request->input('phone') : null,
            'socials' => $this->normalizeSocials($request->input('socials', [])),
            'password' => $temporaryPassword,
            'role_id' => $role->id,
            'first_access' => true,
            'is_active' => true,
            'temporary_password_expires_at' => now()->addDay(),
        ]);

        if ($request->hasFile('avatar')) {
            $user->update([
                'avatar' => $request->file('avatar')->store('avatars', 'public'),
            ]);
        }

        Log::info('Queueing temporary password email from admin user creation', [
            'created_by_user_id' => $request->user()?->id,
            'user_id' => $user->id,
            'email' => $user->email,
            'queue_connection' => config('queue.default'),
            'mail_mailer' => config('mail.default'),
        ]);

        Mail::to($user->email)->queue(new TemporaryPasswordMail($user, $temporaryPassword));

        return to_route('admin.users.index');
    }

    /**
     * Show a single user detail page.
     */
    public function show(User $user): Response
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $user->loadMissing('role:id,key,name');

        return Inertia::render('admin/users/Show', [
            'user' => $this->mapUser($user),
        ]);
    }

    /**
     * Show user edit form.
     */
    public function edit(User $user): Response
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $user->loadMissing('role:id,key,name');

        return Inertia::render('admin/users/Edit', [
            'user' => $this->mapUser($user),
        ]);
    }

    /**
     * Update an existing user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $role = Role::query()->firstOrCreate(
            ['key' => $request->string('role')->toString()],
            ['name' => ucfirst($request->string('role')->toString())],
        );

        $payload = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => filled($request->input('phone')) ? $request->input('phone') : null,
            'socials' => $this->normalizeSocials($request->input('socials', [])),
            'role_id' => $role->id,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $payload['password'] = $request->string('password')->toString();
        }

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

        $user->update($payload);

        return to_route('admin.users.index');
    }

    /**
     * Delete an existing user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        if ($request->user()?->is($user)) {
            return back()->withErrors([
                'delete' => 'You cannot delete your own account from this page.',
            ]);
        }

        $user->delete();

        return to_route('admin.users.index');
    }

    /**
     * Normalize user payload for Inertia pages.
     */
    protected function mapUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'socials' => $user->socials,
            'avatar' => $user->avatar,
            'avatar_url' => $user->avatar ? Storage::disk('public')->url($user->avatar) : null,
            'role' => $user->role?->key?->value ?? $user->role?->key,
            'is_active' => $user->is_active,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }

    protected function normalizeSocials(array $socials): ?array
    {
        $allowed = Arr::only($socials, ['reddit', 'linkedin', 'facebook', 'instagram', 'website']);
        $filtered = array_filter($allowed, static fn ($value) => filled($value));

        return $filtered !== [] ? $filtered : null;
    }
}
