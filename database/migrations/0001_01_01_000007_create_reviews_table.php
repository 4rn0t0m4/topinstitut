<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->string('ip', 45)->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->string('verification_token')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_rejected')->default(false);
            $table->text('reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->unsignedTinyInteger('rating_welcome');
            $table->unsignedTinyInteger('rating_quality');
            $table->unsignedTinyInteger('rating_variety');
            $table->unsignedTinyInteger('rating_price');
            $table->unsignedTinyInteger('rating_ambiance');
            $table->unsignedTinyInteger('rating_cleanliness');
            $table->timestamps();

            $table->index(['establishment_id', 'is_approved', 'is_rejected']);
        });

        Schema::create('review_votes', function (Blueprint $table) {
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_helpful');
            $table->primary(['review_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_votes');
        Schema::dropIfExists('reviews');
    }
};
