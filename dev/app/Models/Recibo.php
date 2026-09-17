<?php

namespace App\Models;

use App\Enums\TipoRecibo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recibo extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero', 'fecha', 'monto', 'concepto', 'tipo', 'proceso_id', 'cliente_id',
        'plan_pago_id', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'monto' => 'decimal:2',
            'tipo' => TipoRecibo::class,
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Recibo $recibo) {
            if (! $recibo->numero) {
                $recibo->numero = 'REC-'.str_pad((string) $recibo->id, 6, '0', STR_PAD_LEFT);
                $recibo->saveQuietly();
            }
        });
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function planPago(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
