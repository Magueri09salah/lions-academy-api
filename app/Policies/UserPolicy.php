<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->isAdmin() || $actor->id === $target->id;
    }

    public function create(User $actor): bool
    {
        return $actor->isAdmin();
    }

    public function update(User $actor, User $target): bool
    {
        // Admins manage everyone; editors can update their own profile only.
        return $actor->isAdmin() || $actor->id === $target->id;
    }

    public function delete(User $actor, User $target): bool
    {
        // Never let an admin delete themselves; never let editors delete anyone.
        return $actor->isAdmin() && $actor->id !== $target->id;
    }

    public function changeRole(User $actor, User $target): bool
    {
        return $actor->isAdmin() && $actor->id !== $target->id;
    }
}
