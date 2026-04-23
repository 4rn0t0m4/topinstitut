<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1=Monday ... 7=Sunday
            $table->time('open_am')->nullable();
            $table->time('close_am')->nullable();
            $table->time('open_pm')->nullable();
            $table->time('close_pm')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['establishment_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
