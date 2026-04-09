<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departements', function (Blueprint $table) {
            $table->string('numero', 3)->primary();
            $table->string('departement');
            $table->string('departement_url');
            $table->string('region');
            $table->string('article')->nullable();
            $table->decimal('gmap_latitude', 10, 7)->nullable();
            $table->decimal('gmap_longitude', 10, 7)->nullable();
            $table->unsignedTinyInteger('zoom')->default(9);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departements');
    }
};
