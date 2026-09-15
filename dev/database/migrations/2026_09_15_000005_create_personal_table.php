<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->string('ci')->unique();
            $table->string('expedido')->nullable();
            $table->string('expedido_otro')->nullable();
            $table->string('genero');
            $table->date('fecha_nacimiento');
            $table->string('nacionalidad')->default('Boliviana');
            $table->string('estado_civil');
            $table->string('cargo')->nullable();
            $table->json('profesiones')->nullable();
            $table->string('profesiones_otro')->nullable();
            $table->json('especialidades')->nullable();
            $table->string('telefono');
            $table->string('whatsapp')->nullable();
            $table->string('email');
            $table->string('direccion')->nullable();
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->nullOnDelete();
            $table->string('numero_contrato')->nullable()->unique();
            $table->string('estado')->default('habilitado');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('rol');
            $table->string('foto')->nullable();
            $table->json('documentos')->nullable();
            $table->text('nota')->nullable();
            $table->string('usuario')->nullable()->unique();
            $table->string('password')->nullable();
            $table->boolean('must_change_password')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal');
    }
};
