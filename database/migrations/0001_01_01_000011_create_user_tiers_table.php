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
            $table->string('name');
            $table->unsignedInteger('min_reviews');
            $table->unsignedInteger('max_reviews');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_tiers');
    }
};
