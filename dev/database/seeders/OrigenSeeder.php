<?php

namespace Database\Seeders;

use App\Models\Origen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrigenSeeder extends Seeder
{
    /**
     * Lista exacta del select "Origen de la consulta" del formulario legado
     * (requerimientos/consultas.txt).
     */
    public function run(): void
    {
        $origenes = [
            'Cliente antiguo', 'Recomendación', 'Espontáneamente', 'Taller colegio',
            'Psicología', 'Página Netley', 'Social', 'Facebook', 'TikTok', 'Messenger',
            'Instagram', 'WhatsApp', 'Otro',
        ];

        foreach ($origenes as $orden => $nombre) {
            Origen::query()->updateOrCreate(
                ['slug' => Str::slug($nombre)],
                ['nombre' => $nombre, 'activo' => true, 'orden' => $orden],
            );
        }
    }
}
