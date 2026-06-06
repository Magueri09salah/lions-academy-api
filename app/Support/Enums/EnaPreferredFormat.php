<?php

declare(strict_types=1);

namespace App\Support\Enums;

enum EnaPreferredFormat: string
{
    case InPersonMarrakech = 'in_person_marrakech';
    case Online = 'online';
    case OnlineFromOtherCity = 'online_from_other_city';

    public function label(): string
    {
        return match ($this) {
            self::InPersonMarrakech => 'Présentiel à Marrakech',
            self::Online => 'En ligne',
            self::OnlineFromOtherCity => "J'habite dans une autre ville, donc uniquement en ligne",
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    /** @return array<int, array{value:string,label:string}> */
    public static function options(): array
    {
        return array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases(),
        );
    }
}
