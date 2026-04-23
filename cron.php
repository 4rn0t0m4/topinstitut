<?php

/**
 * Standalone cron entry point for OVH scheduled tasks.
 *
 * OVH task configuration :
 *   Commande : /usr/local/php8.3/bin/php /homez.xxx/www/new/cron.php [action]
 *   Actions : import-places (default) | import-reviews | import-photos | reminders
 *
 * Runs without CLI by bootstrapping Laravel from the shared hosting PHP.
 */

echo date('Y-m-d H:i:s')." - Cron démarré\n";

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$action = $argv[1] ?? 'import-places';

$actions = [
    'import-places'  => fn () => $kernel->call('import:google-places'),
    'import-reviews' => fn () => $kernel->call('import:google-reviews', ['--limit' => 10]),
    'import-photos'  => fn () => $kernel->call('import:google-photos', ['--limit' => 5, '--max-photos' => 5]),
    'reminders'      => fn () => $kernel->call('reminders:send'),
];

if (! isset($actions[$action])) {
    echo "Unknown action '$action'. Available: ".implode(', ', array_keys($actions))."\n";
    exit(1);
}

$actions[$action]();
echo $kernel->output();

echo date('Y-m-d H:i:s')." - Cron terminé\n";
