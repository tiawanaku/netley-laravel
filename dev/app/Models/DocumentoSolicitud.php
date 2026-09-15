<?php

namespace App\Models;

use App\Enums\EstadoSolicitudDocumento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentoSolicitud extends Model
{
    use HasFactory;

    protected $table = 'documento_solicitudes';

    protected $fillable = ['proceso_id', 'personal_id', 'descripcion', 'estado'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoSolicitudDocumento::class,
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

    public function documentos(): HasMany
    {
        return $this->hasMany(ProcesoDocumento::class, 'solicitud_id');
    }

    public function marcarCumplida(): void
    {
        $this->estado = EstadoSolicitudDocumento::Cumplida;
        $this->save();
    }
}
