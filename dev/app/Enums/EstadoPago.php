<?php

namespace App\Enums;

enum EstadoPago: string
{
    case Pendiente = 'pendiente';
    case PendienteConfirmacion = 'pendiente_confirmacion';
    case Pagado = 'pagado';
    case Vencido = 'vencido';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::PendienteConfirmacion => 'Pendiente de confirmación',
            self::Pagado => 'Pagado',
            self::Vencido => 'Vencido',
        };
    }
}
