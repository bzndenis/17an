<?php

namespace App\Enums;

enum MatchStatus: string
{
    case Scheduled = 'scheduled';
    case Live = 'live';
    case Finished = 'finished';
    case Cancelled = 'cancelled';
    case Walkover = 'walkover';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Terjadwal',
            self::Live => 'Live',
            self::Finished => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::Walkover => 'Walkover',
        };
    }
}
