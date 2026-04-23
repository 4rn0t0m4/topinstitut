<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_import_cursor', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('query_index')->default(0);
            $table->string('last_department_code', 3)->nullable();
            $table->unsignedInteger('cycle_count')->default(0);
            $table->timestamps();
        });

        // Seed the singleton row
        \DB::table('google_import_cursor')->insert([
            'id' => 1,
            'query_index' => 0,
            'last_department_code' => null,
            'cycle_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('google_import_cursor');
    }
};
