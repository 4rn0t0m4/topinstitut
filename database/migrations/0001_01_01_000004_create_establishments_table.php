<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('establishments', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('type')->default(0);
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('google_maps_url')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code', 5)->nullable();
            $table->string('city')->nullable();
            $table->string('department_code', 3)->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('radius')->default(0);
            $table->text('description')->nullable();
            $table->text('pricing')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('siret', 14)->nullable();
            $table->string('photo')->nullable();
            $table->string('tagline')->nullable();
            $table->decimal('rating', 3, 1)->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('city_rank')->default(0);
            $table->string('google_place_id')->nullable();
            $table->decimal('google_rating', 2, 1)->nullable();
            $table->unsignedInteger('google_review_count')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'type']);
            $table->index(['is_active', 'city_id']);
            $table->index(['latitude', 'longitude']);
        });

        Schema::create('establishment_slugs', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('establishment_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('establishment_user', function (Blueprint $table) {
            $table->foreignId('establishment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['establishment_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establishment_user');
        Schema::dropIfExists('establishment_slugs');
        Schema::dropIfExists('establishments');
    }
};
