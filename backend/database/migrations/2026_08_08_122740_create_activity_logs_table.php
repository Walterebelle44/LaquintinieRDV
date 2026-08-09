<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('auteur_libelle')->nullable(); // snapshot au cas où l'utilisateur est supprimé
            $table->string('action');
            $table->string('type', 30)->default('info'); // securite, rdv, connexion, config, alerte, export
            $table->string('entite_type')->nullable();
            $table->unsignedBigInteger('entite_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
