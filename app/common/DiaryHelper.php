<?php

namespace App\common;

use App\Models\TaskCategory;
use App\Models\ActivityType;
use App\Models\CompGroup;
use App\Models\User;
use App\Models\ActSubType;
use App\Models\DailyPerformance;
use App\Models\PublicHoliday;
use App\Models\LocationHistory;
use \Carbon\Carbon;

class DiaryHelper
{


  public static function GetActTag($staff_id){
    $task = TaskCategory::where('status', 1)->get()->pluck('descr', 'id');

    return $task;
  }

  public static function GetActType($act_tag, $filter = false){
    $tack = TaskCategory::find($act_tag);
    if($tack){
      $types = ActivityType::query();
      $types->whereIn('id', $tack->ActivityTypes->pluck('id'));
      $types->where('status', 1);

      if($filter != false && $filter != ''){
        $types->where('descr', 'LIKE', '%' . $filter . '%');
      }

      return $types;
    }

    return false;
  }

  // get the list of act tags for the given group
  public static function GetGrpActType($gid){
    // first get the LOVs
    $gp = CompGroup::findOrFail($gid);
    $nom = [];
    $idlist = [];
    $counter = 0;
    foreach($gp->Lovgps as $glov){
      $counter++;
      foreach($glov->taskcats as $cat){
        if($cat->status != 1){
          continue;
        }
        foreach($cat->ActivityTypes as $typ){
          if(in_array($typ->id, $idlist)){
            continue;
          }

          if($typ->status != 1){
            continue;
          }
          // array_push($nom, $cat);
          array_push($idlist, $typ->id);
        }
      }
    }

    return $idlist;
  }

  public static function GetGrpActSubType($user_id, $act_type_id, $filter = false){
    // get the user's group
    $user = User::find($user_id);
    if($user){
      $grp = $user->Unit->comp_group;
      if($grp){
        $ast = ActSubType::query();
        $ast->where('activity_type_id', $act_type_id)
          ->where('comp_group_id', $grp->id);

        if($filter != false && $filter != ''){
          $ast->where('descr', 'LIKE', '%' . $filter . '%');
        }
        return $ast;
      }
    }

    return false;
  }

  public static function GetDailyPerfObj($user_id, $paramdate, $notreport = true, $resetexpected = false){

    $cbdate = new Carbon;
    if(isset($paramdate)){
      $cbdate = new Carbon($paramdate);
    }

    $date = $cbdate->toDateString();

    $user = User::find($user_id);

    // dd($user);

    $dp = DailyPerformance::where('user_id', $user_id)
      ->whereDate('record_date', $date)
      ->first();

    if($dp){
      // remote possible duplicates
      if($notreport){
        $dbd = DailyPerformance::where('user_id', $user_id)
          ->whereDate('record_date', $date)
          ->where('id', '!=', $dp->id)
          ->delete();
      }

      if($resetexpected){
        // reset the expected info
        // pull info from neo
        $neodata = self::GetUserWorkTime($user, $date);

        // get earliest checkIn
        $cekin = LocationHistory::where('user_id', $user_id)
          ->whereDate('created_at', $date)
          ->where('action', '!=', 'Check-out')
          ->orderBy('created_at', 'ASC')->first();

        if($cekin){
          $earliestime = new Carbon($cekin->created_at);
          $neostime = new Carbon($neodata['start_time']);
          if($earliestime->lt($neostime)){
            $dp->start_work = $earliestime->toDateTimeString();
          } else {
            $dp->start_work = $neodata['start_time'];
          }
        } else {
          // no previous checkins. use default from neo
          $dp->start_work = $neodata['start_time'];
        }


        // then overwrite related fields
        $dp->expected_hours = $neodata['expected_hours'];
        $dp->remark = $neodata['day_remark'];
        if($neodata['is_ph'] != false){
          $dp->is_public_holiday = true;
          $dp->public_holiday_id = $neodata['is_ph'];
        } else {
          $dp->is_public_holiday = false;
          $dp->public_holiday_id = null;
        }

        // just in case, check for planned leave
        if($dp->is_off_day){
          $leave_exp = $dp->LeaveType->hours_value;
          // if leave expected hours is less than 6 (half day or AL-type)
          // , use it instead of default from NEO
          if($leave_exp < 6){
            $dp->expected_hours = $leave_exp;
          }
        }

        $dp->save();
      }

    } else {
      // dont create new for inactive user
      if($user->status == 0){
        return null;
      }
      // no record. create new
      $dp = new DailyPerformance;
      $dp->user_id = $user_id;
      $dp->record_date = $date;
      $dp->unit_id = $user->lob;

      if(CommonHelper::GetCConfig('use_neo_wsr', 'false') == 'true'){
        // use data from neo
        $neodata = self::GetUserWorkTime($user, $date);
        $dp->expected_hours = $neodata['expected_hours'];
        $dp->start_work = $neodata['start_time'];
        $dp->remark = $neodata['day_remark'];
        if($neodata['is_ph'] != false){
          $dp->is_public_holiday = true;
          $dp->public_holiday_id = $neodata['is_ph'];
        }
      } else {
        // use old method instead
        $dp->expected_hours = self::GetExpectedHours($date, null, null, 7.5);
        $today = new Carbon($date);
        $today->hour = 8;
        $today->minute = 0;
        $today->second = 0;
        $dp->start_work = $today->toDateTimeString();
      }


      $dp->save();
    }

    if($notreport){
      $dp->recalcHours();
    }

    return $dp;
  }

  public static function GetUserWorkTime($staff, $date, $getraw = false){
      // default start time at 8
      $today = new \Carbon\Carbon($date);
      $today->hour = 8;
      $today->minute = 0;
      $today->second = 0;

      // if it's leasing staff, get the supervisor instead
      if(substr($staff->staff_no, 0, 1) == 'X'){
        if(isset($staff->report_to) && $staff->report_to != 0){
          $data = self::GetUserWorkTime($staff->Boss, $date, $getraw);
          if($getraw  == true){
            $data['trust_df'] = self::GetDailyPerfObj($staff->id, $date)->toArray();
          }
        } else {
          // just in case, for leasing without registered SP
          $worktime = self::DefaultGetUserWorkTime($staff, $date);

          $data = [
            'is_working' => $worktime > 0,
            'expected_hours' => $worktime,
            'day_remark' => 'Default No SP',
            'start_time' => $today->toDateTimeString(),
            'is_ph' => false
          ];

        }

        return $data;
      }

      // not leasing, then fetch data from NEO
      // \Illuminate\Support\Facades\Log::info('calling neo for  ' . $staff->persno . ' for date ' . $date);
      $neodata = IopHandler::GetNeoUsp($staff->persno, $date);
      // \Illuminate\Support\Facades\Log::info('neo response: ' . json_encode($neodata));


      // just fetch important data, and maybe populate
      $goterror = false;

      $fildata = [];

      try{
        $workhour = 0;
        $isph = false;
        // check if it's a working day
        $isworking = $neodata->check->is_work_day == 1;

        if($isworking){
          // get the work hour

          $workhour = isset($neodata->check->expected_hour) ? $neodata->check->expected_hour : 8;
          // get the start work time
          $swt = explode(':', $neodata->daytype->start_time);
          $today->hour = $swt[0];
          $today->minute = $swt[1];

          $minstart = new \Carbon\Carbon($date);
          $minstart->hour = 16;
          $minstart->minute = 0;
          $minstart->second = 0;

          if($today->gt($minstart)){
            // night shift. set start time early
            $today->hour = 0;
            $today->minute = 0;
          }


        } else {
          // check if it's public holiday
          if($neodata->check->day_code == 'PH' && $neodata->hol){
            // check if the ph already exist in DB
            $ph = PublicHoliday::whereDate('event_date', $date)
              ->where('name', $neodata->hol->descr)->first();
            if($ph){
              $isph = $ph->id;
            } else {
              // create the ph entry in DB
              $ph = new PublicHoliday;
              $ph->event_date = $date;
              $ph->name = $neodata->hol->descr;
              $ph->created_by = 1; // default to amer lol
              $ph->save();
              $isph = $ph->id;
            }
          }
        }

        $fildata = [
          'is_working' => $isworking,
          'expected_hours' => $workhour,
          'day_remark' => $neodata->check->day_descr,
          'start_time' => $today->toDateTimeString(),
          'is_ph' => $isph
        ];
      } catch(\Exception $e){
        $goterror = $e;
      }

      if($goterror){
        // hit error along the way. use default way to get the expected hours instead
        $worktime = self::DefaultGetUserWorkTime($staff, $date);

        $fildata = [
          'is_working' => $worktime > 0,
          'expected_hours' => $worktime,
          'day_remark' => 'Default',
          'start_time' => $today->toDateTimeString(),
          'is_ph' => false,
          'goterror' => $goterror->getMessage()
        ];
      }

      if($getraw  == true){
        return [
          'neo_summary' => $fildata,
          'neo_full' => $neodata,
          'trust_df' => self::GetDailyPerfObj($staff->id, $date)->toArray()
        ];
      }

      return $fildata;
    }

    private static function DefaultGetUserWorkTime($staff, $date){
      $carbond = new Carbon($date);
      $dow = $carbond->dayOfWeekIso;

      if($dow == 5){
        return $staff->Division->friday_hours;
      } elseif($dow > 5){
        return 0;
      } else {
        return 8;
      }
    }

    public static function GetExpectedHours($date, DailyPerformance $dpp = null, $exclude = null, $fridayhours = 7.5){
      // first, check if it's a public holiday
      if($exclude){
        $ph = PublicHoliday::whereDate('event_date', $date)
          ->where('id', '!=', $exclude)->first();
      } else {
        $ph = PublicHoliday::whereDate('event_date', $date)->first();
      }


      if($ph){

        if($dpp){
          $dpp->is_public_holiday = true;
          $dpp->public_holiday_id = $ph->id;
        }

        return 0;
      }

      // not a public holiday. check day of the week
      $carbond = new Carbon($date);
      $dow = $carbond->dayOfWeekIso;

      if($dow == 5){
        return $fridayhours;
      } elseif($dow > 5){
        return 0;
      } else {
        return 8;
      }

    }

}
