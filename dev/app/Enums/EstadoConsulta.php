<?php

namespace App\Enums;

enum EstadoConsulta: string
{
    case Nueva = 'nueva';
    case Agendada = 'agendada';
    case ReAgendada = 'reagendada';
    case ClienteEjecutivo = 'cliente_ejecutivo';
    case NoAsistio = 'no_asistio';
    case Descartada = 'descartada';

    public function label(): string
    {
        return match ($this) {
            self::Nueva => 'Nueva',
            self::Agendada => 'Agendada',
            self::ReAgendada => 'Re-agendada',
            self::ClienteEjecutivo => 'Cliente Ejecutivo',
            self::NoAsistio => 'No asistió',
            self::Descartada => 'Descartada',
        };
    }
}
