<?php

namespace App\Models;

use App\Enums\EstadoProceso;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Proceso extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id', 'materia_legal_id', 'tipo_proceso', 'tiempo_proceso_meses',
        'estado', 'abogado_id',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoProceso::class,
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function materiaLegal(): BelongsTo
    {
        return $this->belongsTo(MateriaLegal::class);
    }

    public function abogado(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'abogado_id');
    }

    public function finanza(): HasOne
    {
        return $this->hasOne(Finanza::class);
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(ProcesoDocumento::class);
    }

    public function solicitudesDocumento(): HasMany
    {
        return $this->hasMany(DocumentoSolicitud::class);
    }

    public function etapas(): HasMany
    {
        return $this->hasMany(ProcesoEtapa::class);
    }

    public function recibos(): HasMany
    {
        return $this->hasMany(Recibo::class);
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class);
    }

    public function ficha(): HasOne
    {
        return $this->hasOne(ProcesoFicha::class);
    }

    public function gestionesExtrajudiciales(): HasMany
    {
        return $this->hasMany(GestionExtrajudicial::class);
    }

    /**
     * Última etapa que el abogado registró para el caso. Usa la colección ya
     * cargada cuando `etapas` viene eager-loaded (evita N+1 en listados).
     */
    public function etapaActual(): ?ProcesoEtapa
    {
        if ($this->relationLoaded('etapas')) {
            return $this->etapas->sortByDesc('created_at')->first();
        }

        return $this->etapas()->latest()->first();
    }

    /**
     * Línea de tiempo unificada del caso: consulta de origen, conversión a
     * cliente, agendas y documentos, ordenados cronológicamente.
     */
    public function timeline(): array
    {
        $eventos = collect();

        $consulta = $this->cliente?->consulta;

        if ($consulta) {
            $eventos->push([
                'fecha' => $consulta->created_at,
                'tipo' => 'consulta',
                'descripcion' => 'Consulta registrada',
            ]);
        }

        if ($this->cliente) {
            $eventos->push([
                'fecha' => $this->cliente->created_at,
                'tipo' => 'cliente',
                'descripcion' => 'Convertido a Cliente Ejecutivo',
            ]);
        }

        $eventos->push([
            'fecha' => $this->created_at,
            'tipo' => 'proceso',
            'descripcion' => 'Caso abierto',
        ]);

        foreach ($this->agendas as $agenda) {
            $eventos->push([
                'fecha' => $agenda->fecha_inicio,
                'tipo' => 'agenda',
                'descripcion' => $agenda->asunto ?? $agenda->tipo->label(),
            ]);
        }

        foreach ($this->documentos as $documento) {
            $eventos->push([
                'fecha' => $documento->created_at,
                'tipo' => 'documento',
                'descripcion' => 'Documento subido: '.$documento->nombre,
            ]);
        }

        foreach ($this->etapas as $etapa) {
            $eventos->push([
                'fecha' => $etapa->created_at,
                'tipo' => 'etapa',
                'descripcion' => 'Etapa actualizada: '.$etapa->etapa,
            ]);
        }

        foreach ($this->gastos as $gasto) {
            $eventos->push([
                'fecha' => $gasto->created_at,
                'tipo' => 'gasto',
                'descripcion' => 'Gasto registrado: '.$gasto->categoria->label().' — Bs. '.number_format((float) $gasto->monto, 2),
            ]);
        }

        return $eventos->sortBy('fecha')->values()->all();
    }
}
