<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use \Carbon\Carbon;
use App\Models\Floor;
use App\Models\FloorSection;
use App\Models\UtilFloorSectionIntv;

class FloorSectUtilLoader implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $now = new Carbon;
      $now->second = 0;

      // fetch all active floors
      $fids = Floor::where('status', 1)->pluck('id');
      $fss = FloorSection::whereIn('floor_id', $fids)->where('status', 1)->get();

      foreach($fss as $fs){
        $data = $fs->SeatSummary(false);
        if($data['total'] > 0){
          $perc = $data['used'] / $data['total'] * 100;
          $toinsert = [
            'building_id' => $fs->Floor->building_id,
            'floor_id' => $fs->floor_id,
            'floor_section_id' => $fs->id,
            'report_time' => $now->toDateTimeString(),
            'total_seat' => $data['total'],
            'occupied_seat' => $data['used'],
            'free_seat' => $data['vacant'],
            'utilization' => $perc,
          ];

          $nu = new UtilFloorSectionIntv($toinsert);
          $nu->save();
        }
      }

    }
}
