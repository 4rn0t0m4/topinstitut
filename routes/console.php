<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('import:google-places --limit=10')->hourly();
Schedule::command('import:google-reviews --limit=10')->hourlyAt(20);
Schedule::command('import:google-photos --limit=5 --max-photos=5')->hourlyAt(40);
