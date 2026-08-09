<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medecins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('numero_ordre')->unique();
            $table->text('biographie')->nullable();
            $table->unsignedSmallInteger('annees_experience')->default(0);
            $table->unsignedInteger('tarif_consultation')->default(0); // en FCFA
            $table->unsignedSmallInteger('duree_consultation_defaut')->default(30); // minutes
            $table->string('salle')->nullable();
            $table->timestamps();
        });

        // Un médecin peut être rattaché à plusieurs spécialités
        Schema::create('medecin_specialite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medecin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialite_id')->constrained()->cascadeOnDelete();
            $table->unique(['medecin_id', 'specialite_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medecin_specialite');
        Schema::dropIfExists('medecins');
    }
};
