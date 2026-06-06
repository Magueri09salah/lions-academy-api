<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

/**
 * Same authorisation matrix as RegistrationPolicy:
 *   - any active staff can view/update/add notes
 *   - only admins can delete
 */
class ContactMessagePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isActive();
    }

    public function view(User $actor, ContactMessage $message): bool
    {
        return $actor->isActive();
    }

    public function update(User $actor, ContactMessage $message): bool
    {
        return $actor->isActive();
    }

    public function delete(User $actor, ContactMessage $message): bool
    {
        return $actor->isAdmin();
    }
}
