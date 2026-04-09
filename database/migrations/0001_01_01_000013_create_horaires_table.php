<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('jour'); // 1=lundi ... 7=dimanche
            $table->time('matin_ouverture')->nullable();
            $table->time('matin_fermeture')->nullable();
            $table->time('aprem_ouverture')->nullable();
            $table->time('aprem_fermeture')->nullable();
            $table->boolean('ferme')->default(false);
            $table->timestamps();

            $table->unique(['etablissement_id', 'jour']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horaires');
    }
};
