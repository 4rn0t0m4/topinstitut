<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('titre');
            $table->text('contenu');
            $table->string('ip', 45)->nullable();
            $table->boolean('valide')->default(false);
            $table->boolean('refus')->default(false);
            $table->text('reponse')->nullable();
            $table->timestamp('reponse_date')->nullable();
            $table->unsignedTinyInteger('note_accueil');
            $table->unsignedTinyInteger('note_qualite');
            $table->unsignedTinyInteger('note_choix');
            $table->unsignedTinyInteger('note_prix');
            $table->unsignedTinyInteger('note_cadre');
            $table->unsignedTinyInteger('note_proprete');
            $table->unsignedInteger('legacy_id')->nullable();
            $table->timestamps();

            $table->index(['etablissement_id', 'valide', 'refus']);
        });

        Schema::create('avis_utiles', function (Blueprint $table) {
            $table->foreignId('avis_id')->constrained('avis')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('utile');
            $table->primary(['avis_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avis_utiles');
        Schema::dropIfExists('avis');
    }
};
