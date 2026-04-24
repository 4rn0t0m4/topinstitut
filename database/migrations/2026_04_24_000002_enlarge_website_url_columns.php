<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Google renvoie parfois des URLs > 255 caractères (params de tracking, hash profonds)
        // → string(500) pour website, text pour google_maps_url (peut contenir des tokens longs).
        Schema::table('establishments', function (Blueprint $table) {
            $table->string('website', 500)->nullable()->change();
            $table->text('google_maps_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->string('website', 255)->nullable()->change();
            $table->string('google_maps_url', 255)->nullable()->change();
        });
    }
};
