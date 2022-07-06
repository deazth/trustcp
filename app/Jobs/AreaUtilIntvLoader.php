<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use \Carbon\Carbon;
use App\Models\AreaUtilIntv;
use App\Models\Seat;
use App\Models\AreaBooking;

class AreaUtilIntvLoader implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $start = '';
    private $end = '';
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
      $now = new Carbon;
      $now->second = 0;
      $now->minute = 0;
      $pasthour = new Carbon($now);
      $pasthour->subHour();

      $this->start = $pasthour->toDateTimeString();
      $this->end = $now->toDateTimeString();
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
        // check if there is any meeting booking within that period
        $gotbook = 0;
        $book = AreaBooking::where('seat_id', $ar->id)
          ->where('status', 'Active')
          ->where('start_time', '<', $this->end)
          ->where('end_time', '>=', $this->start)
          ->first();

        if($book){
          $gotbook = 1;
        }

        // insert to db
        $aui = new AreaUtilIntv;
        $flooor = $ar->floor_section->Floor;
        $aui->building_id = $flooor->building_id;
        $aui->floor_id = $flooor->id;
        $aui->seat_id = $ar->id;
        $aui->record_hour = $this->start;
        $aui->is_used = $gotbook;
        $aui->save();

      }
    }
}
