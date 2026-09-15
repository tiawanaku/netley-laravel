<?php

namespace Database\Seeders;

use App\Models\Departamento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        $departamentos = [
            'La Paz', 'Cochabamba', 'Santa Cruz', 'Oruro', 'Potosí', 'Chuquisaca',
            'Tarija', 'Beni', 'Pando',
        ];

        foreach ($departamentos as $orden => $nombre) {
            Departamento::query()->updateOrCreate(
                ['slug' => Str::slug($nombre)],
                ['nombre' => $nombre, 'activo' => true, 'orden' => $orden],
            );
        }
    }
}
