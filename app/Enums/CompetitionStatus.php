<?php

namespace App\Enums;

enum CompetitionStatus: string
{
    case Draft = 'draft';
    case Registration = 'registration';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Registration => 'Pendaftaran',
            self::Ongoing => 'Berlangsung',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
