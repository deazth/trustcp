<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyPerformance;
use App\Models\User;
use App\Models\PublicHoliday;
use App\Models\StaffLeave;
use Backpack\CRUD\app\Library\Widget;
use App\common\CommonHelper;
use App\common\DiaryHelper;
use \Carbon\Carbon;
use App\common\GDWActions;
use Backpack\CRUD\app\Http\Controllers\ChartController;
use App\Http\Controllers\Admin\Charts\DiaryChartController;
use Illuminate\Support\Facades\Log;

class DiaryController extends Controller
{
  /**
    show the calendar, summary graph, etc
   */
  public function overview(Request $req)
  {
    $this->data['title'] = 'Diary Overview';
    $this->data['breadcrumbs'] = [
      'Home' => backpack_url('dashboard'),
      'Diary Overview' => false
    ];

    $userid = $req->user_id ?? backpack_user()->id;
    $user = User::findOrFail($userid);
    $this->data['user'] = $user;

    $cdate = new Carbon();
    $ldate = new Carbon();

    $daterange = new \DatePeriod(
      $cdate->subDays(6),
      \DateInterval::createFromDateString('1 day'),
      $ldate->addDay()
    );
    //$ds =  new DiaryChartController($userid);

    $gdata = $user->status == 1 ? GDWActions::GetStaffRecentPerf($userid, $daterange) : [];

    // pull data from 3 month ago to populate the calendar
    $today = new Carbon;
    $sdate = new Carbon;
    $sdate->subMonths(3);

    $dfs = DailyPerformance::where('user_id', $userid)
      ->where('record_date', '>=', $sdate->toDateString())
      ->where('record_date', '<=', $today->toDateString())
      ->get();

    $evlist = [];

    // load entries
    foreach ($dfs as $df) {
      $bgcollll = 'rgba(0, 0, 255, 0.5)';

      if ($df->expected_hours == 0 && $df->actual_hours > 0) {
        $bgcollll = 'rgba(0, 155, 0, 1)';
      } elseif ($df->expected_hours > 0 && $df->actual_hours == 0) {
        if ($df->is_off_day == true) {
          $bgcollll = 'rgba(66, 66, 66, 0.8)';
        } else {
          $bgcollll = 'rgba(255, 0, 0, 0.5)';
        }
      }

      $evlist[] = \Calendar::event(
        $df->actual_hours . ' / ' . $df->expected_hours . ' hours',
        true, //full day event?
        new \DateTime($df->record_date),
        new \DateTime($df->record_date),
        $df->id, //optional event ID
        [
          'color' => $bgcollll,
          'url' => route('gwdactivity.index', ['act_date' => $df->record_date])
        ]
      );
    }


    // load the public holiday
    $allph = PublicHoliday::whereDate('event_date', '>', $sdate->toDateString())->get();
    foreach ($allph as $key => $value) {
      $evlist[] = \Calendar::event(
        $value->name,
        true,
        new \DateTime($value->event_date),
        new \DateTime($value->event_date),
        $value->id,
        [
          'color' => 'rgba(94, 38, 6, 0.2)'
        ]
      );
    }

    // then load the personal cuti info,
    $personalcuti = StaffLeave::where('user_id', $userid)
      ->whereDate('start_date', '>', $sdate->toDateString())
      ->get();

    foreach ($personalcuti as $key => $value) {

      $eeeedate = new Carbon($value->end_date);
      $eeeedate->addDay();
      $clabel = $value->is_manual == true ? ' - ' . $value->remark : '';

      $evlist[] = \Calendar::event(
        $value->LeaveType->descr . $clabel,
        true,
        new \DateTime($value->start_date),
        new \DateTime($eeeedate),
        $value->id,
        [
          'color' => 'rgba(215, 215, 44, 0.8)'
        ]
      );
    }

    $calendar = \Calendar::addEvents($evlist);
    $calendar->setOptions([
      'header' => [
        'left' => 'prev,next, today',
        'center' => 'title',
        'right' => 'month',
      ],
      'eventLimit' => true,
    ]);
    $this->data['calendar'] = $calendar;

    //for weekly performance
    $time = microtime(true);
    $pgdata = [];
    $weekact = 0;
    $weekexp = 0;
    foreach ($gdata as $key => $value) {
      $pgdata[] = $value['perc'];
      $weekact += $value['actual'];
      $weekexp += $value['expected'];
    }
    error_log(microtime(true)  - $time);
    print_r("<!--  \r\n gwdactions: " . (microtime(true)  - $time) * 1000 . "-->");
    $weekperc = intval($weekexp == 0 ? 100 + ($weekact / (8 * 7) * 100) : $weekact / $weekexp * 100);
    $weekcol = 'success';
    $weektitle = 'You have met your weekly work-hours. We are proud of you!';
    if ($weekperc <= 85) {
      $weekcol = 'warning';
      $weektitle = 'You barely meet the minimum weekly target of between 80% to 85%';
    }

    if ($weekperc <= 80) {
      $weekcol = 'danger';
      $weektitle = 'You are way below the minimum weekly target of 85%';
    }
    $this->data['cdate'] = $cdate;
    $this->data['ldate'] = $ldate;
    $this->data['weekact'] = $weekact;
    $this->data['weekexp'] = $weekexp;

    $this->data['weekcol'] = $weekcol;
    $this->data['weekperc'] = $weekperc;
    $this->data['weektitle'] = $weektitle;


    //end for weekly performance




    /*
    \Widget::add([
      'type'       => 'chart',
      'controller' => $ds,
      'uid' => 'ssed',
      'wrapper' => [
        'class' => 'col-12', // customize the class on the parent element (wrapper)
        'style' => 'border-radius: 10px;',
    ]
      ])->to('cs');
*/



    $chartObj = new DiaryChartController($userid);
    $thechards = [
      'chart' => $chartObj->chart,
      'path' => $chartObj->getLibraryFilePath(),
      'title' => 'User Perf. Percentage'
    ];
    $this->data['aca'] = $thechards;

    $todaydf = GDWActions::GetDailyPerfObj($userid, new Carbon());
    $todayperc = 0;
    $todaycol = 'success';
    $todaytitle = 'You have utilized your work-hours. Nice!';


    // daily performance
    if ($user->status == 1) {
      if ($todaydf->expected_hours == 0) {
        $todayperc = intval(100 + ($todaydf->actual_hours / 8 * 100));
      } else {
        $calcperf = $todaydf->actual_hours / $todaydf->expected_hours * 100;
        $todayperc = intval($calcperf);
      }


      if ($todayperc <= 85) {
        if ($todaydf->is_off_day == true) {
          $todaycol = 'dark';
          $todaytitle = 'On leave';
        } else {
          $todaycol = 'warning';
          $todaytitle = 'You are within the minimum target, between 85% to 100% productivity';

          if ($todayperc <= 80) {
            $todaycol = 'danger';
            $todaytitle = 'You are below the minimum target productivity';
          }
        }
      }
    }

    $this->data['todaycol'] = $todaycol;

    $this->data['todaydf'] = $todaydf;
    $this->data['todayperc'] = $todayperc;
    $this->data['todaytitle'] = $todaytitle;
    //end for daily performance

    // $this->data['gdata'] = $gdata;

    return view('diary.overview', $this->data);
  }

  public function resetDailyPerf(Request $req){
    if($req->filled('ac_dt_id')){
      // Log::info("d");
      $df = DailyPerformance::where('user_id',backpack_user()->id)->where('record_date',$req->ac_dt_id)->first();


      if($df){
        // only allow owner and admin
        if($df->user_id !=
        $req->user()->id && $req->user()->role > 1){
          abort(403);
        }

        $nudf = GDWActions::GetDailyPerfObj($df->user_id, $df->record_date, true, true);
        return "ss";
      } else {
        abort(404);
      }
    } else {
      return "ss";
    }
  }


  public function perfByDate($userid,$dt){
    $user = User::findOrFail($userid);

    $todaydf = GDWActions::GetDailyPerfObj($userid, $dt);
    $todayperc = 0;
    $todaycol = 'success';
    $todaytitle = 'You have utilized your work-hours. Nice!';


    // daily performance
    if ($user->status == 1) {
      if ($todaydf->expected_hours == 0) {
        $todayperc = intval(100 + ($todaydf->actual_hours / 8 * 100));
      } else {
        $calcperf = $todaydf->actual_hours / $todaydf->expected_hours * 100;
        $todayperc = intval($calcperf);
      }


      if ($todayperc <= 85) {
        if ($todaydf->is_off_day == true) {
          $todaycol = 'dark';
          $todaytitle = 'On leave';
        } else {
          $todaycol = 'warning';
          $todaytitle = 'You are within the minimum target, between 85% to 100% productivity';

          if ($todayperc <= 80) {
            $todaycol = 'danger';
            $todaytitle = 'You are below the minimum target productivity';
          }
        }
      }
    }

    $bg = 'bg-success';
    if ($todayperc < 85) {
      $bg = 'bg-info';
    }

    if ($todayperc < 50) {
      $bg = 'bg-warning';
    }

    if ($todayperc == 0) {
      $bg = 'bg-danger';
    }

    $this->data['todaycol'] = $todaycol;

    $this->data['todaydf'] = $todaydf;
    $this->data['todayperc'] = $todayperc;
    $this->data['todaytitle'] = $todaytitle;
    $this->data['bg'] = $bg;

    return $this->data;

  }


}
