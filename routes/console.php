<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hora inicial para los comandos
$startTime = Carbon::create(null, null, null, 10, 15, 0);

// Mantener los horarios originales con incrementos de 1 minuto
Schedule::command('in:cagedim')->at($startTime->format('H:i'))->weekdays();
Schedule::command('in:cagedim')->everyFiveMinutes();

Schedule::command('in:tradePolicy')->at($startTime->addMinute()->format('H:i'))->weekdays();
Schedule::command('in:tradePolicy')->everyFiveMinutes();

Schedule::command('in:pharmacies')->at($startTime->addMinute()->format('H:i'))->weekdays();
Schedule::command('in:pharmacies')->everyFiveMinutes();

Schedule::command('in:priceControlFile')->at($startTime->addMinute()->format('H:i'))->weekdays();
Schedule::command('in:priceControlFile')->everyFiveMinutes();

Schedule::command('in:productControlFile')->at($startTime->addMinute()->format('H:i'))->weekdays();
Schedule::command('in:productControlFile')->everyFiveMinutes();

Schedule::command('out:blockedOrders')->at($startTime->addMinute()->format('H:i'))->weekdays();
Schedule::command('out:blockedOrders')->everyFiveMinutes();

Schedule::command('out:newCustomerOrChange')->at($startTime->addMinute()->format('H:i'))->weekdays();
Schedule::command('out:newCustomerOrChange')->everyFiveMinutes();

Schedule::command('out:OdersSentToNomane')->at($startTime->addMinute()->format('H:i'))->weekdays();
Schedule::command('out:OdersSentToNomane')->everyFiveMinutes();

Schedule::command('out:productAudit')->at($startTime->addMinute()->format('H:i'))->weekdays();
Schedule::command('out:productAudit')->everyFiveMinutes();

Schedule::command('out:quarterlyActivityReporting')->at($startTime->addMinute()->format('H:i'))->weekdays();
Schedule::command('out:quarterlyActivityReporting')->everyFiveMinutes();

Schedule::command('out:processUnavailableProducts')->at($startTime->addMinute()->format('H:i'))->weekdays();
Schedule::command('out:processUnavailableProducts')->everyFiveMinutes();

Schedule::command('in:poducts')->at($startTime->addMinute()->format('H:i'))->weekdays();
Schedule::command('in:poducts')->everyFiveMinutes();

Schedule::command('in:poducts')->at('13:30')->weekdays()
    //->cron('* * * * *')
    //->timezone('America/New_York')
    //->hourly()
    //->everyMinute()
    //->everyFiveMinutes()

    //->at(['13:35', '13:36', '13:37', '13:38', '13:39'])
    //->at('13:35', '13:36', '13:37', '13:38', '13:39')
    //->timezone('Europe/Paris')
    //->between('8:00', '17:00')
    //->environments(['staging', 'production', 'local'])
    //->runInBackground()
    //->emailOutputTo(env('EMAIL_FOR_APP_ERROR'))
    //->emailOutputOnFailure(env('EMAIL_FOR_APP_ERROR'))
    //->onSuccess(function () {
        // The task succeeded...
    //})
    //->onFailure(function () {
        // The task failed...
    //})
    ;
