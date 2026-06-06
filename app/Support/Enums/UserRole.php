<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Lions Academy backoffice roles.
 *
 * - admin   : full access. Can manage users, formations, projects, trainers,
 *             inscriptions, contact messages, settings.
 * - editor  : content-only access. Cannot manage admin users or sensitive
 *             settings, but can edit formations, projects, trainers,
 *             programme, principles, media.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Editor = 'editor';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Editor => 'Éditeur',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    public function canManageContent(): bool
    {
        return true; // all backoffice roles can edit content
    }

    public function canManageUsers(): bool
    {
        return $this === self::Admin;
    }

    public function canManageSettings(): bool
    {
        return $this === self::Admin;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
