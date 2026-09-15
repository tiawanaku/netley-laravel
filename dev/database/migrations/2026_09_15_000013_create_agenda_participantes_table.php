<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_id')->constrained('agendas')->cascadeOnDelete();
            $table->foreignId('personal_id')->constrained('personal')->cascadeOnDelete();
            $table->string('rol')->nullable();
            $table->timestamps();

            $table->unique(['agenda_id', 'personal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_participantes');
    }
};
