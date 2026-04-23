<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('type', 20)->default('contact')->after('establishment_id');
            $table->string('phone', 20)->nullable()->after('email');
            $table->date('requested_date')->nullable()->after('content');
            $table->string('requested_time', 20)->nullable()->after('requested_date');
            $table->string('requested_service')->nullable()->after('requested_time');

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'phone', 'requested_date', 'requested_time', 'requested_service']);
        });
    }
};
