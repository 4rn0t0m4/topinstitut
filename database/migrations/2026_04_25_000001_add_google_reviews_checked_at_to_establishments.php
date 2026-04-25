<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->timestamp('google_reviews_checked_at')->nullable()->after('google_reviews');
        });
    }

    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn('google_reviews_checked_at');
        });
    }
};
