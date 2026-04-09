<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->unsignedInteger('min_avis');
            $table->unsignedInteger('max_avis');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_tiers');
    }
};
