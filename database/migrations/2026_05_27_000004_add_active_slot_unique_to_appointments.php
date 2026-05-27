<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Colonne générée : contient starts_at uniquement pour les RDV actifs
            // (confirmed/completed), NULL sinon. MySQL autorise plusieurs NULL dans un
            // index unique → un créneau libéré par annulation reste re-réservable.
            $table->dateTime('active_slot')
                ->storedAs("CASE WHEN status IN ('confirmed','completed') THEN starts_at ELSE NULL END")
                ->nullable()
                ->after('ends_at');

            // Garantie BDD : un seul RDV actif par praticien et créneau, quelle que soit
            // l'isolation transactionnelle (filet en plus du verrou applicatif).
            $table->unique(['practitioner_id', 'active_slot'], 'appointments_practitioner_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('appointments_practitioner_slot_unique');
            $table->dropColumn('active_slot');
        });
    }
};
