<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Cron;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $crons = Cron::where('active', 1)->get();
        foreach ($crons as $cron) {
          //$schedule->call($cron->controller)->cron($cron->period);
        }
      // $schedule->call('App\Http\Controllers\SyncController@messages')->cron('0 0 * * *');
      // $schedule->call('App\Http\Controllers\NewPostTtnTrackController@checkStatus')->cron('0 0 * * *');
      //$schedule->call('App\Http\Controllers\UkrPostTtnTrackController@checkStatus')->cron('0 0 * * *');
      // $schedule->call('App\Http\Controllers\RfcController@store')->cron('58 23 * * *');
      // $schedule->call('App\Http\Controllers\OrderDayStatisticController@calcMonth')->cron('0 2 * * *');
      // $schedule->call('App\Http\Controllers\SyncController@newPost')->cron('0 8 * * *');
      // $schedule->call('App\Http\Controllers\SyncController@smsStatus')->cron('*/2 8-21 * * *');
      // $schedule->call('App\Http\Controllers\SyncController@orders')->cron('*/3 * * * *');
      // $schedule->call('App\Http\Controllers\SyncController@OrderProducts')->cron('*/5 7-21 * * *');
      // $schedule->call('App\Http\Controllers\TestController@feedbackcron')->cron('*/20 * * * *');
      // $schedule->call('App\Http\Controllers\SyncController@checkSputnikActivity')->cron('*/10 0-8 * * *');
      // $schedule->call('App\Http\Controllers\SyncController@checkSputnikActivity')->cron('*/10 21-23 * * *');

    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
