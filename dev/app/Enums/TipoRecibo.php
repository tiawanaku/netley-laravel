<?php

namespace App\Enums;

enum TipoRecibo: string
{
    case CuotaPago = 'cuota_pago';
    case Anticipo = 'anticipo';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::CuotaPago => 'Pago de cuota',
            self::Anticipo => 'Anticipo',
            self::Otro => 'Otro',
        };
    }
}
