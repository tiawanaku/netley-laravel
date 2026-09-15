<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_id')->constrained('agendas')->cascadeOnDelete();
            $table->text('respuesta');
            $table->string('categoria')->nullable();
            $table->foreignId('materia_legal_id')->nullable()->constrained('materias_legales')->nullOnDelete();
            $table->foreignId('delito_id')->nullable()->constrained('delitos')->nullOnDelete();
            $table->boolean('publicar')->default(false);
            $table->foreignId('personal_id')->nullable()->constrained('personal')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
