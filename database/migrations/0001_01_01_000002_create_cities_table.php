<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('postal_code', 5)->index();
            $table->string('insee_code', 5)->unique();
            $table->string('department_code', 3);
            $table->unsignedInteger('population')->default(0);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->foreign('department_code')->references('code')->on('departments');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
