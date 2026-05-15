<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('email')->nullable()->after('user_id');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('verification_token', 64)->nullable()->after('email_verified_at');
            $table->index('verification_token');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropIndex(['verification_token']);
            $table->dropColumn(['email', 'email_verified_at', 'verification_token']);
            // user_id ne peut pas être facilement non-nullable rollback car les données existantes pourraient violer
        });
    }
};
