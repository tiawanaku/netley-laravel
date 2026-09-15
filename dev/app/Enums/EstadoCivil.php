<?php

namespace App\Enums;

enum EstadoCivil: string
{
    case Soltero = 'soltero';
    case Casado = 'casado';
    case Divorciado = 'divorciado';
    case Viudo = 'viudo';
    case Concubinato = 'concubinato';

    public function label(): string
    {
        return match ($this) {
            self::Soltero => 'Soltero(a)',
            self::Casado => 'Casado(a)',
            self::Divorciado => 'Divorciado(a)',
            self::Viudo => 'Viudo(a)',
            self::Concubinato => 'Concubinato',
        };
    }
}
