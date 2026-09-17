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
        Schema::create('recibos', function (Blueprint $table) {
            $table->id();
            // Nullable a nivel de BD: el número correlativo (REC-000001) se
            // asigna en un hook `created()` del modelo, una vez se conoce el
            // id real, para evitar colisiones.
            $table->string('numero')->nullable()->unique();
            $table->date('fecha');
            $table->decimal('monto', 10, 2);
            $table->string('concepto');
            $table->string('tipo')->default('otro');
            $table->foreignId('proceso_id')->nullable()->constrained('procesos')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('plan_pago_id')->nullable()->constrained('plan_pagos')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recibos');
    }
};
