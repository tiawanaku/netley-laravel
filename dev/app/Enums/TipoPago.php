<?php

namespace App\Enums;

enum TipoPago: string
{
    case Semanal = 'semanal';
    case Mensual = 'mensual';
    case AlContado = 'al_contado';

    public function label(): string
    {
        return match ($this) {
            self::Semanal => 'Semanal',
            self::Mensual => 'Mensual',
            self::AlContado => 'Al contado',
        };
    }
}
