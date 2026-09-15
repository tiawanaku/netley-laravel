<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_legal_id')->constrained('materias_legales')->cascadeOnDelete();
            $table->string('delito', 255);
            $table->index(['materia_legal_id', 'delito']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delitos');
    }
};
