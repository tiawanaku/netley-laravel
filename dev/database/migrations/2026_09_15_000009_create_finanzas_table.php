<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finanzas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->unique()->constrained('procesos')->restrictOnDelete();
            $table->decimal('costo', 10, 2);
            $table->string('tipo_pago');
            $table->decimal('anticipo', 10, 2)->default(0);
            $table->dateTime('anticipo_registrado_en')->nullable();
            $table->dateTime('anticipo_confirmado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finanzas');
    }
};
