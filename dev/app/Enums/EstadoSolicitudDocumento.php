<?php

namespace App\Enums;

enum EstadoSolicitudDocumento: string
{
    case Pendiente = 'pendiente';
    case Cumplida = 'cumplida';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Cumplida => 'Cumplida',
        };
    }
}
