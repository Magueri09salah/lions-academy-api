<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Principle;
use App\Models\User;

/**
 * Content management for principles.
 *
 * Editors and admins can both manage content (per UserRole::canManageContent()).
 * Only admins can delete.
 */
class PrinciplePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isActive();
    }

    public function view(User $actor, Principle $principle): bool
    {
        return $actor->isActive();
    }

    public function create(User $actor): bool
    {
        return $actor->isActive();
    }

    public function update(User $actor, Principle $principle): bool
    {
        return $actor->isActive();
    }

    public function delete(User $actor, Principle $principle): bool
    {
        return $actor->isAdmin();
    }
}
