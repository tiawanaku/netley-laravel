<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MateriaLegalSeeder::class,
            OrigenSeeder::class,
            DepartamentoSeeder::class,
            PersonalSeeder::class,
        ]);

        User::query()->updateOrCreate(
            ['email' => 'admin@netley.test'],
            ['name' => 'Admin Netley', 'password' => 'password'],
        );
    }
}
