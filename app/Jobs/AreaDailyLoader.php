<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AreaUtilIntv;
use App\Models\AreaUtilDaily;
use App\Models\Seat;

class AreaDailyLoader implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $start = '';
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
      $now = new \Carbon\Carbon;
      $now->subDay();
      $this->start = $now->toDateString();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      // get all meeting area
      $areas = Seat::where('status', 1)
        ->where('seat_type', 'Meeting Area')
        ->get();

      foreach($areas as $ar){
        $flooor = $ar->floor_section->Floor;

        $rec = AreaUtilIntv::where('seat_id', $ar->id)
          ->whereDate('record_hour', $this->start)
          ->sum('is_used');

        // lets assume optimal full day is 8 hours
        $perc = $rec / 8 * 100;

        $aui = new AreaUtilDaily;
        $aui->building_id = $flooor->building_id;
        $aui->floor_id = $flooor->id;
        $aui->seat_id = $ar->id;
        $aui->report_date = $this->start;
        $aui->total_hour_used = $rec;
        $aui->utilization = $perc;
        $aui->save();
      }
    }
}
