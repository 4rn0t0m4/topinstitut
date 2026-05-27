<?php

use Illuminate\Contracts\Console\Kernel;

/**
 * Point d'entrée cron OVH : rappels de rendez-vous (J-1 et jour même).
 * À programmer tous les jours à 8h.
 */
echo date('Y-m-d H:i:s')." - Cron rappels RDV démarré\n";

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);

$kernel->call('appointments:remind');
echo $kernel->output();

echo date('Y-m-d H:i:s')." - Cron terminé\n";
