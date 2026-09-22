<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Ficha de seguimiento del caso" — datos adicionales que el abogado
     * encargado va completando a lo largo del proceso (número de
     * caso/expediente, partes, fecha de cierre, resultado). Es 1:1 con
     * Proceso; los datos financieros (iguala, anticipo, cobros) ya viven en
     * Finanza/Recibo y no se duplican aquí.
     */
    public function up(): void
    {
        Schema::create('proceso_fichas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->unique()->constrained('procesos')->cascadeOnDelete();
            $table->string('numero_caso')->nullable();
            $table->string('denunciante')->nullable();
            $table->string('denunciado')->nullable();
            $table->date('fecha_inicio_caso')->nullable();
            $table->date('fecha_finalizacion')->nullable();
            $table->text('resultado')->nullable();
            $table->foreignId('actualizado_por')->nullable()->constrained('personal')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_fichas');
    }
};
