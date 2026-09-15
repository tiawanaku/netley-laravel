<?php

namespace App\Enums;

enum ResultadoLlamada: string
{
    case Contactado = 'contactado';
    case NoContesta = 'no_contesta';
    case Ocupado = 'ocupado';
    case NumeroErroneo = 'numero_erroneo';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Contactado => 'Contactado',
            self::NoContesta => 'No contesta',
            self::Ocupado => 'Ocupado',
            self::NumeroErroneo => 'Número erróneo',
            self::Otro => 'Otro',
        };
    }
}
