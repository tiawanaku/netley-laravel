<?php

namespace App\Enums;

enum CategoriaDocumento: string
{
    case Identificacion = 'identificacion';
    case Contrato = 'contrato';
    case Poder = 'poder';
    case Memorial = 'memorial';
    case Resolucion = 'resolucion';
    case Demanda = 'demanda';
    case Fotografia = 'fotografia';
    case Prueba = 'prueba';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Identificacion => 'Identificación (CI, pasaporte, etc.)',
            self::Contrato => 'Contrato',
            self::Poder => 'Poder',
            self::Memorial => 'Memorial',
            self::Resolucion => 'Resolución',
            self::Demanda => 'Demanda',
            self::Fotografia => 'Fotografía',
            self::Prueba => 'Prueba',
            self::Otro => 'Otro',
        };
    }
}
