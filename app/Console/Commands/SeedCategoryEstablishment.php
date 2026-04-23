<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Establishment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedCategoryEstablishment extends Command
{
    protected $signature = 'seed:category-establishment {--fresh : Vide le pivot avant}';

    protected $description = 'Peuple le pivot category_establishment selon le type des établissements';

    /**
     * Mapping type → parent category slugs.
     * Parent categories come from the legacy categorie_prestation structure.
     */
    private array $typeMapping = [
        0 => ['soin-visage', 'epilation', 'manucure-pedicure', 'maquillage-cils-sourcils', 'massage-bien-etre'],
        1 => ['epilation', 'soin-visage', 'maquillage-cils-sourcils', 'massage-bien-etre'],
        2 => ['hydrotherapie', 'massage-bien-etre', 'soin-visage'],
        3 => ['hydrotherapie', 'massage-bien-etre'],
    ];

    public function handle(): int
    {
        if ($this->option('fresh')) {
            DB::table('category_establishment')->truncate();
            $this->info('Pivot vidé.');
        }

        $categoryIds = [];
        foreach ($this->typeMapping as $type => $slugs) {
            $categoryIds[$type] = Category::whereIn('slug', $slugs)->pluck('id')->all();
            if (count($categoryIds[$type]) !== count($slugs)) {
                $missing = array_diff($slugs, Category::whereIn('slug', $slugs)->pluck('slug')->all());
                $this->warn("Type $type : slugs introuvables : ".implode(', ', $missing));
            }
        }

        $total = 0;
        Establishment::active()
            ->whereIn('type', array_keys($this->typeMapping))
            ->select('id', 'type')
            ->chunkById(500, function ($chunk) use ($categoryIds, &$total) {
                $rows = [];
                foreach ($chunk as $e) {
                    foreach ($categoryIds[$e->type] as $catId) {
                        $rows[] = ['establishment_id' => $e->id, 'category_id' => $catId];
                    }
                }
                DB::table('category_establishment')->insertOrIgnore($rows);
                $total += count($rows);
                $this->line("  +".count($rows)." (total $total)");
            });

        $this->info("Fait : $total associations établissement-catégorie.");

        return self::SUCCESS;
    }
}
