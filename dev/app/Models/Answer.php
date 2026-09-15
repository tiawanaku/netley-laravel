<?php

namespace App\Models;

use App\Enums\CategoriaRespuesta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'agenda_id', 'respuesta', 'categoria', 'materia_legal_id', 'delito_id', 'publicar',
        'personal_id', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'categoria' => CategoriaRespuesta::class,
            'publicar' => 'boolean',
        ];
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function materiaLegal(): BelongsTo
    {
        return $this->belongsTo(MateriaLegal::class);
    }

    public function delito(): BelongsTo
    {
        return $this->belongsTo(Delito::class);
    }
}
