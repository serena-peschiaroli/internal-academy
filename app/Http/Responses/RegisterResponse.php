<?php

namespace App\Http\Responses;

use App\RoleType;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request): mixed
    {
        $user = $request->user();

        $target = $user?->role?->key === RoleType::ADMIN
            ? route('admin.workshops.index')
            : route('workshops.index');

        return redirect()->to($target);
    }
}
