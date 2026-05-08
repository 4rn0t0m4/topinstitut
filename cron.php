<?php

/**
 * Scheduled task entry point for OVH : import Google Places establishments.
 *   /usr/local/php8.3/bin/php /homez.xxx/www/new/cron.php
 *
 * Output is logged to storage/logs/cron.log so we can verify execution
 * even when stdout is not captured by OVH.
 */

$logFile = __DIR__.'/storage/logs/cron.log';
$log = function ($msg) use ($logFile) {
    @file_put_contents($logFile, '['.date('Y-m-d H:i:s')."] $msg\n", FILE_APPEND);
    echo $msg."\n";
};

$log('=== cron.php (import-places) lancé ===');

try {
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

    $kernel->call('import:google-places', ['--cities' => 3]);
    $log('Sortie : '.trim($kernel->output()));
} catch (\Throwable $e) {
    $log('EXCEPTION '.get_class($e).': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
}

$log('=== cron.php terminé ===');
