<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['establishment_id', 'sort_order']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('service_category_id')->nullable()->after('establishment_id')
                ->constrained('service_categories')->nullOnDelete();
        });

        // Reprise des catégories texte existantes en lignes service_categories.
        if (Schema::hasColumn('services', 'category')) {
            $orderByEstab = [];
            $map = [];
            $rows = DB::table('services')
                ->whereNotNull('category')->where('category', '!=', '')
                ->orderBy('id')
                ->get(['id', 'establishment_id', 'category']);

            foreach ($rows as $svc) {
                $key = $svc->establishment_id.'|'.$svc->category;
                if (! isset($map[$key])) {
                    $order = $orderByEstab[$svc->establishment_id] = ($orderByEstab[$svc->establishment_id] ?? -1) + 1;
                    $map[$key] = DB::table('service_categories')->insertGetId([
                        'establishment_id' => $svc->establishment_id,
                        'name' => $svc->category,
                        'sort_order' => $order,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                DB::table('services')->where('id', $svc->id)->update(['service_category_id' => $map[$key]]);
            }

            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
        });

        // Restaure le texte depuis les catégories.
        $cats = DB::table('service_categories')->pluck('name', 'id');
        foreach ($cats as $id => $name) {
            DB::table('services')->where('service_category_id', $id)->update(['category' => $name]);
        }

        Schema::table('services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_category_id');
        });

        Schema::dropIfExists('service_categories');
    }
};
