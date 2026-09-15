<?php

namespace Database\Seeders;

use App\Enums\Cargo;
use App\Enums\EspecialidadAbogado;
use App\Enums\EstadoCivil;
use App\Enums\EstadoPersonal;
use App\Enums\Expedido;
use App\Enums\Genero;
use App\Enums\ProfesionPersonal;
use App\Enums\RolPersonal;
use App\Models\Personal;
use Illuminate\Database\Seeder;

class PersonalSeeder extends Seeder
{
    public function run(): void
    {
        Personal::query()->updateOrCreate(
            ['ci' => '0000000'],
            [
                'nombre' => 'Abogado',
                'apellido_paterno' => 'De',
                'apellido_materno' => 'Prueba',
                'expedido' => Expedido::LP,
                'genero' => Genero::Masculino,
                'fecha_nacimiento' => '1990-01-01',
                'estado_civil' => EstadoCivil::Soltero,
                'cargo' => Cargo::RepresentanteLegal,
                'profesiones' => [ProfesionPersonal::Abogado->value],
                'especialidades' => [EspecialidadAbogado::Civil->value],
                'telefono' => '70000000',
                'email' => 'staff@netley.test',
                'estado' => EstadoPersonal::Habilitado,
                'fecha_inicio' => now(),
                'rol' => RolPersonal::Administrador,
                'usuario' => '70000000',
                'password' => 'password',
                'must_change_password' => false,
            ],
        );
    }
}
