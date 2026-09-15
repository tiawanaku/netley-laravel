<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class Cliente extends Authenticatable
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'consulta_id', 'nombre', 'apellidos', 'ci', 'telefono', 'whatsapp',
        'usuario', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Alias de "nombre completo" — el paquete AdminLTE (menú de usuario del
     * navbar) asume $user->name en cualquier guard.
     */
    public function getNameAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellidos}");
    }

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }

    public function procesos(): HasMany
    {
        return $this->hasMany(Proceso::class);
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class);
    }

    public function canAccessPanel(string $panel): bool
    {
        return $panel === 'cliente';
    }

    /**
     * Convierte una Consulta ya existente en un Cliente Ejecutivo: copia los
     * datos de contacto y genera usuario/contraseña temporal (mostrada una
     * sola vez por quien la crea).
     */
    public static function convertirDesdeConsulta(Consulta $consulta): array
    {
        $temporal = Str::password(10);

        $cliente = static::create([
            'consulta_id' => $consulta->id,
            'nombre' => $consulta->nombre,
            'apellidos' => trim($consulta->apellido_paterno.' '.$consulta->apellido_materno),
            'ci' => $consulta->ci,
            'telefono' => $consulta->telefono,
            'whatsapp' => $consulta->whatsapp,
            'usuario' => static::generarUsuarioDesde($consulta->telefono),
            'password' => $temporal,
        ]);

        return [$cliente, $temporal];
    }

    /**
     * Alta directa de un Cliente Ejecutivo sin Consulta previa (wizard del
     * panel Admin). Genera usuario/contraseña igual que la conversión desde
     * Consulta.
     */
    public static function crearDirecto(array $datos): array
    {
        $temporal = Str::password(10);

        $cliente = static::create([
            'consulta_id' => null,
            'nombre' => $datos['nombre'],
            'apellidos' => $datos['apellidos'],
            'ci' => $datos['ci'] ?? null,
            'telefono' => $datos['telefono'],
            'whatsapp' => $datos['whatsapp'] ?? null,
            'usuario' => static::generarUsuarioDesde($datos['telefono']),
            'password' => $temporal,
        ]);

        return [$cliente, $temporal];
    }

    protected static function generarUsuarioDesde(string $telefono): string
    {
        $base = preg_replace('/\D/', '', $telefono);
        $usuario = $base;
        $sufijo = 1;

        while (static::where('usuario', $usuario)->exists()) {
            $usuario = $base.$sufijo;
            $sufijo++;
        }

        return $usuario;
    }
}
