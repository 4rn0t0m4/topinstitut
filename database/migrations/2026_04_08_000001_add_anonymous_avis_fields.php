<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avis', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('pseudo_auteur')->nullable()->after('user_id');
            $table->string('email_auteur')->nullable()->after('pseudo_auteur');
            $table->string('token_validation', 64)->nullable()->after('email_auteur');
            $table->timestamp('email_verified_at')->nullable()->after('token_validation');
        });
    }

    public function down(): void
    {
        Schema::table('avis', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->dropColumn(['pseudo_auteur', 'email_auteur', 'token_validation', 'email_verified_at']);
        });
    }
};
