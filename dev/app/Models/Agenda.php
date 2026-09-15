<?php

namespace App\Models;

use App\Enums\EstadoAgenda;
use App\Enums\ModalidadAgenda;
use App\Enums\ResultadoLlamada;
use App\Enums\TipoAgenda;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agenda extends Model
{
    use HasFactory;

    public const UBICACIONES = [
        'Oficina Central',
        'Virtual',
        'Domicilio del cliente',
        'Juzgado',
        'Otro',
    ];

    protected $fillable = [
        'tipo', 'estado', 'fecha_inicio', 'fecha_fin', 'asunto', 'descripcion',
        'modalidad', 'ubicacion', 'duracion_minutos', 'resultado', 'proceso_id',
        'consulta_id', 'cliente_id', 'responsable_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoAgenda::class,
            'estado' => EstadoAgenda::class,
            'modalidad' => ModalidadAgenda::class,
            'resultado' => ResultadoLlamada::class,
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Agenda $agenda) {
            $agenda->historiales()->create([
                'usuario_id' => auth('web')->id(),
                'accion' => 'creado',
                'datos_nuevos' => $agenda->getAttributes(),
            ]);
        });

        static::updated(function (Agenda $agenda) {
            $agenda->historiales()->create([
                'usuario_id' => auth('web')->id(),
                'accion' => 'actualizado',
                'datos_anteriores' => $agenda->getOriginal(),
                'datos_nuevos' => $agenda->getAttributes(),
            ]);
        });
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'responsable_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participantes(): BelongsToMany
    {
        return $this->belongsToMany(Personal::class, 'agenda_participantes')->withPivot('rol')->withTimestamps();
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function historiales(): HasMany
    {
        return $this->hasMany(AgendaHistorial::class);
    }

    public function scopeDeHoy($query)
    {
        return $query->whereDate('fecha_inicio', now()->toDateString());
    }

    public function scopeAtrasadas($query)
    {
        return $query->where('fecha_inicio', '<', now())
            ->whereNotIn('estado', [EstadoAgenda::Finalizada->value, EstadoAgenda::Cancelada->value]);
    }

    public function scopeFuturas($query)
    {
        return $query->where('fecha_inicio', '>', now());
    }

    public function scopeCerradas($query)
    {
        return $query->whereIn('estado', [EstadoAgenda::Finalizada->value, EstadoAgenda::Cancelada->value]);
    }

    public static function hayConflicto(int $responsableId, \DateTimeInterface $inicio, \DateTimeInterface $fin, ?int $ignorarId = null): bool
    {
        return static::query()
            ->where('responsable_id', $responsableId)
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
            ->whereNotIn('estado', [EstadoAgenda::Cancelada->value])
            ->where('fecha_inicio', '<', $fin)
            ->where('fecha_fin', '>', $inicio)
            ->exists();
    }

    /**
     * Regla de horario laboral usada solo en el panel de Personal: lunes a
     * viernes, 08:00-17:00.
     */
    public static function reglaHorarioLaboral(\DateTimeInterface $fecha): bool
    {
        $carbon = \Carbon\Carbon::instance(\Carbon\Carbon::parse($fecha));

        return $carbon->isWeekday() && $carbon->hour >= 8 && $carbon->hour < 17;
    }
}
