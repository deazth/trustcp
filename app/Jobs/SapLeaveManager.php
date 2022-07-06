<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\SapLeaveInfo;


class SapLeaveManager implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
      \Illuminate\Support\Facades\Log::info('SapLeaveManager ' . $this->job->getJobId() . ' processed by ' . gethostname());
      // get distinct users to be processed
      $users = SapLeaveInfo::where('load_status', 'N')->distinct('personel_no')->get('personel_no')->pluck('personel_no');
      $counter = 0;
      foreach ($users as $key => $value) {
        SapLeaveLoader::dispatch($value);
        $counter++;
      }
      \Illuminate\Support\Facades\Log::info('SapLeaveManager: ' . $counter . ' users queued for processing');

    }
}
