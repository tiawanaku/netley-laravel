<?php

namespace App\Models;

use App\Enums\CategoriaDocumento;
use App\Enums\OrigenDocumento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProcesoDocumento extends Model
{
    use HasFactory;

    protected $table = 'proceso_documentos';

    protected $fillable = [
        'proceso_id', 'categoria', 'nombre', 'archivo', 'nombre_original', 'mime_type',
        'tamano', 'origen', 'personal_id', 'cliente_id', 'user_id', 'solicitud_id',
    ];

    protected function casts(): array
    {
        return [
            'categoria' => CategoriaDocumento::class,
            'origen' => OrigenDocumento::class,
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (ProcesoDocumento $documento) {
            Storage::disk('public')->delete($documento->archivo);
        });
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(DocumentoSolicitud::class, 'solicitud_id');
    }

    /**
     * Nombre de quien subió el documento (admin, staff o el propio cliente),
     * el único de los tres FKs de autoría que esté presente.
     */
    public function subidoPor(): ?string
    {
        return $this->user?->name
            ?? ($this->personal ? "{$this->personal->nombre} {$this->personal->apellidos}" : null)
            ?? ($this->cliente ? "{$this->cliente->nombre} {$this->cliente->apellidos}" : null);
    }

    public function tamanoLegible(): string
    {
        if (! $this->tamano) {
            return '—';
        }

        $unidades = ['B', 'KB', 'MB', 'GB'];
        $tamano = $this->tamano;
        $i = 0;

        while ($tamano >= 1024 && $i < count($unidades) - 1) {
            $tamano /= 1024;
            $i++;
        }

        return round($tamano, 1).' '.$unidades[$i];
    }
}
