<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rendez_vous_id')->constrained('rendez_vous')->cascadeOnDelete();
            $table->string('numero')->unique(); // ex. LQT-2026-00841
            $table->string('qr_code_path')->nullable();
            $table->string('fichier_pdf_path')->nullable();
            $table->timestamp('genere_le');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
