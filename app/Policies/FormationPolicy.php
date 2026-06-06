<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Formation;
use App\Models\User;

class FormationPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isActive();
    }

    public function view(User $actor, Formation $formation): bool
    {
        return $actor->isActive();
    }

    public function create(User $actor): bool
    {
        return $actor->isActive();
    }

    public function update(User $actor, Formation $formation): bool
    {
        return $actor->isActive();
    }

    public function delete(User $actor, Formation $formation): bool
    {
        return $actor->isAdmin();
    }
}
