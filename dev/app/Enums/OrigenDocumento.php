<?php

namespace App\Enums;

enum OrigenDocumento: string
{
    case Staff = 'staff';
    case Cliente = 'cliente';

    public function label(): string
    {
        return match ($this) {
            self::Staff => 'Staff',
            self::Cliente => 'Cliente',
        };
    }
}
