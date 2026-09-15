<?php

namespace App\Models;

use App\Enums\EstadoCivil;
use App\Enums\EstadoPersonal;
use App\Enums\Expedido;
use App\Enums\Genero;
use App\Enums\RolPersonal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class Personal extends Authenticatable
{
    use HasFactory;

    protected $table = 'personal';

    protected $fillable = [
        'nombre', 'apellido_paterno', 'apellido_materno', 'ci', 'expedido', 'expedido_otro',
        'genero', 'fecha_nacimiento', 'nacionalidad', 'estado_civil', 'cargo', 'profesiones',
        'profesiones_otro', 'especialidades', 'telefono', 'whatsapp', 'email', 'direccion',
        'departamento_id', 'numero_contrato', 'estado', 'fecha_inicio', 'fecha_fin', 'rol',
        'foto', 'documentos', 'nota', 'usuario', 'password', 'must_change_password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'documentos' => 'array',
            'profesiones' => 'array',
            'especialidades' => 'array',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'estado' => EstadoPersonal::class,
            'rol' => RolPersonal::class,
            'expedido' => Expedido::class,
            'genero' => Genero::class,
            'estado_civil' => EstadoCivil::class,
        ];
    }

    /**
     * Alias de "nombre completo" — el paquete AdminLTE (menú de usuario del
     * navbar) asume $user->name en cualquier guard.
     */
    public function getNameAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    public function getApellidosAttribute(): string
    {
        return trim("{$this->apellido_paterno} {$this->apellido_materno}");
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function procesos(): HasMany
    {
        return $this->hasMany(Proceso::class, 'abogado_id');
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class, 'responsable_id');
    }

    public function agendasParticipa(): BelongsToMany
    {
        return $this->belongsToMany(Agenda::class, 'agenda_participantes')->withPivot('rol')->withTimestamps();
    }

    public function consultasAtendidas(): HasMany
    {
        return $this->hasMany(Consulta::class, 'atendido_por');
    }

    public function canAccessPanel(string $panel): bool
    {
        return $panel === 'personal' && $this->estado === EstadoPersonal::Habilitado;
    }

    public function esAbogado(): bool
    {
        return $this->rol === RolPersonal::Abogado;
    }

    public function esAdministrador(): bool
    {
        return $this->rol === RolPersonal::Administrador;
    }

    public function puedeVerCaso(Proceso $proceso): bool
    {
        if ($this->esAdministrador()) {
            return true;
        }

        return $this->esAbogado() && $proceso->abogado_id === $this->id;
    }

    /**
     * Compara sus especialidades legales (formato legado, p. ej. "CIVIL",
     * "SEGURIDAD SOCIAL") contra el nombre de una MateriaLegal del catálogo
     * (p. ej. "Civil"). Comparación best-effort: mayúsculas + sin acentos —
     * los vocabularios de ambos catálogos no son 100% coincidentes (ver
     * requerimientos/formulario nuevo personal.txt vs materias_legales).
     */
    public function tieneEspecialidadEnMateria(MateriaLegal $materia): bool
    {
        $normalizar = fn (string $valor) => Str::of($valor)->ascii()->upper()->toString();

        $objetivo = $normalizar($materia->nombre);

        return collect($this->especialidades ?? [])
            ->map(fn ($valor) => $normalizar($valor))
            ->contains($objetivo);
    }

    /**
     * Genera un usuario único a partir del teléfono, y una contraseña temporal
     * mostrada una sola vez al crear el registro.
     */
    public function generarAccesoPortal(): string
    {
        $base = preg_replace('/\D/', '', $this->telefono);
        $usuario = $base;
        $sufijo = 1;

        while (static::where('usuario', $usuario)->where('id', '!=', $this->id)->exists()) {
            $usuario = $base.$sufijo;
            $sufijo++;
        }

        $temporal = Str::password(10);

        $this->usuario = $usuario;
        $this->password = $temporal;
        $this->must_change_password = true;
        $this->save();

        return $temporal;
    }
}
