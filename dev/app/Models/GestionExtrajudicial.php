<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GestionExtrajudicial extends Model
{
    use HasFactory;

    protected $table = 'gestion_extrajudiciales';

    protected $fillable = ['proceso_id', 'fecha', 'motivo', 'fecha_devolucion', 'personal_id'];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'fecha_devolucion' => 'date',
        ];
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class);
    }
}
