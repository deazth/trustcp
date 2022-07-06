<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Carbon\Carbon;
use App\Models\DailyPerformance;

class DiaryAdminController extends Controller
{
  public function __construct()
  {
    $this->middleware(['permission:diary-admin']);
  }

  public function showResetPage(Request $req){
    // abort(505);
    $this->data['title'] = 'Diary Bulk Reset';
    $this->data['result'] = false;
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'Diary Bulk Reset' => false
    ];

    $today = new Carbon;
    $indate = new Carbon;
    if($req->filled('indate')){
      $indate = new Carbon($req->indate);
      if($indate->gt($today)){
        \Alert::warning('Future date is not allowed')->flash();
        return redirect()->back();
      } else {
        // fetch the summary for the given date
        $grps = DailyPerformance::where('record_date', $indate->toDateString())
          ->groupBy('remark')->select('remark', \DB::raw('count(*) as rcount'))->get();

        $this->data['result'] = $grps;
      }
    } else {
      $indate = $today;
    }

    $this->data['indate'] = $indate->toDateString();
    $this->data['maxdate'] = $today->toDateString();

    return view('diary.bulkreset', $this->data);
  }

  public function doBulkReset(Request $req){
    // dd($req->all());
    set_time_limit(0);
    $today = new Carbon;
    $indate = new Carbon;
    if($req->filled('indate')){
      $indate = new Carbon($req->indate);
      if($indate->gt($today)){
        \Alert::warning('Future date is not allowed')->flash();
        return redirect()->back();
      } else {
        $rem = $req->remarks ?? [];
        foreach ($rem as $remak) {
          if($remak == null){
            // handle the null remark first
            $grps = DailyPerformance::where('record_date', $indate->toDateString())
              ->whereNull('remark')->get();
            foreach ($grps as $key => $value) {
              \App\common\GDWActions::GetDailyPerfObj($value->user_id, $value->record_date, true, true);
            }
          }
        }
        // get the list of DP to be reset
        $grps = DailyPerformance::where('record_date', $indate->toDateString())
          ->whereIn('remark', $req->remarks)->get();
        foreach ($grps as $key => $value) {
          \App\common\GDWActions::GetDailyPerfObj($value->user_id, $value->record_date, true, true);
        }

      }
    } else {
      \Alert::error('Input date required')->flash();
      return redirect()->back();
    }

    return redirect()->route('diaryadmin.bulkreset', ['indate' => $req->indate]);
  }
}
