<?php

namespace App\Enums;

enum TipoAgenda: string
{
    case Cita = 'cita';
    case Llamada = 'llamada';
    case Reunion = 'reunion';

    public function label(): string
    {
        return match ($this) {
            self::Cita => 'Cita',
            self::Llamada => 'Llamada',
            self::Reunion => 'Reunión',
        };
    }
}
