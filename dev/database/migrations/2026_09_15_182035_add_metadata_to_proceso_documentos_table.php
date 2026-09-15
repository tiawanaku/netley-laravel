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
        Schema::table('proceso_documentos', function (Blueprint $table) {
            $table->string('nombre_original')->nullable()->after('archivo');
            $table->string('mime_type')->nullable()->after('nombre_original');
            $table->unsignedBigInteger('tamano')->nullable()->after('mime_type');
            $table->foreignId('user_id')->nullable()->after('personal_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proceso_documentos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['nombre_original', 'mime_type', 'tamano']);
        });
    }
};
