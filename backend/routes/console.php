<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/*
|--------------------------------------------------------------------------
| Tâches planifiées (Laravel Scheduler)
|--------------------------------------------------------------------------
| Nécessite une entrée cron unique côté serveur :
|   * * * * * php /chemin/vers/artisan schedule:run >> /dev/null 2>&1
*/
Schedule::command('rdv:rappels')->everyFifteenMinutes();