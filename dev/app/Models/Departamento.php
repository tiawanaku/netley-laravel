<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    protected $table = 'departamentos';

    protected $fillable = ['nombre', 'slug', 'activo', 'orden'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class);
    }

    public function personal(): HasMany
    {
        return $this->hasMany(Personal::class);
    }
}
