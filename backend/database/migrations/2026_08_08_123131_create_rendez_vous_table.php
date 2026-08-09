<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('medecin_id')->constrained('medecins')->cascadeOnDelete();
            // Créé par un agent/secrétaire au guichet, le cas échéant
            $table->foreignId('cree_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->string('motif')->nullable();
            $table->enum('statut', [
                'en_attente', 'confirme', 'refuse', 'reprogramme', 'termine', 'annule', 'absent',
            ])->default('en_attente');
            $table->string('motif_refus')->nullable();
            $table->timestamp('repondu_le')->nullable();
            $table->timestamp('annule_le')->nullable();
            $table->enum('annule_par', ['patient', 'medecin', 'admin'])->nullable();
            $table->timestamps();

            $table->index(['medecin_id', 'date', 'heure_debut']);
            $table->index(['patient_id', 'statut']);
        });

        // Liste d'attente lorsqu'un créneau souhaité est complet
        Schema::create('liste_attentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('medecin_id')->constrained('medecins')->cascadeOnDelete();
            $table->date('date_souhaitee');
            $table->enum('statut', ['en_attente', 'notifie', 'expire', 'converti'])->default('en_attente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liste_attentes');
        Schema::dropIfExists('rendez_vous');
    }
};
