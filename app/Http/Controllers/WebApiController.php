<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Unit;
use App\Models\BauExpType;
use App\common\IopHandler;
use App\common\DiaryHelper;
use App\common\SkillHelper;
use App\common\SeatHelper;
use Illuminate\Support\Facades\Log;

/**
  collection of API to be called by the web
 */
class WebApiController extends Controller
{

  public function FindUsers(Request $req)
  {
    $search_term = $req->input('q');

    $results = [];

    if ($search_term) {
      // first search for exact staff no
      $single = User::where('staff_no', $search_term)->where('status', 1)->first();
      if ($single) {
        array_push($results, $single);
        return [
          'data' => $results,
          'total' => 1

        ];
      } else {
        $results = User::where('status', 1)->where('name', 'LIKE', '%' . $search_term . '%')->paginate(10);
      }
    } else {
      $results = User::where('status', 1)->paginate(10);
    }

    return $results;
  }

  public function FindUnits(Request $req)
  {
    $search_term = $req->input('q');

    $results = [];

    if ($search_term) {
      // first search for exact staff no
      if (is_int($search_term)) {
        $single = Unit::where('pporgunit', $search_term)->first();
        if ($single) {
          array_push($results, $single);
          return [
            'data' => $results,
            'total' => 1

          ];
        }
      }

      $results = Unit::where('Pporgunitdesc', 'LIKE', '%' . $search_term . '%')->paginate(10);
    } else {
      $results = Unit::paginate(10);
    }

    return $results;
  }


  public function getImage(Request $req)
  {
    // dd(config('app.env'));
    if(config('app.env') != 'production'){
      return response()->file(public_path('images/hell0k1tty/helloKitty.jpg'));
    }
    if ($req->filled('staff_no')) {
      //dd('here by staffno');
      //return IopHandler::GetStaffImage($req->staff_no);
      try {

        return IopHandler::GetStaffImageAPIgate($req->staff_no);
      } catch (\Throwable $te) {

        return '';
      }
    } elseif ($req->filled('persno')) {
      try {

        $user = User::where('persno', $req->persno)->first();


        if ($user) {
          $staffno = $user->staff_no;
          return IopHandler::GetStaffImageAPIgate($staffno);

        }
        else{
          return '';
        }
      } catch (\Throwable $te) {

        return '';
      }
    }
  }

  public function GetActType(Request $req)
  {
    $search_term = $req->input('q');
    $form = collect($req->input('form'))->pluck('value', 'name');

    // act tag not selected
    if (!isset($form['task_category_id'])) {
      return [];
    }

    $rets = DiaryHelper::GetActType($form['task_category_id'], $search_term);
    // dd($rets->get());

    return $rets ? $rets->paginate(20) : [];
  }

  public function GetActSubType(Request $req)
  {
    $search_term = $req->input('q');
    $form = collect($req->input('form'))->pluck('value', 'name');

    // act tag not selected
    if (!isset($form['activity_type_id'])) {
      return [];
    }

    $rets = DiaryHelper::GetGrpActSubType(backpack_user()->id, $form['activity_type_id'], $search_term);


    return $rets ? $rets->paginate(20) : [];
  }

  public function GetBauExps(Request $req)
  {
    $search_term = $req->input('q');
    $form = collect($req->input('form'))->pluck('value', 'name');

    // act tag not selected
    if (!isset($form['bau_exp_type_id'])) {
      return [];
    }

    $bauexp = BauExpType::find($form['bau_exp_type_id']);
    if($bauexp){
      if($search_term != ''){
        return $bauexp->BauExperiences()->where('name', 'LIKE', '%' . $search_term . '%')->paginate(20);
      }

      return $bauexp->BauExperiences()->paginate(20);
    }

    return [];
  }

  public function GetBauRoles(Request $req)
  {
    $search_term = $req->input('q');
    $form = collect($req->input('form'))->pluck('value', 'name');

    // act tag not selected
    if (!isset($form['bau_exp_type_id'])) {
      return [];
    }

    $bauexp = BauExpType::find($form['bau_exp_type_id']);

    return $bauexp ? $bauexp->Jobscopes()->paginate(20) : [];
  }

  public function reverseGeo(Request $req)
  {
    //return IopHandler::ReverseGeo($req->lat, $req->lon);
    return IopHandler::ReverseGeoAPIgate($req->lat, $req->lon);
  }

  public function getSkillType(Request $req)
  {
    $form = collect($req->input('form'))->pluck('value', 'name');
    $skill_cat = $form['skill_cat_id'];
    $sh = SkillHelper::GetSkillTypeByCat($skill_cat);
    return $sh->paginate(1000);
  }

  public function getSkillSet(Request $req)
  {
    $form = collect($req->input('form'))->pluck('value', 'name');
    if (!isset($form['skill_type_id'])) {
      return [];
    }
    $skill_type = $form['skill_type_id'];
    $st = SkillHelper::GetCommonSkillByType($skill_type);
    // Log::error($st->get());
    return $st->paginate(1000);
  }

  public function getFloorList(Request $req)
  {
    $search_term = $req->input('q');
    $form = collect($req->input('form'))->pluck('value', 'name');

    // act tag not selected
    if (!isset($form['building_id'])) {
      return [];
    }

    $rets = SeatHelper::GetFloorList($form['building_id'], $search_term);

    return $rets;
  }

  public function getFloorSectionList(Request $req)
  {
    $search_term = $req->input('q');
    $form = collect($req->input('form'))->pluck('value', 'name');

    // act tag not selected
    if (!isset($form['floor_id'])) {
      return [];
    }

    $rets = SeatHelper::GetFloorSectionList($form['floor_id'], $search_term);

    return $rets;
  }
}
