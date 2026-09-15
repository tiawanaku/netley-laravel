<?php

namespace App\Enums;

enum FormaIngreso: string
{
    case Llamada = 'llamada';
    case WhatsApp = 'whatsapp';
    case Presencial = 'presencial';
    case FormularioWeb = 'formulario_web';
    case Referido = 'referido';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Llamada => 'Llamada',
            self::WhatsApp => 'WhatsApp',
            self::Presencial => 'Presencial',
            self::FormularioWeb => 'Formulario web',
            self::Referido => 'Referido',
            self::Otro => 'Otro',
        };
    }
}
