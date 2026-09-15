<?php

namespace App\Enums;

/**
 * Especialidades legales de un Personal con profesión "Abogado" — lista
 * exacta del formulario "Agregar Personal" del sistema legado
 * (requerimientos/formulario nuevo personal.txt). Un Personal puede tener
 * varias a la vez (ver Personal::especialidades, columna JSON).
 */
enum EspecialidadAbogado: string
{
    case Civil = 'CIVIL';
    case Impositivo = 'IMPOSITIVO';
    case Minero = 'MINERO';
    case Penal = 'PENAL';
    case Tributario = 'TRIBUTARIO';
    case Laboral = 'LABORAL';
    case SeguridadSocial = 'SEGURIDAD SOCIAL';
    case Familia = 'FAMILIA';
    case SinEspecialidad = 'SIN ESPECIALIDAD';

    public function label(): string
    {
        return $this->value;
    }
}
