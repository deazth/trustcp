<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SapLeaveManager;
use App\Jobs\SapProfileLoader;


class SuperPowerController extends Controller
{

  public function __construct()
  {
    $this->middleware(['permission:super-admin']);
  }


  public function index(Request $req){
    $workcount = \DB::table('jobs')
      ->select([
        'queue',
        'attempts',
        \DB::raw('count(1) as work_count')
      ])->groupBy('queue', 'attempts')->get();

    $inprogress = \DB::table('jobs')
      ->where('attempts', '!=', 0)->get();

    // dd($workcount);

    return view('super.index', [
      'workcount' => $workcount,
      'inprogress' => $inprogress
    ]);
  }

  public function runjob(Request $req){

    if($req->filled('jobtype')){
      switch ($req->jobtype) {
        case 'SapLeaveManager':
          SapLeaveManager::dispatch();
          break;
        case 'SapProfileLoader':
          SapProfileLoader::dispatch();
          break;
        default:
          \Alert::warning('Unknown jobtype: ' . $req->jobtype)->flash();
          break;
      }
    }

    return redirect()->route('suppa.index');
  }

  public function LdapDataScan(Request $req){
    $gotdata = false;
    $input = '';
    $data = [];

    if($req->filled('costcenter')){
      $gotdata = true;
      $input = $req->costcenter;

      $data = \App\common\LdapHelper::LdapScanner($input);

    }


    return view('super.ldapfinder', [
      'gotdata' => $gotdata,
      'input' => $input,
      'data' => $data
    ]);
  }
}
