<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaHistorial extends Model
{
    protected $table = 'agenda_historiales';

    protected $fillable = ['agenda_id', 'usuario_id', 'accion', 'datos_anteriores', 'datos_nuevos'];

    protected function casts(): array
    {
        return [
            'datos_anteriores' => 'array',
            'datos_nuevos' => 'array',
        ];
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }

    /**
     * Solo admins (User) auditan cambios de Agenda, nunca Personal — mismo
     * criterio que el sistema original.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
