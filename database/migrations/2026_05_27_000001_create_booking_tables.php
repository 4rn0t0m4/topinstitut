<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description', 500)->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->string('price', 50)->nullable();
            $table->boolean('is_bookable')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['establishment_id', 'sort_order']);
        });

        Schema::create('practitioners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['establishment_id', 'sort_order']);
        });

        Schema::create('practitioner_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practitioner_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1 = lundi … 7 = dimanche
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->index(['practitioner_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practitioner_schedules');
        Schema::dropIfExists('practitioners');
        Schema::dropIfExists('services');
    }
};
