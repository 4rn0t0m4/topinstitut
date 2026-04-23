<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('google_import_progress');
    }

    public function down(): void
    {
        Schema::create('google_import_progress', function ($table) {
            $table->string('department_code', 3)->primary();
            $table->boolean('completed')->default(false);
            $table->unsignedInteger('total_imported')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });
    }
};
