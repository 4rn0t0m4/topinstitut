<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_imports', function (Blueprint $table) {
            $table->id();
            $table->string('place_id')->unique();
            $table->foreignId('establishment_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['imported', 'ignored', 'duplicate'])->default('imported');
            $table->timestamps();
        });

        Schema::create('google_import_progress', function (Blueprint $table) {
            $table->string('department_code', 3)->primary();
            $table->boolean('completed')->default(false);
            $table->unsignedInteger('total_imported')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_import_progress');
        Schema::dropIfExists('google_imports');
    }
};
