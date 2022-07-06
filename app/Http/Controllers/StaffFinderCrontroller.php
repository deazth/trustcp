<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CommonSkillset;
use App\common\CommonHelper;

class StaffFinderCrontroller extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }



  public function staffFinder(Request $req){

    $skilist = CommonSkillset::all();

    $data = 'empty';
    $inp = '';

    if($req->filled('input')){
      $inp2 = $req->input;
      $inp = str_replace('%', '', $inp2);
      if(strlen($inp) < 3){
        \Alert::error('Insufficient input length')->flash();
        return redirect()->back();
      }
      // search for exact persno
      if(is_int($inp)){
        $data = User::where('persno', $inp)->first();
        if($data){
          return redirect()->route('staff.detail', ['uid' => $data->id]);
        }
      }

      // search for exact staff no
      $data = User::where('staff_no', $inp)->first();
      if($data){
        return redirect()->route('staff.detail', ['uid' => $data->id]);
      }

      // search for name
      $data = User::where('name','like','%'.$inp.'%')->get();

    }

    return view('staff.finder.finder', [
      'result' => $data,
      'skills' => $skilist,
      'initialval' => $inp,
      'title' => 'Staff Finder'

    ]);
  }

  public function userDetail($userid){
    $user = User::find($userid);
    $superior_persno = $user->report_to ;

    $superior = User::where('persno',$superior_persno )->first();

    $canmod = false;
    if($userid != backpack_user()->id){
      $canmod = CommonHelper::UserCanAccessUser(backpack_user()->id, $userid);
    }

    return view('staff.finder.user', [
      'user' => $user,
      'superior' => $superior,
      'canmod' => $canmod,
      'title' => 'Staff Profile'
    ]);
  }


}
