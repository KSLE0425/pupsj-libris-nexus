<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reservations:expire')->everyMinute();
Schedule::command('library:flag-overdue')->dailyAt('01:00');
Schedule::command('library:flag-inactive-users')->dailyAt('02:00');
