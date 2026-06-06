<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MediaAsset;
use App\Models\User;

class MediaAssetPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isActive();
    }

    public function view(User $actor, MediaAsset $media): bool
    {
        if ($media->visibility === 'public') {
            return true;
        }

        return $actor->isAdmin() || $actor->id === $media->uploaded_by;
    }

    public function create(User $actor): bool
    {
        return $actor->isActive();
    }

    public function delete(User $actor, MediaAsset $media): bool
    {
        return $actor->isAdmin() || $actor->id === $media->uploaded_by;
    }
}
