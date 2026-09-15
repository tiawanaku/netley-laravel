<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finanza_id')->constrained('finanzas')->cascadeOnDelete();
            $table->date('fecha');
            $table->decimal('monto', 10, 2);
            $table->string('estado')->default('pendiente');
            $table->string('qr_path')->nullable();
            $table->string('comprobante')->nullable();
            $table->dateTime('pagado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_pagos');
    }
};
