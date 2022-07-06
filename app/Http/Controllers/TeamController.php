<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Backpack\CRUD\app\Library\Widget;
use App\common\CommonHelper;
use App\common\GDWActions;
use App\common\CheckinHelper;
use App\Models\User;
use App\Models\LocationHistory;
use \Carbon\Carbon;

class TeamController extends Controller
{
  public function index(Request $req){
    $data = [];

    $user = backpack_user();
    if($req->filled('uid')){
      $user = User::findOrFail($req->uid);
      $perm = 1; //CommonHelper::UserCanAccessUser(backpack_user()->id, $req->uid);
      if(!$perm){
        CommonHelper::Log403Err(backpack_user(), $user, 'Team index', 'perm ' . $perm);
        abort(403);
      }
    }

    return view('team.index', ['user' => $user,
    'title' => 'Team Member']);
  }

  public function lastKnownLoc(Request $req){
    $user = backpack_user();
    if($req->filled('uid')){
      $user = User::findOrFail($req->uid);
      if(!CommonHelper::UserCanAccessUser(backpack_user()->id, $req->uid)){
        CommonHelper::Log403Err(backpack_user(), $user, 'Team last loc', 'perm 0');
        abort(403);
      }
    }

    $indate = date('Y-m-d');
    if($req->filled('indate')){
      $indate = $req->indate;
    }

    $subs = [];

    $llocSelf = LocationHistory::where('user_id', $user->id)
    ->whereDate('created_at', $indate)
    ->latest()->first();

    if($llocSelf){
      $subs[] = [
        'uid' => $user->id,
        'name' => $user->name,
        'time' => $llocSelf->created_at,
        'addr' => $llocSelf->address,
        'lat' => $llocSelf->latitude,
        'long' => $llocSelf->longitude,
        'gotloc' => true
      ];
    } else {
      $subs[] = [
        'uid' => $user->id,
        'name' => $user->name,
        'time' => '',
        'addr' => '',
        'lat' => 0,
        'long' => 0,
        'gotloc' => false
      ];
    }



    foreach ($user->Subordinates as $key => $value) {
      $lloc = LocationHistory::where('user_id', $value->id)
        ->whereDate('created_at', $indate)
        ->latest()->first();

      if($lloc){
        $subs[] = [
          'uid' => $value->id,
          'name' => $value->name,
          'time' => $lloc->created_at,
          'addr' => $lloc->address,
          'lat' => $lloc->latitude,
          'long' => $lloc->longitude,
          'gotloc' => true
        ];
      } else {
        $subs[] = [
          'uid' => $value->id,
          'name' => $value->name,
          'time' => '',
          'addr' => '',
          'lat' => 0,
          'long' => 0,
          'gotloc' => false
        ];
      }
    }

    return view('team.lastloc', [
      'user' => $user,
      'indate' => $indate,
      'tmember' => $subs,
      'title' => 'Team Last Location'
    ]);
  }

  public function diaryperf(Request $req){
    $user = backpack_user();
    if($req->filled('uid')){
      $user = User::findOrFail($req->uid);
      if(!CommonHelper::UserCanAccessUser(backpack_user()->id, $req->uid)){
        CommonHelper::Log403Err(backpack_user(), $user, 'Team last loc', 'perm 0');
        abort(403);
      }
    }

    $startdate = new Carbon;
    $enddate = new Carbon;
    if($req->filled('sdate')){
      $startdate = new Carbon($req->sdate);
    } else {
      $startdate->subDays(6);
    }

    if($req->filled('edate')){
      $enddate = new Carbon($req->edate);
    }

    $subs = [];
    $datelist = [];
    $header = [];

    // default header
    $header[] = 'Staff';



    // dont allow for date diff to be more than 31 days
    $daydiff = $startdate->diffInDays($enddate);

    if($daydiff > 31){
      Widget::add([
        'type'         => 'alert',
        'class'        => 'alert alert-danger mb-2',
        'heading'      => 'Input error',
        'content'      => 'The date difference is too big',
        'close_button' => true, // show close button or not
      ])->to('before_content');
    } else {
      $dateiter = new Carbon($startdate);
      $daycount = 0;
      while($dateiter->lte($enddate)){
        $header[] = $dateiter->format('D d-M');
        $daycount++;
        $dateiter->addDay();
      }


      $lusers = $user->Subordinates;
      $lusers = $lusers->concat([$user]);
     // dd($lusers);
      foreach ($lusers as $key => $value) {
        $oneuser = [];
        $totalexp = 0;
        $totalact = 0;
        $oneuser['name'] = $value->id_name;
        $daydfs = [];


        $dateiter = new Carbon($startdate);
        while($dateiter->lte($enddate)){
          $df= GDWActions::GetDailyPerfObj($value->id, $dateiter->toDateString(), false);
          $daydfs[] = $df;
          $totalexp += $df->expected_hours;
          $totalact += $df->actual_hours;
          $dateiter->addDay();
        }

        $oneuser['dfs'] = $daydfs;
        $oneuser['dc'] = $daycount;
        $oneuser['total_exp'] = $totalexp;
        $oneuser['total_act'] = $totalact;
        $oneuser['avg_prod'] = GDWActions::CalcPerfPerc($totalexp, $totalact, $daycount);

        $subs[] = $oneuser;
      }
    }

    // header at the back
    $header[] = 'Days';
    $header[] = 'Total Expected';
    $header[] = 'Total Hours';
    $header[] = '% Productivity';

    return view('team.diaryperf', [
      'user' => $user,
      'sdate' => $startdate->toDateString(),
      'edate' => $enddate->toDateString(),
      'tmember' => $subs,
      'header' => $header,
      'title' => 'Team Diary Performance'
    ]);
  }

  public function checkinout(Request $req){
    $user = backpack_user();
    if($req->filled('uid')){
      $user = User::findOrFail($req->uid);
      if(!CommonHelper::UserCanAccessUser(backpack_user()->id, $req->uid)){
        CommonHelper::Log403Err(backpack_user(), $user, 'Team inout', 'perm 0');
        abort(403);
      }
    }

    $startdate = new Carbon;
    $enddate = new Carbon;
    if($req->filled('sdate')){
      $startdate = new Carbon($req->sdate);
    } else {
      $startdate->subDays(6);
    }

    if($req->filled('edate')){
      $enddate = new Carbon($req->edate);
    }

    $subs = [];
    $datelist = [];
    $header = [];

    // default header
    $header[] = 'Staff';

    // dont allow for date diff to be more than 31 days
    $daydiff = $startdate->diffInDays($enddate);

    if($daydiff > 31){
      Widget::add([
        'type'         => 'alert',
        'class'        => 'alert alert-danger mb-2',
        'heading'      => 'Input error',
        'content'      => 'The date difference is too big',
        'close_button' => true, // show close button or not
      ])->to('before_content');
    } else {
      $dateiter = new Carbon($startdate);
      $daycount = 0;
      while($dateiter->lte($enddate)){
        $header[] = $dateiter->format('D d-M') . ' In';
        $header[] = $dateiter->format('D d-M') . ' Out';
        $daycount++;
        $dateiter->addDay();
      }

      foreach ($user->Subordinates as $key => $value) {
        $oneuser = [];
        $totalexp = 0;
        $totalact = 0;
        $oneuser['name'] = $value->id_name;
        $daydfs = [];


        $dateiter = new Carbon($startdate);
        while($dateiter->lte($enddate)){
          $df= CheckinHelper::GetCheckInOut($value, $dateiter->toDateString());
          $daydfs[] = $df;
          $dateiter->addDay();
        }

        $oneuser['dfs'] = $daydfs;
        $subs[] = $oneuser;
      }
    }

    return view('team.checkinout', [
      'user' => $user,
      'sdate' => $startdate->toDateString(),
      'edate' => $enddate->toDateString(),
      'tmember' => $subs,
      'header' => $header,
      'title' => 'Team Checkins'
    ]);
  }

}
