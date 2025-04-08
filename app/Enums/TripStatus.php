<?php

namespace App\Enums;

enum TripStatus: string
{
    case PENDING = 'solicitado';
    case CONFIRMED = 'aprovado';
    case CANCELLED = 'cancelado';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'solicitado',
            self::CONFIRMED => 'aprovado',
            self::CANCELLED => 'cancelado'
        };
    }
}
