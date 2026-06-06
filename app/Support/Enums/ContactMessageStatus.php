<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Lifecycle for messages submitted through the public contact form.
 *
 *   new       → just arrived, not yet read by staff (gold badge)
 *   read      → an admin opened the detail page
 *   replied   → admin marked it as handled / answered by email
 *   archived  → kept for history, no further action expected
 */
enum ContactMessageStatus: string
{
    case New = 'new';
    case Read = 'read';
    case Replied = 'replied';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Nouveau',
            self::Read => 'Lu',
            self::Replied => 'Répondu',
            self::Archived => 'Archivé',
        };
    }

    /**
     * Colour token consumed by the front-end <StatusBadge> component.
     * Same vocabulary as RegistrationStatus so the badge component is reused.
     */
    public function tone(): string
    {
        return match ($this) {
            self::New => 'gold',
            self::Read => 'ink',
            self::Replied => 'success',
            self::Archived => 'sand',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    /**
     * @return array<int, array{value:string,label:string,tone:string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label(), 'tone' => $c->tone()],
            self::cases(),
        );
    }
}
