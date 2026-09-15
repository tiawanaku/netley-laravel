<?php

namespace App\Enums;

enum EstadoProceso: string
{
    case Activo = 'activo';
    case Cerrado = 'cerrado';
    case Archivado = 'archivado';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Cerrado => 'Cerrado',
            self::Archivado => 'Archivado',
        };
    }
}
