<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RegistrationConcours;
use App\Models\User;

class RegistrationConcoursPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isActive();
    }

    public function view(User $actor, RegistrationConcours $lead): bool
    {
        return $actor->isActive();
    }

    public function update(User $actor, RegistrationConcours $lead): bool
    {
        return $actor->isActive();
    }

    public function delete(User $actor, RegistrationConcours $lead): bool
    {
        return $actor->isAdmin();
    }

    public function export(User $actor): bool
    {
        return $actor->isActive();
    }
}
