<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Disponibilités récurrentes hebdomadaires (ex. Lundi 08h-15h)
        Schema::create('disponibilites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medecin_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('jour_semaine'); // 1=Lundi ... 7=Dimanche (ISO-8601)
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Indisponibilités ponctuelles (congés, urgences, jours bloqués)
        Schema::create('indisponibilites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medecin_id')->constrained()->cascadeOnDelete();
            $table->dateTime('debut');
            $table->dateTime('fin');
            $table->string('motif')->nullable();
            $table->timestamps();
        });

        // Jours fériés / fermetures exceptionnelles de l'hôpital (globaux)
        Schema::create('jours_feries', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('libelle');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disponibilites');
        Schema::dropIfExists('indisponibilites');
        Schema::dropIfExists('jours_feries');
    }
};
