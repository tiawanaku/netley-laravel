<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proceso_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->string('categoria')->default('otro');
            $table->string('nombre');
            $table->string('archivo');
            $table->string('origen')->default('staff');
            $table->foreignId('personal_id')->nullable()->constrained('personal')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('solicitud_id')->nullable()->constrained('documento_solicitudes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_documentos');
    }
};
