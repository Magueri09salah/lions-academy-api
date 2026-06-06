<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProgramMonth;
use App\Models\User;

/**
 * Same matrix as PrinciplePolicy: editors manage content, admin-only delete.
 */
class ProgramMonthPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isActive();
    }

    public function view(User $actor, ProgramMonth $month): bool
    {
        return $actor->isActive();
    }

    public function create(User $actor): bool
    {
        return $actor->isActive();
    }

    public function update(User $actor, ProgramMonth $month): bool
    {
        return $actor->isActive();
    }

    public function delete(User $actor, ProgramMonth $month): bool
    {
        return $actor->isAdmin();
    }
}
