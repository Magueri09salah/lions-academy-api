<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isActive();
    }

    public function view(User $actor, Project $project): bool
    {
        return $actor->isActive();
    }

    public function create(User $actor): bool
    {
        return $actor->isActive();
    }

    public function update(User $actor, Project $project): bool
    {
        return $actor->isActive();
    }

    public function delete(User $actor, Project $project): bool
    {
        return $actor->isAdmin();
    }
}
