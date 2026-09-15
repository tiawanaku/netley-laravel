<?php

namespace App\Enums;

enum EstadoPersonal: string
{
    case Habilitado = 'habilitado';
    case Deshabilitado = 'deshabilitado';

    public function label(): string
    {
        return match ($this) {
            self::Habilitado => 'Habilitado',
            self::Deshabilitado => 'Deshabilitado',
        };
    }
}
