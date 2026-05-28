<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->timestamp('trial_started_at')->nullable()->after('subscription_ends_at');
        });

        // Backfill : tout établissement déjà revendiqué (≥1 propriétaire) et non payant
        // démarre une période d'essai d'un mois.
        $now = Carbon::now();
        $end = $now->copy()->addMonth();

        $ownedEstablishmentIds = DB::table('establishment_user')->distinct()->pluck('establishment_id');

        DB::table('establishments')
            ->whereIn('id', $ownedEstablishmentIds)
            ->whereNull('stripe_subscription_id')
            ->whereNull('trial_started_at')
            ->update([
                'trial_started_at' => $now,
                'subscription_tier' => 'premium',
                'subscription_ends_at' => $end,
            ]);
    }

    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn('trial_started_at');
        });
    }
};
