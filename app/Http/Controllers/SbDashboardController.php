<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Floor;
use App\Models\FloorSection;
use App\common\SeatHelper;

class SbDashboardController extends Controller
{

  public function __construct()
  {
    $this->middleware(['permission:infra-dashboard']);
  }

  public function index(){
    $this->data['title'] = 'Agile Office Dashboard';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'Agile Office Dashboard' => false
    ];

    return view('sbdash.index', $this->data);
  }

  public function BuildFloorUtil(Request $req){

    $this->data['title'] = 'Building - Seat Utilization';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'Agile Office Dashboard' => route('sbdash.index'),
        'Building Util' => false
    ];

    $blist = Building::all();
    $this->data['bl'] = $blist;
    $selb = 0;
    $gotdata = false;
    $indate = date('Y-m-d');
    $maxdate = date('Y-m-d');
    foreach ($blist as $key => $value) {
      $selb = $value->id;
      break;
    }

    if($req->filled('indate')){
      $indate = $req->indate;
    }

    if($req->filled('bid')){
      $selb = $req->bid;
    }

    if($selb != 0){
      $gotdata = true;
    }
    $build = Building::findOrFail($selb);
    $thechards = [];
    $summ = SeatHelper::GetBuildingSeatSummary($build, $indate);

    // child utils
    $catkontrol = new \App\Http\Controllers\Admin\Charts\SbdBuildUtilChartController($build->id, $indate);
    $thechards[] = [
      'chart' => $catkontrol->chart,
      'path' => $catkontrol->getLibraryFilePath(),
      'title' => 'Floor Utilization'
    ];

    // waterfall
    $catkontrol = new \App\Http\Controllers\Admin\Charts\SbdDailyBldWfallChartController($build->id, $indate);
    $thechards[] = [
      'chart' => $catkontrol->chart,
      'path' => $catkontrol->getLibraryFilePath(),
      'title' => 'Waterfall Floor Utilization'
    ];

    // interval data
    $catkontrol2 = new \App\Http\Controllers\Admin\Charts\SbBuildIntvChartController($build->id, $indate);
    $thechards[] = [
      'chart' => $catkontrol2->chart,
      'path' => $catkontrol2->getLibraryFilePath(),
      'title' => 'Interval Data'
    ];


    $this->data['gotdata'] = $gotdata;
    $this->data['selb'] = $selb;
    $this->data['indate'] = $indate;
    $this->data['maxdate'] = $maxdate;
    $this->data['build'] = $build;
    $this->data['summ'] = $summ;
    $this->data['summh1'] = 'Floor';
    $this->data['thecharts'] = $thechards;
    $this->data['nexturl'] = 'sbdash.rpt_ao_f_fs_daily';
    $this->data['currenturl'] = 'sbdash.rpt_ao_b_f_daily';
    return view('sbdash.seatutil', $this->data);
  }

  public function BuildDurUtil(Request $req){
    $this->data['title'] = 'Building - Seat Duration Util';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'Agile Office Dashboard' => route('sbdash.index'),
        'Building Duration Util' => false
    ];

    $blist = Building::all();
    $this->data['bl'] = $blist;
    $selb = 0;

    $edate = new \Carbon\Carbon;
    $edate->subDay();
    $sdate = new \Carbon\Carbon($edate);
    $sdate->subDays(6);

    $maxdate = date('Y-m-d');

    if($req->filled('bid')){
      $selb = $req->bid;
    } else {
      foreach ($blist as $key => $value) {
        $selb = $value->id;
        break;
      }
    }
    if($req->filled('sdate')){
      $sdate = new \Carbon\Carbon($req->sdate);
    }

    if($req->filled('edate')){
      $edate = new \Carbon\Carbon($req->edate);
    }

    $build = Building::findOrFail($selb);

    $catkontrol = new \App\Http\Controllers\Admin\Charts\SbdBuildDurUtilChartController($build->id, $sdate->toDateString(), $edate->toDateString());
    $thechards[] = [
      'chart' => $catkontrol->chart,
      'path' => $catkontrol->getLibraryFilePath(),
      'title' => $sdate->toDateString() .' to ' . $edate->toDateString() . ' Utilization for ' . $build->GetLabel()
    ];


    $this->data['sdate'] = $sdate->toDateString();
    $this->data['edate'] = $edate->toDateString();
    $this->data['build'] = $build;
    $this->data['thecharts'] = $thechards;

    return view('sbdash.builddurutil', $this->data);
  }

  public function BuildFloorMonthlyUtil(Request $req){

    $this->data['title'] = 'Building - Monthly Seat Utilization';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'Agile Office Dashboard' => route('sbdash.index'),
        'Building Monthly Util' => false
    ];

    $blist = Building::all();
    $this->data['bl'] = $blist;
    $selb = 0;
    $gotdata = false;
    $indate = new \Carbon\Carbon;
    $maxdate = date('Y-m-d');
    $indate->day = 1;

    foreach ($blist as $key => $value) {
      $selb = $value->id;
      break;
    }

    if($req->filled('indate')){
      $indate = $req->indate;
    }

    if($req->filled('bid')){
      $selb = $req->bid;
    }

    if($selb != 0){
      $gotdata = true;
    }
    $build = Building::findOrFail($selb);
    $thechards = [];
    // $summ = SeatHelper::GetBuildingSeatSummary($build, $indate);

    // child utils
    $catkontrol = new \App\Http\Controllers\Admin\Charts\SbdBuildMonthUtilChartController($build->id, $indate);
    $thechards[] = [
      'chart' => $catkontrol->chart,
      'path' => $catkontrol->getLibraryFilePath(),
      'title' => 'Floor Utilization'
    ];

    // interval data
    $catkontrol2 = new \App\Http\Controllers\Admin\Charts\SbdBuildMonthIntvChartController($build->id, $indate);
    $thechards[] = [
      'chart' => $catkontrol2->chart,
      'path' => $catkontrol2->getLibraryFilePath(),
      'title' => 'Interval Data'
    ];


    $this->data['gotdata'] = $gotdata;
    $this->data['selb'] = $selb;
    $this->data['indate'] = $indate;
    $this->data['maxdate'] = $maxdate;
    $this->data['build'] = $build;
    // $this->data['summ'] = $summ;
    $this->data['summh1'] = 'Floor';
    $this->data['thecharts'] = $thechards;
    $this->data['nexturl'] = 'sbdash.rpt_ao_f_fs_daily';
    $this->data['currenturl'] = 'sbdash.rpt_ao_b_f_monthly';
    return view('sbdash.seatmonthlyutil', $this->data);
  }

  public function FloorFsUtil(Request $req){

    $blist = Floor::where('status', 1)->get();
    $this->data['bl'] = $blist;
    $selb = 0;
    $gotdata = false;
    $indate = date('Y-m-d');
    $maxdate = date('Y-m-d');
    foreach ($blist as $key => $value) {
      $selb = $value->id;
      break;
    }

    if($req->filled('indate')){
      $indate = $req->indate;
    }

    if($req->filled('bid')){
      $selb = $req->bid;
    }

    if($selb != 0){
      $gotdata = true;
    }
    $build = Floor::findOrFail($selb);

    $this->data['title'] = 'Floor - Seat Utilization';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'Agile Office Dashboard' => route('sbdash.index'),
        $build->Buildings->building_name => route('sbdash.rpt_ao_b_f_daily', ['bid' => $build->building_id, 'indate' => $indate]),
        'Floor Util' => false
    ];

    $thechards = [];
    $summ = SeatHelper::GetFloorSecSeatUtilSummary($build, $indate);

    // child utils
    $catkontrol = new \App\Http\Controllers\Admin\Charts\SbdFloorUtilChartController($build->id, $indate);
    $thechards[] = [
      'chart' => $catkontrol->chart,
      'path' => $catkontrol->getLibraryFilePath(),
      'title' => 'Section Utilization'
    ];

    // interval data
    $catkontrol2 = new \App\Http\Controllers\Admin\Charts\SbFloorIntvChartController($build->id, $indate);
    $thechards[] = [
      'chart' => $catkontrol2->chart,
      'path' => $catkontrol2->getLibraryFilePath(),
      'title' => 'Interval Data'
    ];


    $this->data['gotdata'] = $gotdata;
    $this->data['selb'] = $selb;
    $this->data['indate'] = $indate;
    $this->data['maxdate'] = $maxdate;
    $this->data['build'] = $build;
    $this->data['summ'] = $summ;
    $this->data['summh1'] = 'Section';
    $this->data['thecharts'] = $thechards;
    $this->data['nexturl'] = 'sbdash.rpt_ao_fs_daily';
    $this->data['currenturl'] = 'sbdash.rpt_ao_f_fs_daily';

    return view('sbdash.seatutil', $this->data);
  }

  public function FsDetailUtil(Request $req){
    $this->data['title'] = 'Floor Section - Seat Utilization';


    $blist = FloorSection::where('status', 1)->get();
    $this->data['bl'] = $blist;
    $selb = 0;
    $gotdata = false;
    $indate = date('Y-m-d');
    $maxdate = date('Y-m-d');
    foreach ($blist as $key => $value) {
      $selb = $value->id;
      break;
    }

    if($req->filled('indate')){
      $indate = $req->indate;
    }

    if($req->filled('bid')){
      $selb = $req->bid;
    }

    if($selb != 0){
      $gotdata = true;
    }
    $build = FloorSection::findOrFail($selb);
    $thechards = [];

    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'Agile Office Dashboard' => route('sbdash.index'),
        $build->Floor->Buildings->building_name => route('sbdash.rpt_ao_b_f_daily', ['bid' => $build->Floor->building_id, 'indate' => $indate]),
        $build->Floor->floor_name => route('sbdash.rpt_ao_f_fs_daily', ['bid' => $build->floor_id, 'indate' => $indate]),
        'Floor Section Util' => false
    ];
    $summ = [];

    $catkontrol = new \App\Http\Controllers\Admin\Charts\SbdFsIntvUtilChartController($build->id, $indate);
    $thechards[] = [
      'chart' => $catkontrol->chart,
      'path' => $catkontrol->getLibraryFilePath(),
      'title' => 'Interval Data'
    ];


    $this->data['gotdata'] = $gotdata;
    $this->data['selb'] = $selb;
    $this->data['indate'] = $indate;
    $this->data['maxdate'] = $maxdate;
    $this->data['build'] = $build;
    $this->data['summ'] = $summ;
    $this->data['thecharts'] = $thechards;
    $this->data['nexturl'] = 'sbdash.rpt_ao_fs_daily';
    $this->data['currenturl'] = 'sbdash.rpt_ao_fs_daily';

    return view('sbdash.seatutil', $this->data);
  }

  public function BuildAreaUtil(Request $req){
    $this->data['title'] = 'Building - Area Utilization';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'Agile Office Dashboard' => route('sbdash.index'),
        'Area Util' => false
    ];

    $blist = Building::all();
    $this->data['bl'] = $blist;
    $selb = 0;
    $gotdata = false;
    $indate = date('Y-m-d');
    $maxdate = date('Y-m-d');
    foreach ($blist as $key => $value) {
      $selb = $value->id;
      break;
    }

    if($req->filled('indate')){
      $indate = $req->indate;
    }

    if($req->filled('bid')){
      $selb = $req->bid;
    }

    if($selb != 0){
      $gotdata = true;
    }
    $build = Building::findOrFail($selb);
    $thechards = [];
    // $summ = SeatHelper::GetBuildingSeatSummary($build, $indate);

    // child utils
    $catkontrol = new \App\Http\Controllers\Admin\Charts\SbAreaDailyChartController($build->id, $indate);
    $thechards[] = [
      'chart' => $catkontrol->chart,
      'path' => $catkontrol->getLibraryFilePath(),
      'title' => 'Area Utilization'
    ];

    // interval data
    $catkontrol2 = new \App\Http\Controllers\Admin\Charts\SbAreaIntvChartController($build->id, $indate);
    $thechards[] = [
      'chart' => $catkontrol2->chart,
      'path' => $catkontrol2->getLibraryFilePath(),
      'title' => 'Interval Data'
    ];


    $this->data['gotdata'] = $gotdata;
    $this->data['selb'] = $selb;
    $this->data['indate'] = $indate;
    $this->data['maxdate'] = $maxdate;
    $this->data['build'] = $build;
    // $this->data['summ'] = $summ;
    // $this->data['summh1'] = 'Floor';
    $this->data['thecharts'] = $thechards;
    // $this->data['nexturl'] = 'sbdash.rpt_ao_f_fs_daily';
    // $this->data['currenturl'] = 'sbdash.rpt_ao_b_f_daily';
    return view('sbdash.areautil', $this->data);
  }

  public function BuildAreaMonth(Request $req){
    $this->data['title'] = 'Monthly Area Utilization';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'Agile Office Dashboard' => route('sbdash.index'),
        'Area Monthly Util' => false
    ];

    $blist = Building::all();
    $this->data['bl'] = $blist;
    $selb = 0;
    $gotdata = false;
    $indate = date('Y-m');
    $maxdate = date('Y-m');
    foreach ($blist as $key => $value) {
      $selb = $value->id;
      break;
    }

    if($req->filled('indate')){
      $indate = $req->indate;
    }

    if($req->filled('bid')){
      $selb = $req->bid;
    }

    if($selb != 0){
      $gotdata = true;
    }
    $build = Building::findOrFail($selb);
    $thechards = [];
    // $summ = SeatHelper::GetBuildingSeatSummary($build, $indate);

    // child utils
    $catkontrol = new \App\Http\Controllers\Admin\Charts\SbAreaMonthlyChartController($build->id, $indate);
    $thechards[] = [
      'chart' => $catkontrol->chart,
      'path' => $catkontrol->getLibraryFilePath(),
      'title' => 'Monthly Total Hour'
    ];

    // interval data
    $catkontrol2 = new \App\Http\Controllers\Admin\Charts\SbAreaWeeklyIntvChartController($build->id, $indate);
    $thechards[] = [
      'chart' => $catkontrol2->chart,
      'path' => $catkontrol2->getLibraryFilePath(),
      'title' => 'Total by week-day'
    ];


    $this->data['gotdata'] = $gotdata;
    $this->data['selb'] = $selb;
    $this->data['indate'] = $indate;
    $this->data['maxdate'] = $maxdate;
    $this->data['build'] = $build;
    // $this->data['summ'] = $summ;
    // $this->data['summh1'] = 'Floor';
    $this->data['thecharts'] = $thechards;
    // $this->data['nexturl'] = 'sbdash.rpt_ao_f_fs_daily';
    // $this->data['currenturl'] = 'sbdash.rpt_ao_b_f_daily';
    return view('sbdash.areamonthly', $this->data);
  }
}
