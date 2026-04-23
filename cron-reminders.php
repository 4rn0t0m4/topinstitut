<?php

/**
 * Scheduled task entry point for OVH : send review reminder emails.
 */

echo date('Y-m-d H:i:s')." - Cron reminders démarré\n";

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->call('reminders:send');
echo $kernel->output();

echo date('Y-m-d H:i:s')." - Cron terminé\n";
