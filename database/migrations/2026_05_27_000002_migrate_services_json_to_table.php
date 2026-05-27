<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recopie le JSON establishments.services vers la table services.
        DB::table('establishments')
            ->whereNotNull('services')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $items = json_decode($row->services, true);
                    if (! is_array($items)) {
                        continue;
                    }

                    $order = 0;
                    foreach ($items as $item) {
                        $name = trim($item['name'] ?? '');
                        if ($name === '') {
                            continue;
                        }

                        DB::table('services')->insert([
                            'establishment_id' => $row->id,
                            'name' => mb_substr($name, 0, 255),
                            'description' => isset($item['description']) ? mb_substr(trim($item['description']), 0, 500) : null,
                            'duration_minutes' => $this->parseDuration($item['duration'] ?? null),
                            'price' => isset($item['price']) ? mb_substr(trim($item['price']), 0, 50) : null,
                            'is_bookable' => true,
                            'sort_order' => $order++,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });

        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn('services');
        });
    }

    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->json('services')->nullable()->after('pricing');
        });

        // Reconstruit le JSON depuis la table services.
        $grouped = DB::table('services')->orderBy('sort_order')->get()->groupBy('establishment_id');
        foreach ($grouped as $establishmentId => $services) {
            $json = $services->map(fn ($s) => [
                'name' => $s->name,
                'description' => $s->description ?? '',
                'duration' => $s->duration_minutes ? $s->duration_minutes.' min' : '',
                'price' => $s->price ?? '',
            ])->values()->all();

            DB::table('establishments')->where('id', $establishmentId)->update(['services' => json_encode($json)]);
        }
    }

    /**
     * Convertit une durée texte ("45 min", "1h", "1h30", "30") en minutes. Défaut 30.
     */
    private function parseDuration(?string $raw): int
    {
        if (! $raw) {
            return 30;
        }
        $raw = strtolower(trim($raw));

        // "1h30", "1 h 30", "1h"
        if (preg_match('/(\d+)\s*h\s*(\d+)?/', $raw, $m)) {
            return ((int) $m[1]) * 60 + (int) ($m[2] ?? 0);
        }
        // "45 min", "45min", "45"
        if (preg_match('/(\d+)/', $raw, $m)) {
            return (int) $m[1];
        }

        return 30;
    }
};
