<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;

/**
 * Authorisation for registration management.
 *
 *   - Editors can view + update status + add notes
 *   - Admins additionally can delete and export
 *
 * Public POST is unauthenticated and is gated by the FormRequest
 * (which always authorises) + the route's rate-limit / honeypot stack.
 */
class RegistrationPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isActive();
    }

    public function view(User $actor, Registration $registration): bool
    {
        return $actor->isActive();
    }

    public function update(User $actor, Registration $registration): bool
    {
        return $actor->isActive();
    }

    public function delete(User $actor, Registration $registration): bool
    {
        return $actor->isAdmin();
    }

    public function export(User $actor): bool
    {
        return $actor->isActive();
    }
}
