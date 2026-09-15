<?php

namespace Database\Seeders;

use App\Models\MateriaLegal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MateriaLegalSeeder extends Seeder
{
    public function run(): void
    {
        $materias = [
            'Civil', 'Penal', 'Familiar', 'Laboral', 'Comercial', 'Administrativo',
            'Tributario', 'Constitucional', 'Ambiental', 'Minero', 'Migratorio',
            'Propiedad Intelectual',
        ];

        foreach ($materias as $orden => $nombre) {
            MateriaLegal::query()->updateOrCreate(
                ['slug' => Str::slug($nombre)],
                ['nombre' => $nombre, 'activo' => true, 'orden' => $orden],
            );
        }
    }
}
