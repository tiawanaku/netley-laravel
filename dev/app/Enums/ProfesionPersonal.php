<?php

namespace App\Enums;

/**
 * Profesiones de un Personal (selección múltiple) — determina qué grupo(s)
 * de especialidades se habilitan en el formulario (Legal, Psicología,
 * Médicas, Trabajo Social). Lista exacta del formulario legado.
 */
enum ProfesionPersonal: string
{
    case Abogado = 'Abogado';
    case Psicologia = 'Psicologia';
    case TrabajoSocial = 'Trabajo Social';
    case Medico = 'Medico';
    case Administrativo = 'Administrativo';
    case Contador = 'Contador';
    case Secretaria = 'Secretaria';
    case Pasante = 'Pasante';
    case Limpieza = 'Limpieza';
    case Otros = 'Otros';

    public function label(): string
    {
        return match ($this) {
            self::Abogado => 'Abogada/Abogado',
            self::Psicologia => 'Psicóloga/Psicólogo',
            self::TrabajoSocial => 'Trabajador Social',
            self::Medico => 'Médico',
            self::Administrativo => 'Administrativa/Administrativo',
            self::Contador => 'Contadora/Contador',
            self::Secretaria => 'Secretaria',
            self::Pasante => 'Pasante',
            self::Limpieza => 'Limpieza',
            self::Otros => 'Otra profesión',
        };
    }
}
