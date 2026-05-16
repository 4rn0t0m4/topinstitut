<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            // Abonnement (free, premium)
            $table->string('subscription_tier', 20)->default('free')->after('is_active');
            $table->timestamp('subscription_ends_at')->nullable()->after('subscription_tier');
            $table->boolean('is_verified_owner')->default(false)->after('subscription_ends_at');

            // Mise en avant payante par ville (sponsorisé)
            $table->timestamp('featured_until')->nullable()->after('is_verified_owner');

            $table->index(['subscription_tier', 'subscription_ends_at']);
            $table->index('featured_until');
        });
    }

    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropIndex(['subscription_tier', 'subscription_ends_at']);
            $table->dropIndex(['featured_until']);
            $table->dropColumn(['subscription_tier', 'subscription_ends_at', 'is_verified_owner', 'featured_until']);
        });
    }
};
