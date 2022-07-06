<?php
//anything that touch user table
namespace App\common;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\News;
use App\Models\Lovgp;
use App\Models\TaskCategory;
use App\Models\ActivityType;
use App\Models\PublicHoliday;
use App\Models\ActSubType;
use App\Models\UserStatHistory;
use App\Models\DailyUserStat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

use \Carbon\Carbon;

class UserHelper
{
    public static function HaveUnreadNews($staff_id){
      $ui = UserHelper::GetUserInfo($staff_id);

      $lastbaca = new Carbon();
      if(isset($ui->last_news_read)){
        $lastbaca = new Carbon($ui->last_news_read);
      } else {
        // set ke last baca tahun lepas
        $lastbaca->subYear();
      }

      // cari latest news
      $sekarang = new Carbon();
      $latestnews = News::latest()->first();
      if($latestnews){
        $sekarang = new Carbon($latestnews->created_at);
      }

      return $sekarang->gt($lastbaca);
    }

    public static function UpdateUnreadNews($staff_id){
      $ui = UserHelper::GetUserInfo($staff_id);
      $sekarang = new Carbon();
      $ui->last_news_read = $sekarang->toDateTimeString();
      $ui->save();
    }

    public static function GetUserGroup($staff_id){
      $user = User::find($staff_id);
      //dd($user->Division) ;
      if($user){

        return $user->Division->comp_group;
      } else {
        return NULL;
      }
    }

    public static function GetUserTaskCat($staff_id){
      $cgrp = UserHelper::GetUserGroup($staff_id);

      if($cgrp){

        return self::GetGroupTaskCat($cgrp);
      } else {
        // user not found, or not in any group. return default
        return self::GetUserTaskCatDefault();
      }
    }

    public static function GetGroupTaskCat($cgrp){
      $nom = [];
      $idlist = [];
      $counter = 0; //dd($cgrp);
      foreach($cgrp->Lovgps as $glov){
        $counter++;
        foreach($glov->taskcats as $cat){
          if(in_array($cat->id, $idlist)){
            continue;
          }
          array_push($nom, $cat);
          array_push($idlist, $cat->id);
        }
      }

      if($counter == 0){
        // group with no LOV assigned. use default
        return self::GetUserTaskCatDefault();
      }

      return $nom;
    }

    public static function GetUserTaskCatDefault(){
      $def = Lovgp::where('name', 'Default')->first();
      if($def){
        $nom = [];
        $idlist = [];
        foreach($def->taskcats as $cat){
          if(in_array($cat->id, $idlist)){
            continue;
          }
          array_push($nom, $cat);
          array_push($idlist, $cat->id);
        }
        return $nom;
      } else {
        // default not set. return everything
        return TaskCategory::where('status', '!=', 0)->get();
      }
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
            $data['trust_df'] = DiaryHelper::GetDailyPerfObj($staff->id, $date)->toArray();
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
      $neodata = IopHandler::GetNeoUsp($staff->persno, $date);


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
        \Illuminate\Support\Facades\Log::error('GetUserWorkTime ' . $staff->persno . ' for date ' . $date);
        \Illuminate\Support\Facades\Log::error($e);
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
          'trust_df' => DiaryHelper::GetDailyPerfObj($staff->id, $date)->toArray()
        ];
      }

      return $fildata;
    }


    public static function updateDfStartWorkTime($staff_id, $time){
      // get that day's df
      $stime = new Carbon($time);
      $df = DiaryHelper::GetDailyPerfObj($staff_id, $stime->toDateString());

      if(isset($df->start_work)){
        $earliesttime = new Carbon($df->start_work);
      } else {
        // most likely old data. default to 8 am
        $earliesttime = new Carbon($df->record_date . ' 08:00:00');
      }

      // then overwrite if the given time is earlier
      if($stime->lt($earliesttime)){
        $df->start_work = $stime->toDateTimeString();
        $df->save();
      }

    }

    public static function GetSubTypes($actid, $grpid){
      $subt = ActSubType::where('activity_type_id', $actid)
        ->where('comp_group_id', $grpid)
        ->get();

      return $subt;
    }

    // this is the monthly stats
    public static function GetUserStat($date){
      // convert to carbon date
      $indate = new Carbon($date);
      $indate->firstOfMonth();

      // check if it's this month
      $nowmon = new Carbon;
      $nowmon->firstOfMonth();

      if($nowmon->gt($indate)){
        // past month. most likely already in the history table
        $hist = UserStatHistory::where('rec_month', $indate->toDateString())->first();
        if($hist){
          return [
            'date' => $hist->rec_month,
            'active_user' => $hist->active_user,
            'diary_user' => $hist->diary_user,
            'location_user' => $hist->location_user,
            'workspace_user' => $hist->workspace_user
          ];
        }
      }

      // if it reaches here, meaning it's for current month, or no previous record
      $sdate = $indate->copy()->startOfMonth()->toDateTimeString();
      $edate = $indate->copy()->endOfMonth()->toDateTimeString();

      $locc = \App\Models\Attendance::whereBetween('created_at', [$sdate, $edate])
        ->distinct()->count('user_id');

      $qrsc = \App\Models\SeatCheckin::whereBetween('created_at', [$sdate, $edate])
        ->distinct()->count('user_id');

      $gwdc = \App\Models\GwdActivity::whereBetween('created_at', [$sdate, $edate])
        ->distinct()->count('user_id');

      $locr = \App\Models\Attendance::whereBetween('created_at', [$sdate, $edate])->select('user_id');

      $qrsr = \App\Models\SeatCheckin::whereBetween('created_at', [$sdate, $edate])->select('user_id');

      $uusc = \App\Models\GwdActivity::whereBetween('created_at', [$sdate, $edate])->select('user_id')
        ->union($locr)->union($qrsr)->count();

      // store the data if it's previous month
      if($nowmon->gt($indate)){
        $hist = new UserStatHistory;
        $hist->rec_month = $sdate;
        $hist->active_user = $uusc;
        $hist->diary_user = $gwdc;
        $hist->location_user = $locc;
        $hist->workspace_user = $qrsc;
        $hist->save();

      }

      return [
        'date' => $sdate,
        'active_user' => $uusc,
        'diary_user' => $gwdc,
        'location_user' => $locc,
        'workspace_user' => $qrsc
      ];
    }

    public static function GetDurLobUserStat($startdate, $enddate, $lob_descr){
      if($lob_descr == null){
        $lob_descr = 'null';
      }

      $tomorrowend = new Carbon($enddate);
      $enddate = $tomorrowend->addDay()->toDateString();

      // return the cached value if exist
      $cached = Redis::get('GDLUS_' . $lob_descr . '_' . $startdate .'_'. $enddate);
      if(isset($cached)) {
        return json_decode($cached, true);
      }

      // get valid user list
      $que = User::query();
      $que->where('status', 1);
      if($lob_descr == 'null'){
        $que->whereNull('lob_descr');
      } else {
        $que->where('lob_descr', $lob_descr);
      }
      $userlist = $que->pluck('id');

      // user count
      $uc = $userlist->count();

      // location count
      // $lc = \App\Models\Attendance::whereDate('created_at', $date)
      //   ->whereIn('user_id', $userlist->toArray())
      //   ->distinct()->count('user_id');
      $lq = DB::table('attendances')
        ->whereBetween('attendances.created_at', [$startdate, $enddate])
        ->join('users', 'users.id', '=', 'attendances.user_id')
        ->where('users.status', 1)
        ->when($lob_descr, function($query) use ($lob_descr){
          if($lob_descr == 'null'){
            return $query->whereNull('users.lob_descr');
          } else {
            return $query->where('users.lob_descr', $lob_descr);
          }
        })->select('users.id');
      $lc = $lq->distinct()->count('users.id');

      // workspace count
      $wq = DB::table('seat_checkins')
        ->whereBetween('seat_checkins.created_at', [$startdate, $enddate])
        ->join('users', 'users.id', '=', 'seat_checkins.user_id')
        ->where('users.status', 1)
        ->when($lob_descr, function($query) use ($lob_descr){
          if($lob_descr == 'null'){
            return $query->whereNull('users.lob_descr');
          } else {
            return $query->where('users.lob_descr', $lob_descr);
          }
        })->select('users.id');
      $wc = $wq->distinct()->count('users.id');

      // diary count
      $dq = DB::table('gwd_activities')
        ->whereBetween('gwd_activities.created_at', [$startdate, $enddate])
        ->join('users', 'users.id', '=', 'gwd_activities.user_id')
        ->where('users.status', 1)
        ->when($lob_descr, function($query) use ($lob_descr){
          if($lob_descr == 'null'){
            return $query->whereNull('users.lob_descr');
          } else {
            return $query->where('users.lob_descr', $lob_descr);
          }
        })->select('users.id')->distinct();
      $dc = $dq->count('users.id');

      $uusc = $dq->union($wq)->union($lq)->count();

      $ret = [
        'user_count' => $uc,
        'location_count' => $lc,
        'workspace_count' => $wc,
        'diary_count' => $dc,
        'user_in_group' => 0,
        'unique_count' => $uusc
      ];

      // store in cache for 7 days
      Redis::set('GDLUS_' . $lob_descr . '_' . $startdate .'_'. $enddate, json_encode($ret), 'EX', 604800);

      return $ret;

    }

    public static function GetDailyUserStat($date){
      $rec = DailyUserStat::whereDate('record_date', $date)->get();

      return [
        'user_count' => $rec->sum('user_count'),
        'location_count' => $rec->sum('location_count'),
        'workspace_count' => $rec->sum('workspace_count'),
        'diary_count' => $rec->sum('diary_count'),
        'user_in_group' => $rec->sum('user_in_group'),
        'unique_count' => $rec->sum('unique_count')
      ];
    }

    public static function GetDailyLobUserStat($date, $lob_descr, $regen = false, $nogen = false){
      if($lob_descr == null){
        $lob_descr = 'null';
      }

      // skip existing for regen
      if($regen == false){
        // check if it already exist
        $rec = DailyUserStat::whereDate('record_date', $date)
          ->where('lob_descr', $lob_descr)->first();
        if($rec){
          return [
            'user_count' => $rec->user_count,
            'location_count' => $rec->location_count,
            'workspace_count' => $rec->workspace_count,
            'diary_count' => $rec->diary_count,
            'user_in_group' => $rec->user_in_group,
            'unique_count' => $rec->unique_count
          ];
        }

        // skip gen data
        if($nogen == true){
          return [
            'user_count' => 0,
            'location_count' => 0,
            'workspace_count' => 0,
            'diary_count' => 0,
            'user_in_group' => 0,
            'unique_count' => 0
          ];
        }
      }

      // if it reaches here, meaning no existing or regen

      // get valid user list
      $que = User::query();
      $que->where('status', 1);
      if($lob_descr == 'null'){
        $que->whereNull('lob_descr');
      } else {
        $que->where('lob_descr', $lob_descr);
      }
      $userlist = $que->pluck('id');

      // user count
      $uc = $userlist->count();

      // location count
      // $lc = \App\Models\Attendance::whereDate('created_at', $date)
      //   ->whereIn('user_id', $userlist->toArray())
      //   ->distinct()->count('user_id');
      $lq = DB::table('attendances')
        ->whereDate('attendances.created_at', $date)
        ->join('users', 'users.id', '=', 'attendances.user_id')
        ->where('users.status', 1)
        ->when($lob_descr, function($query) use ($lob_descr){
          if($lob_descr == 'null'){
            return $query->whereNull('users.lob_descr');
          } else {
            return $query->where('users.lob_descr', $lob_descr);
          }
        })->select('users.id')->distinct();
      $lc = $lq->count('users.id');

      // workspace count
      $wq = DB::table('seat_checkins')
        ->whereDate('seat_checkins.created_at', $date)
        ->join('users', 'users.id', '=', 'seat_checkins.user_id')
        ->where('users.status', 1)
        ->when($lob_descr, function($query) use ($lob_descr){
          if($lob_descr == 'null'){
            return $query->whereNull('users.lob_descr');
          } else {
            return $query->where('users.lob_descr', $lob_descr);
          }
        })->select('users.id')->distinct();
      $wc = $wq->count('users.id');

      // diary count
      $dq = DB::table('gwd_activities')
        ->whereDate('gwd_activities.created_at', $date)
        ->join('users', 'users.id', '=', 'gwd_activities.user_id')
        ->where('users.status', 1)
        ->when($lob_descr, function($query) use ($lob_descr){
          if($lob_descr == 'null'){
            return $query->whereNull('users.lob_descr');
          } else {
            return $query->where('users.lob_descr', $lob_descr);
          }
        })->select('users.id')->distinct();
      $dc = $dq->count('users.id');

      // unique user
      // $locr = \App\Models\Attendance::whereDate('created_at', $date)
      //   ->whereIn('user_id', $userlist->toArray())->select('user_id');
      //
      // $qrsr = \App\Models\SeatCheckin::whereDate('created_at', $date)
      //   ->whereIn('user_id', $userlist->toArray())->select('user_id');
      //
      // $uusc = \App\Models\GwdActivity::whereDate('created_at', $date)
      //   ->whereIn('user_id', $userlist->toArray())->select('user_id')
      //   ->union($locr)->union($qrsr)->count();

      $uusc = $dq->union($wq)->union($lq)->count();

      // create or update the record
      $rec = DailyUserStat::updateOrCreate([
        'record_date' => $date,
        'lob_descr' => $lob_descr
      ], [
        'user_count' => $uc,
        'location_count' => $lc,
        'workspace_count' => $wc,
        'diary_count' => $dc,
        'user_in_group' => 0,
        'unique_count' => $uusc
      ]);

      return [
        'user_count' => $rec->user_count,
        'location_count' => $rec->location_count,
        'workspace_count' => $rec->workspace_count,
        'diary_count' => $rec->diary_count,
        'user_in_group' => $rec->user_in_group,
        'unique_count' => $rec->unique_count
      ];

    }


    // ----------------------
    public static function GetUserInfo($staff_id){
      $ui = UserInfo::where('user_id', $staff_id)->first();
      if($ui){

      } else {
        $ui = new UserInfo;
        $ui->user_id = $staff_id;
        $ui->save();

      }

      return $ui;
    }

    private static function DefaultGetUserWorkTime($staff, $date){
      $carbond = new Carbon($date);
      $dow = $carbond->dayOfWeekIso;

      if($dow == 5){
        return $staff->Division ? $staff->Division->friday_hours : 7.5;
      } elseif($dow > 5){
        return 0;
      } else {
        return 8;
      }
    }
}
