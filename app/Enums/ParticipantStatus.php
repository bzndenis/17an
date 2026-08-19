<?php

namespace App\Enums;

enum ParticipantStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Eliminated = 'eliminated';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Inactive => 'Tidak Aktif',
            self::Eliminated => 'Tersingkir',
            self::Withdrawn => 'Mengundurkan Diri',
        };
    }
}
