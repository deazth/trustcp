<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\common\DiaryHelper;
use App\Models\StaffLeave;
use \Carbon\Carbon;

class ManualLeaveZerod implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $staff_leave_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($slid)
    {
      $this->staff_leave_id = $slid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $sl = StaffLeave::find($this->staff_leave_id);

      if($sl){
        $sdate = new Carbon($sl->start_date);
        $edate = new Carbon($sl->end_date);

        while($sdate->lte($edate)){
          // get the df
          $df = DiaryHelper::GetDailyPerfObj($sl->user_id, $sdate);
          // zerorize it
          $df->is_off_day = true;
          $df->leave_type_id = $sl->leave_type_id;
          $df->zerorized = true;
          $df->expected_hours = 0;
          $df->save();

          $sdate->addDay();
        }
      }
    }
}
