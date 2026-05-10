<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            // Caractéristiques pratiques (filtres) : tableau de slugs
            // ex : ["pmr", "men", "organic", "parking", "english"]
            $table->json('features')->nullable()->after('services');
        });
    }

    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn('features');
        });
    }
};
