<?php

namespace App\Models;

use App\Enums\CategoriaGasto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Gasto extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha', 'categoria', 'descripcion', 'monto', 'proceso_id', 'comprobante',
        'comprobante_nombre_original', 'comprobante_mime_type', 'comprobante_tamano', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'monto' => 'decimal:2',
            'categoria' => CategoriaGasto::class,
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Gasto $gasto) {
            if ($gasto->comprobante) {
                Storage::disk('public')->delete($gasto->comprobante);
            }
        });
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tamanoLegible(): string
    {
        if (! $this->comprobante_tamano) {
            return '—';
        }

        $unidades = ['B', 'KB', 'MB', 'GB'];
        $tamano = $this->comprobante_tamano;
        $i = 0;

        while ($tamano >= 1024 && $i < count($unidades) - 1) {
            $tamano /= 1024;
            $i++;
        }

        return round($tamano, 1).' '.$unidades[$i];
    }
}
