<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\User;
use App\Models\SapLeaveInfo;
use App\Models\LeaveType;
use App\Models\StaffLeave;


class SapLeaveLoader implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private $perner;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct($psno)
  {
    $this->perner = $psno;
  }

  /*
    S = success
    N = new
    D = duplicate
    I = ignore
    U = user issue

    */

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {


    //Redis::throttle('any_key')->block(0)->allow(1000)->every(5)->then(function () {

      $user = User::where('persno', $this->perner)->first();
      if ($user && $user->status == 1) {
      } else {
        SapLeaveInfo::where('load_status', 'N')
          ->where('personel_no', $this->perner)
          ->update(['load_status' => 'U']);
        return;
      }

      $cutis = SapLeaveInfo::where('load_status', 'N')
        ->where('personel_no', $this->perner)
        ->orderBy('timestamp', 'ASC')->get();

      foreach ($cutis as $c) {
        $leavetype = LeaveType::where('code', $c->leave_code)->first();
        if ($leavetype) {
        } else {
          $leavetype = new LeaveType;
          $leavetype->code = $c->leave_code;
          $leavetype->descr = $c->leave_describtion;
          $leavetype->hours_value = 8;  // default
          $leavetype->created_by = 1;
          $leavetype->save();
        }


        // REJECTED WITHDRAWN
        // check for existing cuti with same date

        $curcuti = StaffLeave::where('leave_type_id', $leavetype->id)
          ->whereDate('start_date', $c->date_start)
          ->whereDate('end_date', $c->date_end)
          ->where('user_id', $user->id)
          ->first();

        if ($curcuti) {
          // entry already exist. check if it's for reject or withdrawn
          if ($c->operation == 'INS' && ($c->status == 'REJECTED' || $c->status == 'WITHDRAWN')) {
            // reverse the cuti
            $curcuti->reverseCuti();
            // then delete the cuti
            // $curcuti->delete();

            // delete semua cuti untuk tarikh tu. cater cases multi lines
            $curcuti2 = StaffLeave::where('leave_type_id', $leavetype->id)
              ->whereDate('start_date', $c->date_start)
              ->whereDate('end_date', $c->date_end)
              ->where('user_id', $user->id)
              ->delete();

            $c->load_status = 'S';
          } elseif ($c->operation == 'DEL' && ($c->status == 'POSTED' || $c->status == 'WITHDRAWN')) {
            // reverse the cuti
            $curcuti->reverseCuti();
            // then delete the cuti
            // $curcuti->delete();

            // delete semua cuti untuk tarikh tu. cater cases multi lines
            $curcuti2 = StaffLeave::where('leave_type_id', $leavetype->id)
              ->whereDate('start_date', $c->date_start)
              ->whereDate('end_date', $c->date_end)
              ->where('user_id', $user->id)
              ->delete();

            $c->load_status = 'S';
          } else {
            // most likely duplicate. ignore
            $c->load_status = 'D';
          }
        } else {
          // not exist. create new
          if ($c->operation == 'INS' && $c->status == 'APPROVED') {
            // ignore this
            $c->load_status = 'I';
          } elseif ($c->status == 'REJECTED' || $c->status == 'WITHDRAWN') {
            // no need to create new for this one lol
            $c->load_status = 'I';
          } elseif ($c->operation == 'DEL') {
            // no need to create new for this one lol
            $c->load_status = 'I';
          } else {

            $nucuti = [
              'user_id' => $user->id,
              'start_date' => $c->date_start,
              'end_date' => $c->date_end,
              'leave_type_id' => $leavetype->id
            ];

            // hopefully this will prevent duplicates
            $curcuti = StaffLeave::updateOrCreate($nucuti, $nucuti);
            // $curcuti = new StaffLeave;
            // $curcuti->user_id = $user->id;
            // $curcuti->start_date = $c->date_start;
            // $curcuti->end_date = $c->date_end;
            // $curcuti->leave_type_id = $leavetype->id;
            // $curcuti->save();

            $curcuti->createCuti();
            $c->load_status = 'S';
          }
        }
        $c->save();
      }
   /** }, function () {
      // Could not obtain lock...

      return $this->release(5);
    });
    **/
  }
}
