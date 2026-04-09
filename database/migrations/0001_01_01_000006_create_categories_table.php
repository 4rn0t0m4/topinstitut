<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('legacy_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
        });

        Schema::create('categorie_etablissement', function (Blueprint $table) {
            $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('categorie_id')->constrained()->cascadeOnDelete();
            $table->primary(['etablissement_id', 'categorie_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorie_etablissement');
        Schema::dropIfExists('categories');
    }
};
