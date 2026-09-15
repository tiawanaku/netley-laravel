<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delito extends Model
{
    public $timestamps = false;

    protected $fillable = ['materia_legal_id', 'delito'];

    public function materiaLegal(): BelongsTo
    {
        return $this->belongsTo(MateriaLegal::class);
    }

    public static function opcionesPara(?int $materiaLegalId): array
    {
        if (! $materiaLegalId) {
            return [];
        }

        return static::query()
            ->where('materia_legal_id', $materiaLegalId)
            ->orderBy('delito')
            ->pluck('delito', 'id')
            ->all();
    }
}
