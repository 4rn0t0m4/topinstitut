<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villes', function (Blueprint $table) {
            $table->id();
            $table->string('nom_ville');
            $table->string('code_postal', 5)->index();
            $table->string('url')->unique();
            $table->string('departement', 3);
            $table->unsignedInteger('habitants')->default(0);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->foreign('departement')->references('numero')->on('departements');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villes');
    }
};
