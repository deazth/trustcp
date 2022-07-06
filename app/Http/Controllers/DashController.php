<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LocationHistory;
use App\common\DashHelper;
use Backpack\CRUD\app\Library\Widget;

class DashController extends Controller
{
    public function checkin(Request $req)
    {
        $user = backpack_user();
        $checkin = DashHelper::getLatestLocationHist($user);


        return view('dash.checkin', ['checkin'=>$checkin]);
        //return $checkin;
    }

    public function diaryChart(Request $req)
    {

        \Widget::add([
            'type'       => 'chart',
            'controller' => \App\Http\Controllers\Admin\Charts\DiaryChartController::class,
            ]);

        return view('dash.diary');

    }

    public function index(){
      $this->data['title'] = 'Monthly User Statistic';
      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'User Statistic' => route('sbdash.index'),
          'Monthly' => false
      ];

      return view('dash.index', $this->data);
    }

    public function userStat(Request $req){
      $this->data['title'] = 'Monthly User Usage';
      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'User Statistic' => route('userstat.index'),
          'Monthly Usage' => false
      ];

      $edate = new \Carbon\Carbon;
      $edate->subDay();
      $sdate = new \Carbon\Carbon($edate);
      $sdate->subMonths(6);

      $maxdate = new \Carbon\Carbon;
      $maxdate->subDay();

      if($req->filled('sdate')){
        $sdate = new \Carbon\Carbon($req->sdate);
      }

      if($req->filled('edate')){
        $edate = new \Carbon\Carbon($req->edate);
        if($edate->gt($maxdate)){
          $edate = $maxdate;
        }
      }

      $sdateinp = $sdate->startOfMonth()->toDateString();
      $edateinp = $edate->endOfMonth()->toDateString();


      $catkontrol = new \App\Http\Controllers\Admin\Charts\SbdUserStatChartController($sdateinp, $edateinp);
      $thechards[] = [
        'chart' => $catkontrol->chart,
        'path' => $catkontrol->getLibraryFilePath(),
        'title' => 'Monthly User Usage from ' . $sdateinp .' to ' . $edateinp
      ];


      $this->data['sdate'] = $sdateinp;
      $this->data['edate'] = $edateinp;
      $this->data['thecharts'] = $thechards;
      $this->data['maxdate'] = $maxdate;
      $this->data['route'] = 'userstat.monthlyuserstat';

      return view('dash.userstats', $this->data);
    }

    public function dailyuserstat(Request $req){
      $this->data['title'] = 'Daily User Usage';
      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'User Statistic' => route('userstat.index'),
          'Daily Usage' => false
      ];

      $edate = new \Carbon\Carbon;
      $edate->subDay();
      $sdate = new \Carbon\Carbon($edate);
      $sdate->subDays(6);

      $maxdate = new \Carbon\Carbon;
      $maxdate->subDay();

      if($req->filled('edate')){
        $edate = new \Carbon\Carbon($req->edate);
        if($edate->gte($maxdate)){
          $edate = $maxdate;
        }
      }

      if($req->filled('sdate')){
        $tdate = new \Carbon\Carbon($req->sdate);

        if($tdate->gt($edate)){
          // prevent future start date
          Widget::add([
            'type'         => 'alert',
            'class'        => 'alert alert-warning mb-2',
            'heading'      => 'Bad Input',
            'content'      => 'Start date is after the end date',
            'close_button' => true, // show close button or not
          ])->to('before_content');
        } elseif($tdate->lt($sdate) && $tdate->diffInDays($sdate) > 31){
          // prevent date range more than 31 days
          Widget::add([
            'type'         => 'alert',
            'class'        => 'alert alert-warning mb-2',
            'heading'      => 'Bad Input',
            'content'      => 'Selected date range is too big. Default to 6 days instead.',
            'close_button' => true, // show close button or not
          ])->to('before_content');
        } else {
          $sdate = $tdate;
        }
      }



      $sdateinp = $sdate->toDateString();
      $edateinp = $edate->toDateString();

      $thechards = [];
      $catkontrol = new \App\Http\Controllers\Admin\Charts\DailyUserStatsChartController($sdateinp, $edateinp);
      $thechards[] = [
        'chart' => $catkontrol->chart,
        'path' => $catkontrol->getLibraryFilePath(),
        'title' => 'Daily User Usage from ' . $sdateinp .' to ' . $edateinp
      ];

      $catkontrol = new \App\Http\Controllers\Admin\Charts\UserStatsDurLobChartController($sdateinp, $edateinp);
      $thechards[] = [
        'chart' => $catkontrol->chart,
        'path' => $catkontrol->getLibraryFilePath(),
        'title' => 'Usages by LOB from ' . $sdateinp .' to ' . $edateinp
      ];


      $this->data['sdate'] = $sdateinp;
      $this->data['edate'] = $edateinp;
      $this->data['thecharts'] = $thechards;
      $this->data['maxdate'] = $maxdate->toDateString();
      $this->data['route'] = 'userstat.dailyuserstat';

      return view('dash.userstats', $this->data);
    }

}
