<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('reminded_day_before_at')->nullable()->after('status');
            $table->timestamp('reminded_same_day_at')->nullable()->after('reminded_day_before_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['reminded_day_before_at', 'reminded_same_day_at']);
        });
    }
};
