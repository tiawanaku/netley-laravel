<?php

namespace App\Models;

use App\Enums\TipoPago;
use App\Enums\TipoRecibo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Finanza extends Model
{
    use HasFactory;

    protected $fillable = [
        'proceso_id', 'costo', 'tipo_pago', 'anticipo', 'anticipo_registrado_en',
        'anticipo_confirmado_en',
    ];

    protected function casts(): array
    {
        return [
            'costo' => 'decimal:2',
            'anticipo' => 'decimal:2',
            'tipo_pago' => TipoPago::class,
            'anticipo_registrado_en' => 'datetime',
            'anticipo_confirmado_en' => 'datetime',
        ];
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(PlanPago::class);
    }

    /**
     * Genera las cuotas del plan de pagos, respetando un máximo calculado
     * según el tiempo estimado del proceso. Bloquea la regeneración si ya
     * hay cuotas pagadas.
     */
    public function generarPlanPagos(int $cuotas, \DateTimeInterface $fechaInicio): void
    {
        if ($this->cuotas()->where('estado', '!=', 'pendiente')->exists()) {
            throw ValidationException::withMessages([
                'cuotas' => 'No se puede regenerar el plan de pagos: ya existen cuotas pagadas o en confirmación.',
            ]);
        }

        $maximo = max(1, $this->proceso->tiempo_proceso_meses);
        $cuotas = min($cuotas, $maximo);

        $this->cuotas()->delete();

        $saldo = $this->costo - $this->anticipo;
        $montoPorCuota = round($saldo / $cuotas, 2);
        $fecha = \Carbon\Carbon::instance(\Carbon\Carbon::parse($fechaInicio));

        for ($i = 0; $i < $cuotas; $i++) {
            $this->cuotas()->create([
                'fecha' => $fecha->copy(),
                'monto' => $i === $cuotas - 1
                    ? $saldo - ($montoPorCuota * ($cuotas - 1))
                    : $montoPorCuota,
                'estado' => 'pendiente',
            ]);

            $fecha = match ($this->tipo_pago) {
                TipoPago::Semanal => $fecha->addWeek(),
                default => $fecha->addMonth(),
            };
        }
    }

    public function recibos(): HasMany
    {
        return $this->hasMany(Recibo::class, 'proceso_id', 'proceso_id');
    }

    /**
     * Confirma el anticipo y emite el recibo correspondiente — la
     * contraparte de PlanPago::confirmarPago() para el pago inicial.
     */
    public function confirmarAnticipo(): void
    {
        $this->anticipo_confirmado_en = now();
        $this->save();

        Recibo::create([
            'fecha' => now()->toDateString(),
            'monto' => $this->anticipo,
            'concepto' => 'Anticipo — '.$this->proceso->tipo_proceso,
            'tipo' => TipoRecibo::Anticipo,
            'proceso_id' => $this->proceso_id,
            'cliente_id' => $this->proceso->cliente_id,
            'user_id' => auth('web')->id(),
        ]);
    }
}
