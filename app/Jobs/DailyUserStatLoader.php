<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\User;

class DailyUserStatLoader implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $indate;
    protected $reset;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($d = null, $r = false)
    {
      if($d == null){
        $cdate = new \Carbon\Carbon;
        $this->indate = $cdate->subDay()->toDateString();
      } else {
        $this->indate = $d;
      }

      $this->reset = $r;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $lob_list = User::where('status', 1)
        ->distinct()->select('lob_descr')->pluck('lob_descr')->toArray();

      foreach ($lob_list as $key => $value) {
        try {
          $data = \App\common\UserHelper::GetDailyLobUserStat($this->indate, $value, $this->reset);
        } catch (\Exception $e) {
          \Illuminate\Support\Facades\Log::info('DailyUserStatLoader ' . $value . ' error for ' . $this->indate);
          \Illuminate\Support\Facades\Log::error($e);
        }
      }
    }
}
