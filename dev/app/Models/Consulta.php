<?php

namespace App\Models;

use App\Enums\EstadoConsulta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consulta extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'apellido_paterno', 'apellido_materno', 'ci', 'telefono', 'whatsapp',
        'departamento_id', 'provincia', 'pais', 'email', 'materia_legal_id', 'descripcion',
        'nota_interna', 'origen_id', 'colegio_otros', 'origen_otro', 'estado',
        'pago_inicial_monto', 'pago_inicial_registrado_en', 'atendido_por',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoConsulta::class,
            'pago_inicial_monto' => 'decimal:2',
            'pago_inicial_registrado_en' => 'datetime',
        ];
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function materiaLegal(): BelongsTo
    {
        return $this->belongsTo(MateriaLegal::class);
    }

    public function origen(): BelongsTo
    {
        return $this->belongsTo(Origen::class);
    }

    public function atendioPor(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'atendido_por');
    }

    public function cliente(): HasOne
    {
        return $this->hasOne(Cliente::class);
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class);
    }

    /**
     * Respuestas dadas en las citas de esta consulta, con quién tomó la cita,
     * quién respondió y el tiempo transcurrido entre el registro de la
     * consulta y la respuesta.
     */
    public function respuestas()
    {
        return Answer::query()
            ->whereIn('agenda_id', $this->agendas()->pluck('id'))
            ->with(['agenda.responsable', 'personal', 'user', 'materiaLegal', 'delito'])
            ->orderBy('created_at')
            ->get()
            ->map(function (Answer $answer) {
                return [
                    'tomada_por' => $answer->agenda?->responsable,
                    'respondida_por' => $answer->personal ?? $answer->user,
                    'fecha' => $answer->created_at,
                    'contenido' => $answer->respuesta,
                    'categoria' => $answer->categoria,
                    'materia_legal' => $answer->materiaLegal,
                    'delito' => $answer->delito,
                    'publicar' => $answer->publicar,
                    'tiempo_transcurrido' => $this->created_at->diffForHumans($answer->created_at, true),
                ];
            });
    }

    /**
     * Última respuesta con categoría Legal registrada — usada para prellenar
     * materia legal / delito al convertir esta consulta a Cliente Ejecutivo
     * (equivalente a ascender_caso.php del sistema legado).
     */
    public function ultimaRespuestaLegal(): ?Answer
    {
        return Answer::query()
            ->whereIn('agenda_id', $this->agendas()->pluck('id'))
            ->where('categoria', 'Legal')
            ->latest()
            ->first();
    }
}
