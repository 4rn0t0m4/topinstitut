<?php

/**
 * Scheduled task entry point for OVH : import Google photos.
 */

echo date('Y-m-d H:i:s')." - Cron import-photos démarré\n";

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->call('import:google-photos', ['--limit' => 5, '--max-photos' => 5]);
echo $kernel->output();

echo date('Y-m-d H:i:s')." - Cron terminé\n";
