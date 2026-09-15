<?php

namespace App\Enums;

enum Expedido: string
{
    case LP = 'LP';
    case CB = 'CB';
    case SC = 'SC';
    case OR = 'OR';
    case PT = 'PT';
    case CH = 'CH';
    case TJ = 'TJ';
    case BN = 'BN';
    case PD = 'PD';
    case QR = 'QR';
    case Otro = 'OTRO';

    public function label(): string
    {
        return match ($this) {
            self::LP => 'LP - La Paz',
            self::CB => 'CB - Cochabamba',
            self::SC => 'SC - Santa Cruz',
            self::OR => 'OR - Oruro',
            self::PT => 'PT - Potosí',
            self::CH => 'CH - Chuquisaca',
            self::TJ => 'TJ - Tarija',
            self::BN => 'BN - Beni',
            self::PD => 'PD - Pando',
            self::QR => 'QR',
            self::Otro => 'Otro',
        };
    }
}
