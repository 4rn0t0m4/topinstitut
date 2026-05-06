<?php

/**
 * Scheduled task entry point for OVH : scrape emails from establishments' websites.
 *   /usr/local/php8.4/bin/php /homez.xxx/www/new/cron-emails.php
 */

$logFile = __DIR__.'/storage/logs/cron.log';
$log = function ($msg) use ($logFile) {
    @file_put_contents($logFile, '['.date('Y-m-d H:i:s')."] $msg\n", FILE_APPEND);
    echo $msg."\n";
};

$log('=== cron-emails.php lancé ===');

try {
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

    $kernel->call('scrape:emails', ['--limit' => 10]);
    $log('Sortie : '.trim($kernel->output()));
} catch (\Throwable $e) {
    $log('EXCEPTION '.get_class($e).': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
}

$log('=== cron-emails.php terminé ===');
