<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->string('ci')->nullable();
            $table->string('telefono');
            $table->string('whatsapp')->nullable();
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->nullOnDelete();
            $table->string('provincia')->nullable();
            $table->string('pais')->default('Bolivia');
            $table->string('email')->nullable();
            $table->foreignId('materia_legal_id')->nullable()->constrained('materias_legales')->nullOnDelete();
            $table->text('descripcion')->nullable();
            $table->text('nota_interna')->nullable();
            $table->foreignId('origen_id')->nullable()->constrained('origenes')->nullOnDelete();
            $table->string('colegio_otros')->nullable();
            $table->string('origen_otro')->nullable();
            $table->string('estado')->default('nueva');
            $table->decimal('pago_inicial_monto', 10, 2)->nullable();
            $table->dateTime('pago_inicial_registrado_en')->nullable();
            $table->foreignId('atendido_por')->nullable()->references('id')->on('personal')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};
