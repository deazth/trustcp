<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\User;
use App\Models\SapEmpProfile;
use App\Models\UserTeamHistory;
use App\Models\Unit;
use App\Models\SubUnit;

class SapProfileLoaderByPersno implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;

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
    N = new
    D = done
    E = error
    R = reject

    */

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
     
      set_time_limit(0);
      $omdata = SapEmpProfile::where('load_status', 'N')->where('personel_no',$this->perner)->get();
      foreach ($omdata as $key => $value) {
        // remove spaces in the staff no
        $trimmedstaffno = str_replace(' ', '', $value->staff_id);

        try {
          // find this user
          $user = User::where('persno', $value->personel_no)->first();
          if($user){
            // user exist. use it
            $user->staff_no = $trimmedstaffno;
          } else {
            // check if staff no provided
            if(isset($value->staff_id) && $value->staff_id != 0){
              // find using staff no.
              $user = User::where('staff_no', $trimmedstaffno)->first();
              if($user){
                // user found. use it. tie it to the persno
                $user->persno = $value->personel_no;
              } else {
                // create new
                $user = new User;
                $user->staff_no = $trimmedstaffno;
                $user->persno = $value->personel_no;
                $user->isvendor = false;
              }
            } else {
              // no staff no. reject record
              $value->load_status = 'R';
              $value->save();
              continue;
            }
          }

          if($value->status == 'Inactive' || $value->status == 'Withdrawn'){
            // skip if for inactive
            $user->status = 0;
            $user->save();
            $value->load_status = 'D';
            $value->save();
            continue;
          }

          // update the rest of the record
          $gp_no = isset($value->group_no) ? $value->group_no : '0';
          $un_no = isset($value->unit_no) ? $value->unit_no : '0';

          // begin update the data
          $user->name = $value->name;
          $user->cost_center = $value->cost_center;
          $user->position = $value->postion_name;
          $user->lob = $gp_no;

          if($user->report_to != $value->reportingto_no){
            // changes in OM structure. record the movement

            $nubos = User::where('persno', $value->reportingto_no)->first();

            // create the history record
            $uth = new UserTeamHistory;
            $uth->user_id = $user->id;
            $uth->edited_by = 0;
            $uth->remark = 'SAP update';
            if($user->Boss){
              $uth->old_superior_id = $user->Boss->id;
            }

            if($nubos){
              $uth->new_superior_id = $nubos->id;
            } else {
              // somehow the new boss doesnt exist in trust at the moment
              $uth->new_superior_id = 0;
            }

            $uth->save();
          }

          $user->report_to = $value->reportingto_no;
          $user->status = 1;

          // find the division
          $unit = Unit::where('pporgunit', $gp_no)->first();
          if($unit){

          } else {
            $unit = new Unit;
            $unit->lob = 3000;
            $unit->pporgunit = $gp_no;
          }
          $grpname = isset($value->group_name) ? $value->group_name : $gp_no;

          $unit->pporgunitdesc = $grpname;
          $unit->save();
          $user->unit_id = $unit->id;

          // then the subdiv
          $subu = SubUnit::where('ppsuborg', $un_no)->first();
          if($subu){
          } else {
            $subu = new SubUnit;
            $subu->lob = 3000;
            $subu->ppsuborg = $un_no;
          }

          $unitname = isset($value->unit_name) ? $value->unit_name : $un_no;


          $subu->ppsuborgunitdesc = $unitname;
          $subu->pporgunit = $gp_no;
          $subu->pporgunitdesc = $grpname;
          $subu->save();

          $user->unit = $grpname;
          $user->subunit = $unitname;
          $user->jobtype = $value->postion_name;

          // new data from SAP (2021-03-18)
          $user->job_grade = $value->band;

          $user->save();
          $value->load_status = 'D';
          $value->save();



        } catch (\Exception $e) {
        
          $value->load_status = 'E';
          $value->save();
        }

      }
    }
}
