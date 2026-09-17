<?php

namespace App\Enums;

enum CategoriaGasto: string
{
    case Fotocopias = 'fotocopias';
    case TasasJudiciales = 'tasas_judiciales';
    case Transporte = 'transporte';
    case Papeleria = 'papeleria';
    case Notificaciones = 'notificaciones';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Fotocopias => 'Fotocopias',
            self::TasasJudiciales => 'Tasas judiciales',
            self::Transporte => 'Transporte',
            self::Papeleria => 'Papelería / útiles de oficina',
            self::Notificaciones => 'Notificaciones / correspondencia',
            self::Otro => 'Otro',
        };
    }
}
