<?php

use App\Console\Commands\SendBookingReminders; 
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
 // Run hourly from 8 AM to 5 PM on weekdays...
 

 
 if(env('APP_ENV') == 'local'){
    Schedule::command('booking:reminders')->everySecond();
 }

 if(env('APP_ENV') == 'production'){
    Schedule::command('booking:reminders')->hourly()->between('8:00', '17:00')->everyFiveMinutes();
}

 
