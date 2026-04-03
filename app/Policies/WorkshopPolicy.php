<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workshop;

class WorkshopPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function view(User $user, Workshop $workshop): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Workshop $workshop): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Workshop $workshop): bool
    {
        return $user->isAdmin();
    }
}

