<?php

namespace App\Models;

use App\Enums\EstadoPago;
use Endroid\QrCode\Builder\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PlanPago extends Model
{
    use HasFactory;

    protected $table = 'plan_pagos';

    protected $fillable = [
        'finanza_id', 'fecha', 'monto', 'estado', 'qr_path', 'comprobante', 'pagado_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'monto' => 'decimal:2',
            'estado' => EstadoPago::class,
            'pagado_en' => 'datetime',
        ];
    }

    public function finanza(): BelongsTo
    {
        return $this->belongsTo(Finanza::class);
    }

    public function scopeEnMora($query)
    {
        return $query->where('estado', EstadoPago::Pendiente->value)
            ->whereDate('fecha', '<', now()->toDateString());
    }

    public function scopePorVencer($query, int $dias = 7)
    {
        return $query->where('estado', EstadoPago::Pendiente->value)
            ->whereBetween('fecha', [now()->toDateString(), now()->addDays($dias)->toDateString()]);
    }

    /**
     * Genera (o reutiliza) un QR de referencia de pago con los datos de la
     * cuota, para que el cliente lo escanee al pagar. No tiene relación con
     * verificación/autenticidad — es solo una referencia de cobro.
     */
    public function generarQr(): string
    {
        if ($this->qr_path && Storage::disk('public')->exists($this->qr_path)) {
            return $this->qr_path;
        }

        $contenido = sprintf(
            'Netley | Cuota #%d | Bs. %s | Vence: %s',
            $this->id,
            number_format((float) $this->monto, 2),
            $this->fecha->format('d/m/Y'),
        );

        $result = Builder::create()->data($contenido)->size(300)->margin(10)->build();

        $path = 'plan_pagos/qr/'.$this->id.'.png';
        Storage::disk('public')->put($path, $result->getString());

        $this->qr_path = $path;
        $this->save();

        return $path;
    }

    public function registrarPagoCliente(string $comprobante): void
    {
        $this->comprobante = $comprobante;
        $this->estado = EstadoPago::PendienteConfirmacion;
        $this->save();
    }

    public function confirmarPago(): void
    {
        $this->estado = EstadoPago::Pagado;
        $this->pagado_en = now();
        $this->save();
    }
}
