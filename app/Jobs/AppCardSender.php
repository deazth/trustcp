<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Models\AppreciateCard;
use App\Models\User;
use App\Notifications\AppreCard;

class AppCardSender implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    protected $userlist;
    protected $sender_id;
    protected $template;
    protected $content;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ul, $si, $tp, $ct)
    {
      $this->userlist = $ul;
      $this->sender_id = $si;
      $this->template = $tp;
      $this->content = $ct;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      try{
        $entrydate = date('Y-m-d');
        foreach($this->userlist as $auser){
          $nucard = new AppreciateCard;
          $nucard->user_id = $auser;
          $nucard->sender_id = $this->sender_id;
          $nucard->template = $this->template;
          $nucard->content = $this->content;
          $nucard->entry_date = $entrydate;
          $nucard->save();

          // send the actual email
          $recv = User::find($auser);
          $recv->notify(new AppreCard($nucard));
        }
      } catch(\Throwable $te){
        Log::error('postcard - sender: ' . $this->sender_id . ', content: ' . $this->content . ', recp: ' . json_encode($this->userlist));
        Log::error($te->getMessage());
      }
    }
}
