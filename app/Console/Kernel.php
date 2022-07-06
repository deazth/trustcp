<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\FloorSectUtilLoader;
use App\Jobs\FloorSectDailyLoader;
use App\Jobs\SeatBookExpire;
use App\Jobs\AreaUtilIntvLoader;
use App\Jobs\AreaDailyLoader;
use App\Jobs\SapProfileLoader;
use App\Jobs\SapLeaveManager;
use App\Jobs\DailyUserStatLoader;

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
      // load the hourly seat util
      // $schedule->job(new FloorSectUtilLoader)->hourly();
      $schedule->job(new FloorSectUtilLoader)->hourly()->onOneServer();

      // load the daily summary for seat util
      $schedule->job(new FloorSectDailyLoader)->dailyAt('00:30')->onOneServer();

      // kick the expired seat Booking
      $schedule->job(new SeatBookExpire)->everyThreeMinutes()->onOneServer();

      // load meeting area utils
      $schedule->job(new AreaUtilIntvLoader)->hourly()->onOneServer();
      $schedule->job(new AreaDailyLoader)->dailyAt('00:45')->onOneServer();

      // SAP data loader
      $schedule->job(new SapProfileLoader)->twiceDaily(1, 13)->onOneServer();
      $schedule->job(new SapLeaveManager)->twiceDaily(2, 12)->onOneServer();

      // purge unused token
      $schedule->command('passport:purge')->daily()->onOneServer();

      // user stat loader
      $schedule->job(new DailyUserStatLoader)->dailyAt('01:22')->onOneServer();

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
