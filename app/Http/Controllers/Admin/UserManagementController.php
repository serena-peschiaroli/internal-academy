<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class UserManagementController extends Controller
{
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

        User::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
            'role_id' => $role->id,
        ]);

        return to_route('admin.users.create');
    }
}
