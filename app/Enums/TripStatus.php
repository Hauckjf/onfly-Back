<?php

namespace App\Enums;

enum TripStatus: string
{
    case PENDING = 'solicitado';
    case CONFIRMED = 'confirmado';
    case CANCELLED = 'cancelado';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'solicitado',
            self::CONFIRMED => 'confirmado',
            self::CANCELLED => 'cancelado'
        };
    }
}
