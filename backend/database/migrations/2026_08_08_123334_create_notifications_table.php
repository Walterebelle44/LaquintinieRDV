<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications_envoyees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rendez_vous_id')->nullable()->constrained('rendez_vous')->nullOnDelete();
            $table->enum('type', [
                'confirmation_inscription', 'confirmation_rdv', 'nouvelle_demande_medecin',
                'rdv_accepte', 'rdv_refuse', 'rdv_reprogramme', 'rappel_24h', 'rappel_1h',
                'rdv_annule', 'liste_attente_liberee',
            ]);
            $table->enum('canal', ['email', 'sms']);
            $table->text('contenu');
            $table->enum('statut_envoi', ['en_attente', 'envoye', 'echoue'])->default('en_attente');
            $table->timestamp('envoye_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_envoyees');
    }
};
