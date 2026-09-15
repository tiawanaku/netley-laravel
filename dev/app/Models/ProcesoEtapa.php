<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesoEtapa extends Model
{
    use HasFactory;

    protected $table = 'proceso_etapas';

    protected $fillable = ['proceso_id', 'etapa', 'comentario', 'personal_id'];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class);
    }
}
