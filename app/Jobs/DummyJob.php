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



class DummyJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private $perner;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->onConnection('redis');
    
  }

 

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {


    Redis::throttle('any_key')->block(0)->allow(1000)->every(5)->then(function () {

      
      
    }, function () {
      // Could not obtain lock...

      $users = User::get();

      foreach ($users as $key => $value) {

      }



      return $this->release(5);
    });
    
  }

  public function tags()
  {
      return ['render', 'video:'.$this->user->id];
  }
}
