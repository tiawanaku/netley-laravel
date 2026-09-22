<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gestiones extrajudiciales de un caso (notificaciones, requerimientos,
     * etc.): registro repetible con fecha, motivo y fecha de devolución,
     * tal como se llevaba en la ficha de seguimiento en papel.
     */
    public function up(): void
    {
        Schema::create('gestion_extrajudiciales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->date('fecha')->nullable();
            $table->string('motivo')->nullable();
            $table->date('fecha_devolucion')->nullable();
            $table->foreignId('personal_id')->constrained('personal')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gestion_extrajudiciales');
    }
};
