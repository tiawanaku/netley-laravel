<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Origen extends Model
{
    protected $table = 'origenes';

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
}
