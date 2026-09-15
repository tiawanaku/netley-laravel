<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('materia_legal_id')->nullable()->constrained('materias_legales')->nullOnDelete();
            $table->string('tipo_proceso');
            $table->unsignedSmallInteger('tiempo_proceso_meses');
            $table->string('estado')->default('activo');
            $table->foreignId('abogado_id')->nullable()->references('id')->on('personal')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procesos');
    }
};
