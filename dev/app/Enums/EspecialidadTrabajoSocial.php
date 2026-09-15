<?php

namespace App\Enums;

enum EspecialidadTrabajoSocial: string
{
    case Familiar = 'TRABAJO SOCIAL FAMILIAR';
    case Salud = 'TRABAJO SOCIAL SALUD';
    case Educativo = 'TRABAJO SOCIAL EDUCATIVO';
    case Comunitario = 'TRABAJO SOCIAL COMUNITARIO';
    case Juridico = 'TRABAJO SOCIAL JURIDICO';

    public function label(): string
    {
        return ucwords(strtolower($this->value));
    }
}
