<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesoFicha extends Model
{
    use HasFactory;

    protected $table = 'proceso_fichas';

    protected $fillable = [
        'proceso_id', 'numero_caso', 'denunciante', 'denunciado',
        'fecha_inicio_caso', 'fecha_finalizacion', 'resultado', 'actualizado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio_caso' => 'date',
            'fecha_finalizacion' => 'date',
        ];
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'actualizado_por');
    }
}
