<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('categoria')->default('otro');
            $table->text('descripcion')->nullable();
            $table->decimal('monto', 10, 2);
            // Opcional: si el gasto es de un caso puntual (ej. fotocopias del
            // expediente X) queda ligado a ese proceso; si no, es un gasto
            // general del despacho.
            $table->foreignId('proceso_id')->nullable()->constrained('procesos')->nullOnDelete();
            $table->string('comprobante')->nullable();
            $table->string('comprobante_nombre_original')->nullable();
            $table->string('comprobante_mime_type')->nullable();
            $table->unsignedBigInteger('comprobante_tamano')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
