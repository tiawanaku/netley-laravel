<?php

namespace App\Enums;

enum ModalidadAgenda: string
{
    case Presencial = 'presencial';
    case Virtual = 'virtual';

    public function label(): string
    {
        return match ($this) {
            self::Presencial => 'Presencial',
            self::Virtual => 'Virtual',
        };
    }
}
