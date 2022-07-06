<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\common\CommonHelper;
use App\common\CheckinHelper;
use App\Models\UserSeatBooking;

class SeatBookExpire implements ShouldQueue
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
      // \Illuminate\Support\Facades\Log::info('SeatBookExpire ' . $this->job->getJobId() . ' processed by ' . gethostname());
      $now = new \Carbon\Carbon;
      $mintime = new \Carbon\Carbon;
      $mindelay = CommonHelper::GetCConfig('book_late_minutes', 60);
      $mintime->subMinutes($mindelay);

      // find the booking that has passed the start time
      $oldbooks = UserSeatBooking::where('status', 'Booked')
        ->where('start_time', '<', $mintime->toDateTimeString())
        ->get();

      foreach ($oldbooks as $key => $value) {
        $value->CancelBooking('Expired', 'Not checked-in in time');
      }

      // find the booking that has passed the end time
      $oldbooks = UserSeatBooking::where('status', 'Booked')
        ->where('end_time', '<', $now->toDateTimeString())
        ->get();

      foreach ($oldbooks as $key => $value) {
        $value->CancelBooking('Expired', 'Ended');
      }

      // kick checkins that has passed the end time
      $endedbooks = UserSeatBooking::where('status', 'Checked-in')
        ->where('end_time', '<', $now->toDateTimeString())
        ->get();

      foreach ($endedbooks as $key => $value) {
        if(isset($value->seat_checkin_id)){
          CheckinHelper::SeatCheckout($value->User, $value->seat_checkin_id);
        }

        $value->CancelBooking('Ended', '');
      }
    }
}
