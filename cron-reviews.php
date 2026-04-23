<?php

/**
 * Scheduled task entry point for OVH : import Google reviews.
 */

echo date('Y-m-d H:i:s')." - Cron import-reviews démarré\n";

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->call('import:google-reviews', ['--limit' => 10]);
echo $kernel->output();

echo date('Y-m-d H:i:s')." - Cron terminé\n";
