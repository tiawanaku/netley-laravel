<?php

namespace App\Enums;

enum EspecialidadPsicologia: string
{
    case Clinica = 'CLINICA';
    case Forense = 'FORENSE';
    case Educativa = 'EDUCATIVA';
    case Social = 'SOCIAL';
    case Familiar = 'FAMILIAR';

    public function label(): string
    {
        return match ($this) {
            self::Clinica => 'Psicología Clínica',
            self::Forense => 'Psicología Forense',
            self::Educativa => 'Psicología Educativa',
            self::Social => 'Psicología Social',
            self::Familiar => 'Psicología Familiar',
        };
    }
}
