<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Trainer;
use App\Models\User;

class TrainerPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isActive();
    }

    public function view(User $actor, Trainer $trainer): bool
    {
        return $actor->isActive();
    }

    public function create(User $actor): bool
    {
        return $actor->isActive();
    }

    public function update(User $actor, Trainer $trainer): bool
    {
        return $actor->isActive();
    }

    public function delete(User $actor, Trainer $trainer): bool
    {
        return $actor->isAdmin();
    }
}
