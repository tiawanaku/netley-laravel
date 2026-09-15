<?php

namespace App\Enums;

/**
 * Cargo (puesto) de un Personal dentro del despacho — lista exacta del
 * formulario legado ("Agregar Personal"). Es descriptivo/organizacional,
 * distinto de Personal::rol (que rige accesos del panel Personal).
 */
enum Cargo: string
{
    case Directora = 'Directora';
    case Gerente = 'Gerente';
    case Coordinadora = 'Coordinadora';
    case RepresentanteLegal = 'Representante legal';
    case Administrativo = 'Administrativo';
    case Abogado = 'Abogado';
    case Psicologo = 'Psicologo';
    case TrabajadorSocial = 'Trabajador Social';
    case Medico = 'Medico';
    case Contador = 'Contador';
    case Secretaria = 'Secretaria';
    case Limpieza = 'Limpieza';
    case Pasante = 'Pasante';

    public function label(): string
    {
        return $this->value;
    }
}
