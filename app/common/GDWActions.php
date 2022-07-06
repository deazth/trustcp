<?php

namespace App\common;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Unit;
use App\ActSubType;
use App\GwdActivity;
use \Carbon\Carbon;
use App\Models\ActivityType;
use App\Models\TaskCategory;
use App\Models\Avatar;
use App\Models\DailyPerformance;
use App\Models\PublicHoliday;
use App\Models\CommonConfig;
use App\Models\LocationHistory;
use \DB;

class GDWActions
{

  const DT_NW = 0; // normal working day
  const DT_WK = 1; // weekend
  const DT_PH = 2; // public holiday


  public static function addActivity(Request $req, $staff_id){

    if($req->filled('actdate')){
      $indate = $req->actdate;
      $today = new Carbon(date('Y-m-d'));
      $inpd = new Carbon($indate);
      if($inpd->gt($today)){
        return 'future date';
      }
    } else {
      $indate = date('Y-m-d');
    }
    $curruser = User::find($staff_id);
    $dp = GDWActions::GetDailyPerfObj($staff_id, $indate);

    if(GDWActions::canAcceptThisAct($dp, $req->hours)){
      // allow changes
    } else {
      // reject changes
      return '402';
    }


    $act = new GwdActivity;
    $act->user_id = $staff_id;

    // get the TaskCategory
    $taskcat = TaskCategory::where('descr', $req->actcat)->orderBy('status', 'DESC')->orderBy('created_at', 'DESC')->first();
    if($taskcat){
      $taskcat_id = $taskcat->id;
    } else {
      $taskcat_id = 12;
    }

    // get the activity type
    $actttype = ActivityType::where('descr', $req->acttype)->orderBy('status', 'DESC')->orderBy('created_at', 'DESC')->first();
    if($actttype){
      $actttype_id = $actttype->id;
    } else {
      $actttype_id = 10;
    }

    $act->activity_type_id = $actttype_id;
    $act->task_category_id = $taskcat_id;
    // $act->title = $req->title;

    $act->hours_spent = $req->hours;

    // optionals
    if($req->filled('title')){
      $act->title = $req->title;
    } else {
      $act->title = 'd';
    }
    // dd($req);
    if($req->filled('parent_no')){
      $act->parent_number = $req->parent_no;
    }

    if($req->filled('details')){
      $act->details = $req->details;
    }
    if($req->filled('tribe_id')){
      $act->tribe_id = $req->tribe_id;
    }
    if($req->filled('subtype')){
      $gp = $curruser->Division->Group;
      if($gp){
        $subtype = ActSubType::where('comp_group_id', $gp->id)
          ->where('activity_type_id', $actttype_id)
          ->where('descr', $req->subtype)->first();
        if($subtype){
          $act->act_sub_type_id = $subtype->id;
        }

      }
    }

    if($req->filled('subtype_id')){
      $act->act_sub_type_id = $req->subtype_id;
    }


    $act->activity_date = $indate;
    $user = $act->User;

    if($user->isvendor == 1){
      $act->partner_id = $user->partner_id;
    } else {
      $act->unit_id = $user->unit_id;
    }

    // get current checkin as well
    $act->checkin_id = $user->curr_checkin;

    // tie to daily perf
    $act->daily_performance_id = $dp->id;
    $act->save();

    // update the daily perf hours
    $dp->recalcHours();

    GDWActions::updateAvatar($staff_id);

    return $act;

  }

  public static function editActivity(Request $req){

    $act = GwdActivity::find($req->id);
    if(!$act){
      return "404";
    }

    $currdp = $act->DailyPerf;

    $timediff = $req->hours - $act->hours_spent;
    if($timediff > 0){
      if(GDWActions::canAcceptThisAct($currdp, $timediff)){
        // allow changes
      } else {
        // reject changes
        return '402';
      }
    }

    $act->hours_spent = $req->hours;
    $act->save();

    if($req->filled('actdate')){
      $act->activity_date = $req->actdate;

      // day changed
      $nudp = GDWActions::GetDailyPerfObj($act->user_id, $req->actdate);
      $nudp->addHours($act->hours_spent);
      $act->daily_performance_id = $nudp->id;

      // reduce the hours from the old daily perf
      $currdp->addHours(0 - $act->hours_spent);

    } else {
      // get the diff in hours
      $hoursdiff = $req->hours - $act->hours_spent;
      $currdp->addHours($hoursdiff);
    }

    // optionals
    if($req->filled('title')){
      $act->title = $req->title;
    }

    if($req->filled('parent_no')){
      $act->parent_number = $req->parent_no;
    }

    if($req->filled('details')){
      $act->details = $req->details;
    }

    $act->save();

    GDWActions::updateAvatar($act->user_id);

    return $act;

  }

  public static function canAcceptThisAct($dailyperf, $hourstoadd){

    // double check kat config
    $confc = CommonConfig::where('key', 'reinforce_diary')->first();
    if($confc){
      if($confc->value != 'true'){
        // no need to check. just return true
        return true;
      }
    } else {
      $confc = new CommonConfig;
      $confc->key = 'reinforce_diary';
      $confc->value = 'true';
      $confc->save();
    }

    // check ada check in rekod tak untuk hari ni
    $earliestime = GDWActions::GetStartWorkTime($dailyperf->user_id, $dailyperf->record_date);

    // dd(date('Y-m-d'));

    // current time
    $ctime = new Carbon;
    $cdate = new Carbon(date('Y-m-d'));

    // check if this is for previous days
    $qdate = new Carbon($dailyperf->record_date);
    if($qdate->gt($cdate)){
      // future date. reject
      return false;
    }

    if($qdate->lt($cdate)){
      // past days. set max hour to 24
      $maxhrs = 24;
    } else {
      $maxhrs = $ctime->hour;
    }

    // get max number of hours allowed at this time
    $maxhrs = $maxhrs - $earliestime->hour + 1;  // plus 1 hour as allowed buffer

    // return true if the added hours will not exceed allowed hours
    return $maxhrs >= $dailyperf->actual_hours + $hourstoadd;
  }

  public static function GetStartWorkTime($user_id, $date){
    /*
    $cekin = LocationHistory::where('user_id', $user_id)
      ->whereDate('created_at', $date)
      ->where('action', '!=', 'Check-out')
      ->orderBy('created_at', 'ASC')->first();

    if($cekin){
      $earliestime = new Carbon($cekin->created_at);
    } else {
      $earliestime = new Carbon($date);
      $earliestime->hour = 8;
    }

    // if checkin after 8, assume start time at 8
    $startlapan = new Carbon($date);
    $startlapan->hour = 8;

    if($earliestime->gt($startlapan)){
      $earliestime = $startlapan;
    }
    */

    $df = self::GetDailyPerfObj($user_id, $date);
    if(isset($df->start_work)){
      $earliestime = new Carbon($df->start_work);
    } else {
      $earliestime = new Carbon($date);
      $earliestime->hour = 8;
    }

    return $earliestime;
  }

  public static function deleteActivity($activityid){
    $act = GwdActivity::find($activityid);
    if(!$act){
      return "404";
    }
    $staffid = $act->user_id;
    $currdp = $act->DailyPerf;
    $act->delete();
    // take out the hours from daily perf
    $currdp->recalcHours();
    // $currdp->addHours(0 - $act->hours_spent);

    GDWActions::updateAvatar($staffid);

    return "deleted";
  }

  public static function updateAvatar($staff_id){
    $curdate = date('Y-m-d');

    $curhours = GwdActivity::where('user_id', $staff_id)
      ->whereDate('activity_date', $curdate)
      ->sum('hours_spent')
      // ->get()
      ;


    $av = Avatar::where('min_hours', '<=', $curhours)
      ->where('max_hours', '>=', $curhours)
      ->orderBy('max_hours', 'DESC')
      ->first();

    $rank = 0;
    if($av){
      $rank = $av->rank;
    }

    $user = User::find($staff_id);
    $user->avatar_rank = $rank;
    $user->save();
    $avta = $user->Avatar ;
    $avta->curr_total_hours = $curhours;
    return $avta;
  }

  public static function getActSummary($staff_id, $date){
    // get the month range from the given date
    $cdate = Carbon::parse($date);
    $monmon = $cdate->format('F Y');
    $sdate = $cdate->startOfMonth()->toDateString();
    $edate = $cdate->addMonths(1)->toDateString();
    $label = [];
    $data = [];
    $bgs = [];


    // get the list of act type
    $actype = ActivityType::where('status', 1)->get();
    $counter = rand(0, 12);
    foreach ($actype as $key => $value) {
      $counter++;
      array_push($label, $value->descr);
      array_push($bgs, GDWActions::getBgColor($counter));
      // get the sum of hours for this activity type
      $hrs = GwdActivity::where('user_id', $staff_id)
        ->where('activity_type_id', $value->id)
        ->whereDate('activity_date', '>=', $sdate)
        ->whereDate('activity_date', '<', $edate)
        ->sum('hours_spent');
      array_push($data, $hrs);
    }

    $info = [
      'label' => $label,
      'data' => $data,
      'bg' => $bgs,
      'month' => $monmon
    ];

    // dd($info);
    return $info;

  }

  public static function getActInfoOnDate($staff_id, $date, $startrnd){
    $label = [];
    $data = [];
    $bgs = [];
    // get the list of act type
    $actype = ActivityType::where('status', 1)->get();
    $counter = $startrnd;
    foreach ($actype as $key => $value) {
      $counter++;
      array_push($label, $value->descr);
      array_push($bgs, GDWActions::getBgColor($counter));
      // get the sum of hours for this activity type
      $hrs = GwdActivity::where('user_id', $staff_id)
        ->where('activity_type_id', $value->id)
        ->whereDate('activity_date', '=', $date)
        ->sum('hours_spent');
      array_push($data, $hrs);
    }

    $info = [
      'label' => $label,
      'data' => $data,
      'bg' => $bgs
    ];

    // dd($info);
    return $info;

  }

  public static function getMonthlyCal($staff_id, $date){
    $cdate = Carbon::parse($date);
    $sdate = $cdate->startOfMonth()->toDateString();
    $edate = $cdate->addMonths(1)->toDateString();

    $dailyperf = DailyPerformance::where('user_id', $staff_id)
      ->whereDate('record_date', '>=', $sdate)
      ->whereDate('record_date', '<', $edate)
      ->where('actual_hours', '>', 0)
      ->get();

    $retval = [];

    foreach ($dailyperf as $key => $value) {
      if($value->expected_hours == 0){
        $dotcol = 'red';
      } else {
        $perc = $value->actual_hours / $value->expected_hours * 100;
        if($perc < 50){
          $dotcol = 'yellow';
        } elseif($perc < 70){
          $dotcol = 'blue';
        } elseif($perc <= 100){
          $dotcol = 'green';
        } else {
          $dotcol = 'red';
        }
      }

      $retval[$value->record_date] = [
        'marked' => true,
        'dotColor' => $dotcol
      ];
    }

    return $retval;

  }

  public static function getGwdActivities($staff_id, $date = null){
    // $cdate = Carbon::parse($date);
    // $sdate = $cdate->startOfMonth()->toDateString();
    // $edate = $cdate->addMonths(1)->toDateString();
    //
    // $actlist = GwdActivity::where('user_id', $staff_id)
    //   ->whereDate('activity_date', '>=', $sdate)
    //   ->whereDate('activity_date', '<', $edate)
    //   ->get();

    // $cconfig = CommonConfig::where('key', 'diary_act_list_size')->first();
    // if($cconfig){
    //
    // } else {
    //   $cconfig = new CommonConfig;
    //   $cconfig->key = 'diary_act_list_size';
    //   $cconfig->value = 50;
    //   $cconfig->save();
    // }


    // $actlist = GwdActivity::where('user_id', $staff_id)
    //   ->orderBy('activity_date', 'DESC')
    //   ->take($cconfig->value)
    //   ->get();

    $actlist = GwdActivity::where('user_id', $staff_id)
      ->whereDate('activity_date', $date)
      ->get();

    foreach ($actlist as $value) {
      $value->acts_type = $value->ActType->descr;
      $value->acts_cat = $value->ActCat->descr;

      if ($value->ActSubType) {
          $value->acts_subtype = $value->ActSubType->descr;
      } else {$value->acts_subtype = null;}

    }

    return $actlist;
  }

  public static function getGroupSummary($unitdiv_id){
    $retd = [];
    $label = [];
    $tfillp = [];
    $tperfp = [];
    $yfillp = [];
    $yperfp = [];

    $yest = date('Y-m-d',strtotime("-1 days"));
    $tod = date('Y-m-d');
    // get the group this div belongs to
    $cdiv = Unit::find($unitdiv_id);

    if($cdiv){
      $dagup = $cdiv->Group;
      $grplist = [];
      if($dagup){
        $grplist = $dagup->Members;
      } else {
        array_push($grplist, $cdiv);
      }


      // get staff count
      foreach ($grplist as $key => $value) {
        $scount = $value->Staffs->count();

        if($scount > 0){
          array_push($label, $value->pporgunitdesc);
          // get yesterday
          $dps = $value->PerfEntryOnDateRange($yest, $yest);
          $actual = $dps->sum('actual_hours');
          $expct = $dps->sum('expected_hours');
          if($expct == 0){
            $perf = $actual > 0 ? 120 : 0;
          } else {
            $perf = $actual / $expct * 100;
          }
          array_push($yperfp, $perf);
          array_push($yfillp, $dps->where('actual_hours', '!=', 0)->count() / $scount * 100);

          // then today
          $dps = $value->PerfEntryOnDateRange($tod, $tod);
          $actual = $dps->sum('actual_hours');
          $expct = $dps->sum('expected_hours');
          if($expct == 0){
            $perf = $actual > 0 ? 120 : 0;
          } else {
            $perf = $actual / $expct * 100;
          }
          array_push($tperfp, $perf);
          array_push($tfillp, $dps->where('actual_hours', '!=', 0)->count() / $scount * 100);
        }

      }

      $retd = [
        'label' => $label,
        'yesterday_count' => $yfillp,
        'yesterday_perf' => $yperfp,
        'today_count' => $tfillp,
        'today_perf' => $tperfp
      ];

    }

    return $retd;
  }




  public static function getBgColor($i){
    $bgcolors = [
      'rgba(255, 99, 132, 0.5)',
      'rgba(255, 150, 5, 0.5)',
      'rgba(0, 255, 132, 0.5)',
      'rgba(0, 0, 255, 0.5)',
      'rgba(255, 0, 0, 0.7)',
      'rgba(0, 0, 0, 0.2)',
      'rgba(14, 170, 132, 0.5)',
      'rgba(108, 68, 229, 0.7)',
      'rgba(215, 215, 44, 0.8)',
      'rgba(255, 0, 255, 0.8)',
      'rgba(94, 38, 6, 0.7)',
    ];

    return $bgcolors[$i % count($bgcolors)];
  }

  public static function setOnLeave($staff_id, $date, $remark){

    // find if there is any existing leave on that day
    $act = GwdActivity::where('user_id', $staff_id)
      ->whereDate('activity_date', $date)
      ->where('isleave', true)
      ->first();

    if($act){

    } else {
      $act = new GwdActivity;
      $act->user_id = $staff_id;
      $act->isleave = true;
      $act->activity_date = $date;
      $act->activity_type_id = 0;
      $act->title = 'cuti';
      $act->hours_spent = 0;

      $user = $act->User;

      if($user->isvendor == 1){
        $act->partner_id = $user->partner_id;
      } else {
        $act->unit_id = $user->unit_id;
      }

    }

    $act->leave_remark = $remark;
    $act->save();

    return $act;

  }

  public static function GetDayType($date){
    $ph = PublicHoliday::whereDate('event_date', $date)->first();

    if($ph){
      return self::DT_PH;
    }

    // not a public holiday. check day of the week
    $carbond = new Carbon($date);
    $dow = $carbond->dayOfWeekIso;

    if($dow > 5){
      return self::DT_WK;
    } else {
      return self::DT_NW;
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

  public static function CalcPerfPerc($exp, $act, $daycount = 1){
    $ret = 0;
    if($exp == 0){
      if($act == 0){
        $ret = 100;
      } else {
        $ret = (1 + $act / ($daycount * 8)) * 100;
      }
    } else {
      if($act > $exp){
        $rem = $act - $exp;
        $ret = (1 + $rem / ($daycount * 8)) * 100;
      } else {
        $ret = $act / $exp * 100;
      }
    }

    return intval($ret);
  }

  public static function GetDailyPerfObj($user_id, $paramdate, $notreport = true, $resetexpected = false){

    $cbdate = new Carbon;
    if(isset($paramdate)){
      $cbdate = new Carbon($paramdate);
    }

    $date = $cbdate->toDateString();

    $user = User::find($user_id);

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
        $neodata = UserHelper::GetUserWorkTime($user, $date);

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
      $dp->unit_id = $user->unit_id;

      if(Utilities::GetCommonConfig('use_neo_wsr', 'false') == 'true'){
        // use data from neo
        $neodata = UserHelper::GetUserWorkTime($user, $date);
        $dp->expected_hours = $neodata['expected_hours'];
        $dp->start_work = $neodata['start_time'];
        $dp->remark = $neodata['day_remark'];
        if($neodata['is_ph'] != false){
          $dp->is_public_holiday = true;
          $dp->public_holiday_id = $neodata['is_ph'];
        }
      } else {
        // use old method instead
        $friday = $user->Division ? $user->Division->friday_hours : 8;
        $dp->expected_hours = GDWActions::GetExpectedHours($date, null, null, $friday);
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

  public static function GetSubordsPerf($user_id){
    $today = new Carbon();
    $yesturday = new Carbon();
    $yesturday->subDay();
    $retval = [];

    // get the list of subs
    $curuser = User::find($user_id);
    if($curuser && isset($curuser->persno)){
      $subs = User::where('report_to', $curuser->persno)->get();
      foreach($subs as $ones){
        // skip for inactive users
        if($ones->status == 0){
          continue;
        }

        $ydpf = GDWActions::GetDailyPerfObj($ones->id, $yesturday);
        $tdpf = GDWActions::GetDailyPerfObj($ones->id, $today);
        $retval[] = [
          'staff_id' => $ones->id,
          'staff_no' => $ones->staff_no,
          'name' => $ones->name,
          'status' => $ones->status,
          'position' => $ones->position,
          'yesterday_exp' => $ydpf->expected_hours,
          'yesterday_act' => $ydpf->actual_hours,
          'today_exp' => $tdpf->expected_hours,
          'today_act' => $tdpf->actual_hours
        ];
      }
    }

    return $retval;
  }

  // get 7 days worth of data
  public static function GetStaffRecentPerf($user_id, $daterange){


    $retarr = [];

    // dd($daterange);

    foreach($daterange as $indate){
      $tdf = GDWActions::GetDailyPerfObj($user_id, $indate);
      // if($tdf->expected_hours == 0){
      //   // $perc = $tdf->actual_hours > 0 ? 120 : 100;
      //   $perc = 100 + ($tdf->actual_hours / 8 * 100);
      // } else {
      //   $calcperf = $tdf->actual_hours / $tdf->expected_hours * 100;
      //   $perc = intval($calcperf);
      // }

      array_push($retarr, [
        'date' => $indate->toDateString(),
        'actual' => $tdf->actual_hours,
        'expected' => $tdf->expected_hours,
        // 'perc' => $perc,
        'perc' => $tdf->performance,
        'isonleave' => $tdf->is_off_day
      ]);
    }

    return $retarr;
  }

  public static function GetStaffAvgPerf($user_id, $fromdate, $todate){

    $daydiff = $todate->diff($fromdate)->days + 1;

    $dfobjs = DailyPerformance::where('user_id', $user_id)
      ->whereDate('record_date', '>=', $fromdate)
      ->whereDate('record_date', '<=', $todate)
      ->get();

    $actotal = $dfobjs->sum('actual_hours');
    $expotal = $dfobjs->sum('expected_hours');
    if($expotal == 0){
      $perc = $actotal > 0 ? 100 + ($actotal / (8 * $daydiff) * 100) : 100;
    } else {
      $perc = $actotal / $expotal * 100;
    }

    return [
      'actual' => $actotal,
      'expected' => $expotal,
      'perc' => round($perc, 2)
    ];
  }

}
