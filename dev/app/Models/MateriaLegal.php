<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MateriaLegal extends Model
{
    protected $table = 'materias_legales';

    protected $fillable = ['nombre', 'slug', 'activo', 'orden'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function delitos(): HasMany
    {
        return $this->hasMany(Delito::class);
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class);
    }

    public function procesos(): HasMany
    {
        return $this->hasMany(Proceso::class);
    }

    public static function activas()
    {
        return static::query()->where('activo', true)->orderBy('orden')->get();
    }
}
