<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agrégat journalier des vues de fiche.
        Schema::create('establishment_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->unique(['establishment_id', 'date']);
        });

        // Agrégat journalier des évènements (clics, ouvertures modale).
        // event_type ∈ phone_click, directions_click, website_click,
        //              gallery_open, booking_modal_open, booking_completed
        Schema::create('establishment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('event_type', 32);
            $table->unsignedInteger('count')->default(0);
            $table->timestamps();
            $table->unique(['establishment_id', 'date', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('establishment_events');
        Schema::dropIfExists('establishment_visits');
    }
};
