<?php

namespace App\Enums;

enum EspecialidadMedica: string
{
    case Cardiologia = 'CARDIOLOGIA';
    case Neumologia = 'NEUMOLOGIA';
    case Neurologia = 'NEUROLOGIA';
    case Hematologia = 'HEMATOLOGIA';
    case Infectologia = 'INFECTOLOGIA';
    case Endocrinologia = 'ENDOCRINOLOGIA';
    case Reumatologia = 'REUMATOLOGIA';
    case Gastroenterologia = 'GASTROENTEROLOGIA';
    case CirugiaGeneral = 'CIRUGIA GENERAL';
    case Neurocirugia = 'NEUROCIRUGIA';
    case MedicoGeneral = 'MEDICO GENERAL';

    public function label(): string
    {
        return ucwords(strtolower($this->value));
    }
}
