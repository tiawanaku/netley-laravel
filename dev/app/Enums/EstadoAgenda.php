<?php

namespace App\Enums;

enum EstadoAgenda: string
{
    case Pendiente = 'pendiente';
    case Confirmada = 'confirmada';
    case Reagendada = 'reagendada';
    case Cancelada = 'cancelada';
    case Finalizada = 'finalizada';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Confirmada => 'Confirmada',
            self::Reagendada => 'Reagendada',
            self::Cancelada => 'Cancelada',
            self::Finalizada => 'Finalizada',
        };
    }
}
