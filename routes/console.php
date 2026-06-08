<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
Schedule::command('app:remind-signatories')->everySixHours();
// Run the urgent reminder check every morning at 8:00 AM
Schedule::command('app:send-urgent-reminders')->dailyAt('08:00');
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
