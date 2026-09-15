<?php

namespace App\Enums;

enum RolPersonal: string
{
    case Administrador = 'administrador';
    case Abogado = 'abogado';
    case Asistente = 'asistente';
    case Secretaria = 'secretaria';
    case Contador = 'contador';
    case Psicologo = 'psicologo';
    case TrabajadorSocial = 'trabajador_social';

    public function label(): string
    {
        return match ($this) {
            self::Administrador => 'Administrador',
            self::Abogado => 'Abogado',
            self::Asistente => 'Asistente',
            self::Secretaria => 'Secretaria',
            self::Contador => 'Contador',
            self::Psicologo => 'Psicólogo',
            self::TrabajadorSocial => 'Trabajador Social',
        };
    }
}
