<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Floor;
use App\Models\FloorSection;
use App\Models\UtilFloorSectionIntv;
use App\Models\UtilFloorSectionDaily;

class FloorSectDailyLoader implements ShouldQueue
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
      $yesterday = new \Carbon\Carbon;
      $yesterday->second = 0;
      $yesterday->minute = 0;
      $yesterday->hour = 0;
      $yesterday->subDay();

      $fids = Floor::where('status', 1)->pluck('id');
      $fss = FloorSection::whereIn('floor_id', $fids)->where('status', 1)->get();

      foreach($fss as $fs){
        $rec = UtilFloorSectionIntv::where('floor_section_id', $fs->id)
          ->whereDate('report_time', $yesterday->toDateString())
          ->groupBy('floor_section_id')
          ->select('floor_section_id',
              \DB::raw('max(total_seat) as total_seat'),
              \DB::raw('max(occupied_seat) as max_occupied_seat'),
              \DB::raw('min(free_seat) as free_seat'),
              \DB::raw('max(utilization) as utilization'),
            )
          ->first();
        if($rec){
          $nu = UtilFloorSectionDaily::updateOrCreate(
            ['floor_section_id' => $fs->id, 'report_date' => $yesterday->toDateString()],
            [
              'building_id' => $fs->Floor->building_id,
              'floor_id' => $fs->floor_id,
              'total_seat' => $rec->total_seat,
              'max_occupied_seat' => $rec->max_occupied_seat,
              'free_seat' => $rec->free_seat,
              'utilization' => $rec->utilization
            ]
          );
        }

      }
    }
}
