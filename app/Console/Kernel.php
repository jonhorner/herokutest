<?php

namespace App\Console;

use App\Constants\Constants;
use App\Models\Cron;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        // $schedule->command('inspire')->hourly();

        $schedule->command('updateGuild')
            ->everyMinute()
            ->when(function() {
                return Cron::shouldIRun(
                    'updateGuild',
                    Constants::GOOGLE_SHEET_CRON_PERIOD_MINS
                );
            });

        $schedule->command('googleReport')
            ->everyMinute()
            ->when(function() {
                return Cron::shouldIRun(
                    'googleReport',
                    Constants::GOOGLE_SHEET_CRON_PERIOD_MINS
                );
            });

        $schedule->command('squadReport')
            ->everyMinute()
            ->when(function() {
                return Cron::shouldIRun(
                    'squadReport',
                    Constants::GOOGLE_SHEET_CRON_PERIOD_MINS
                );
            });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
