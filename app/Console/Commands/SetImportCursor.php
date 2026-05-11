<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetImportCursor extends Command
{
    protected $signature = 'import:cursor-set
        {--cycle= : Nouvelle valeur de cycle_count (offset villes = cycle × 5)}
        {--query= : Nouvelle valeur de query_index (0..2)}
        {--last-dept= : Nouvelle valeur de last_department_code (vide pour repartir au 1er)}
        {--show : Affiche juste l état actuel sans modifier}';

    protected $description = 'Inspecte ou force la valeur du curseur Google Places';

    public function handle(): int
    {
        $cursor = DB::table('google_import_cursor')->where('id', 1)->first();

        $this->line('=== État actuel ===');
        if (! $cursor) {
            $this->warn('Aucun curseur en base.');
        } else {
            $this->line("cycle_count           : {$cursor->cycle_count}");
            $this->line("query_index           : {$cursor->query_index}");
            $this->line("last_department_code  : ".($cursor->last_department_code ?? '(null)'));
            $this->line("updated_at            : {$cursor->updated_at}");
        }

        if ($this->option('show')) {
            return self::SUCCESS;
        }

        $updates = [];
        if ($this->option('cycle') !== null) {
            $updates['cycle_count'] = (int) $this->option('cycle');
        }
        if ($this->option('query') !== null) {
            $updates['query_index'] = (int) $this->option('query');
        }
        if ($this->option('last-dept') !== null) {
            $val = $this->option('last-dept');
            $updates['last_department_code'] = $val === '' ? null : $val;
        }

        if (empty($updates)) {
            $this->info('Rien à modifier. Utilise --cycle, --query ou --last-dept.');

            return self::SUCCESS;
        }

        $updates['updated_at'] = now();

        if ($cursor) {
            DB::table('google_import_cursor')->where('id', 1)->update($updates);
        } else {
            DB::table('google_import_cursor')->insert(array_merge(
                ['id' => 1, 'cycle_count' => 0, 'query_index' => 0, 'last_department_code' => null, 'created_at' => now()],
                $updates
            ));
        }

        $this->info('Curseur mis à jour : '.json_encode($updates));

        return self::SUCCESS;
    }
}
