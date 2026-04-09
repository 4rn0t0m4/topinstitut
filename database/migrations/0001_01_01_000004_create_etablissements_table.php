<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etablissements', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('type')->default(0);
            $table->string('titre');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('adresse')->nullable();
            $table->string('cp', 5)->nullable();
            $table->string('ville')->nullable();
            $table->string('dept', 3)->nullable();
            $table->foreignId('ville_id')->nullable()->constrained('villes')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('rayon')->default(0);
            $table->text('description')->nullable();
            $table->text('horaires')->nullable();
            $table->text('tarifs')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('portable', 20)->nullable();
            $table->string('siret', 14)->nullable();
            $table->string('photo')->nullable();
            $table->string('accroche')->nullable();
            $table->decimal('moyenne', 3, 1)->default(0);
            $table->unsignedInteger('nb_avis')->default(0);
            $table->boolean('valide')->default(false);
            $table->unsignedInteger('classement_ville')->default(0);
            $table->unsignedInteger('legacy_id')->nullable()->index();
            $table->timestamps();

            $table->index(['valide', 'type']);
            $table->index(['valide', 'ville_id']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etablissements');
    }
};
