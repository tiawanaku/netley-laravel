<?php

namespace App\Enums;

enum CategoriaRespuesta: string
{
    case Legal = 'Legal';
    case Psicologica = 'Psicologica';
    case TrabajoSocial = 'Trabajo Social';
    case Informatica = 'Informatica';
    case Otro = 'Otro';

    public function label(): string
    {
        return match ($this) {
            self::Legal => 'Legal',
            self::Psicologica => 'Psicológica',
            self::TrabajoSocial => 'Trabajo Social',
            self::Informatica => 'Informática',
            self::Otro => 'Otro',
        };
    }
}
