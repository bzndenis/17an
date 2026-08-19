<?php

namespace App\Enums;

enum CompetitionSystem: string
{
    case Knockout = 'knockout';
    case Point = 'point';
    case League = 'league';
    case GroupKnockout = 'group_knockout';

    public function label(): string
    {
        return match ($this) {
            self::Knockout => 'Knockout',
            self::Point => 'Sistem Poin',
            self::League => 'Liga',
            self::GroupKnockout => 'Grup + Knockout',
        };
    }
}
