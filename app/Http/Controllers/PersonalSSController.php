<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SkillCategory;
use App\Models\SkillType;
//use App\Jobscope;
//use App\Involvement;
use App\Models\CommonSkillset;
use App\Models\PersonalSkillset;
use App\Models\PersSkillHistory;
//use App\BauExperience;
//use App\BauExpType;
use App\Models\User;
//use App\CompGroup;
//use App\common\UserHelper;

class PersonalSSController extends Controller
{


  public function list(Request $req){
    $sid = $req->user()->id;
    $isvisitor = false;
    $isboss = false;
    if($req->filled('staff_id')){
      if($sid != $req->staff_id){
        $isvisitor = true;
        $isboss = \App\common\UserRegisterHandler::isInReportingLine($req->staff_id, $sid);
        $sid = $req->staff_id;
      }
    }

    $user = User::find($sid);

    if($user){

    } else {
      abort(404);
    }

    $skillcat = SkillCategory::all();
    $skilltype = SkillType::all();
    //$skills = CommonSkillset::all();
    //$perskill = PersonalSkillset::where('staff_id', $sid)->get();

    return view('staff.skillset', [
      'user' => $user,
      'skillcat' => $skillcat,
      'skilltype' => $skilltype,
      //'skills' => $skills,
      //'pskills' => $perskill,
      'isvisitor' => $isvisitor,
      'isboss' => $isboss
    ]);

  }

  public function listExpage(Request $req){

    $sid = $req->user()->id;
    $isvisitor = false;
    $isboss = false;
    if($req->filled('staff_id')){
      if($sid != $req->staff_id){
        $isvisitor = true;
        $isboss = \App\common\UserRegisterHandler::isInReportingLine($req->staff_id, $sid);
        $sid = $req->staff_id;
      }
    }

    $user = User::find($sid);

    if($user){

    } else {
      abort(404);
    }

    $pastexps = Involvement::where('user_id', $sid)->get();
    $exptype = BauExpType::all();
    $firstkeyitem = 0;
    $newmaxperc = 100; // - $pastexps->sum('perc');
    foreach ($exptype as $key => $value) {
      $firstkeyitem = $key;
      break;
    }


    return view('staff.experiences', [
      'user' => $user,
      'isvisitor' => $isvisitor,
      'isboss' => $isboss,
      'pastexps' => $pastexps,
      'btypes' => $exptype,
      'firsttypekey' => $firstkeyitem,
      'maxperc' => $newmaxperc
    ]);
  }

  private function addPersonalSkill($staff_id, $skill_id){
    $ps = PersonalSkillset::where('staff_id', $staff_id)->where('common_skill_id', $skill_id)->first();
    if(!$ps){
      $ps = new PersonalSkillset;
      $ps->common_skill_id = $skill_id;
      $ps->staff_id = $staff_id;
      $ps->save();
    }

    return $ps;
  }

  public function updatev2(Request $req){
    $newstatus = 'N';
    // double check
    if($req->user()->id != $req->staff_id){
      // added by someone else
      if(\App\common\UserRegisterHandler::isInReportingLine($req->staff_id, $req->user()->id)){
        // added by boss
        $newstatus = 'A';
      } else {
        // not validly added
        return redirect()->back()->with([
          'alert' => 'You are not allowed to add skill for this person',
          'a_type' => 'danger'
        ]);
      }
    }

    // check if the skill exists
    $ps = PersonalSkillset::where('staff_id', $req->staff_id)
      ->where('common_skill_id', $req->csid)
      ->where('status', '!=', 'D')->first();
    if($ps){
      return redirect()->back()->with([
        'alert' => 'Skill already added. Update it instead',
        'a_type' => 'warning'
      ]);
    }

    $ps = $this->addPersonalSkill($req->staff_id, $req->csid);
    $oldlevel = $ps->level ?? 0;
    $ps->level = $req->rate;
    $ps->status = $newstatus;
    $ps->save();

    // add the history
    $phist = new PersSkillHistory;
    $phist->personal_skillset_id = $ps->id;
    $phist->action_user_id = $req->user()->id;
    $phist->newlevel = $req->rate;
    $phist->oldlevel = $oldlevel;
    $phist->action = 'Add';
    $phist->remark = $req->remark;
    $phist->save();

    return redirect(route('ps.list', ['staff_id' => $req->staff_id]))
      ->with([
        'alert' => 'New skill added',
        'a_type' => 'success'
      ]);


  }


  public function detail(Request $req){
    if($req->filled('psid')){
      $ps = PersonalSkillset::find($req->psid);
      if($ps){
        // check who is the current user
        $sid = $req->user()->id;
        $isvisitor = false;
        $isboss = false;
        if($sid != $ps->staff_id){
          $isvisitor = true;
          $isboss = \App\common\UserRegisterHandler::isInReportingLine($ps->staff_id, $sid);
        }

        $owner = User::find($ps->staff_id);

        return view('staff.psdetail', [
          'ps' => $ps,
          'owner' => $owner,
          'isvisitor' => $isvisitor,
          'isboss' => $isboss
        ]);
      } else {
        return redirect(route('ps.list'));
      }


    } else {
      return redirect(route('ps.list'));
    }

  }

  public function modify(Request $req){
    $newstatus = 'C';

    $ps = PersonalSkillset::find($req->psid);
    if($ps){
      if($req->user()->id != $ps->staff_id){
        // added by someone else
        if(\App\common\UserRegisterHandler::isInReportingLine($ps->staff_id, $req->user()->id)){
          // updated by boss
          $newstatus = 'A';
        } else {
          // not validly added
          return redirect()->back()->with([
            'alert' => 'You are not allowed to add skill for this person',
            'a_type' => 'danger'
          ]);
        }
      }

      if($req->filled('rate')){

      } else {
        return redirect()->back()->withInput()->with([
          'alert' => 'Please select the competency level',
          'a_type' => 'danger'
        ]);
      }

      $oldlevel = $ps->level ?? 0;
      $newlevel = $req->rate;
      $haction = 'Update';

      if($req->action == 'A'){
        $newstatus = 'A';
        $haction = 'Approve';
      } if($req->action == 'Y'){
        $newstatus = 'A';
        $haction = 'Accept';
      } elseif ($req->action == 'R') {
        $newstatus = 'R';
        $haction = 'Reject';
        $newlevel = 0;
      } elseif ($req->action == 'C') {
        if($newlevel == $oldlevel){
          $haction = "Comment";
          $newstatus = "x";
        } else {
          if($newstatus == 'C'){
            $haction = 'Update';
          } else {
            $haction = $newlevel < $oldlevel ? 'Downgraded' : "Upgraded";
          }
        }

      } elseif ($req->action == 'D') {
        $newstatus = 'D';
        $haction = 'Delete';
        $newlevel = 0;
      }

      if($newstatus != 'x'){
        $ps->level = $newlevel;
        if($newstatus == 'A'){
          $ps->prev_level = $newlevel;
        }

        $ps->status = $newstatus;
        $ps->save();
      }

      // add the history
      $phist = new PersSkillHistory;
      $phist->personal_skillset_id = $ps->id;
      $phist->action_user_id = $req->user()->id;
      $phist->newlevel = $newlevel;
      $phist->oldlevel = $oldlevel;
      $phist->action = $haction;
      $phist->remark = $req->remark ?? '';
      $phist->save();

      if($req->action == 'D' || $req->action == 'Y'){
        return redirect(route('ps.list', ['staff_id' => $ps->staff_id]))
          ->with([
            'alert' => 'Skill updated',
            'a_type' => 'success'
          ]);
      }

      return redirect(route('ps.detail', ['psid' => $req->psid]))
        ->with([
          'alert' => 'Skill updated',
          'a_type' => 'success'
        ]);

    } else {
      return redirect(route('staff'));
    }

  }

  public function addexp(Request $req){

    // dd($req->all());

    if($req->user()->id != $req->staff_id){
      if(\App\common\UserRegisterHandler::isInReportingLine($req->staff_id, $req->user()->id)){
      } else {
        // added by someone not relevant
        return redirect()->back()->with([
          'alert' => 'You are not allowed to modify entry for this person',
          'a_type' => 'danger'
        ]);
      }
    }

    // validate total percentage
    // $totalp = 0;
    // foreach($req->roles as $ap){
    //   $totalp += $ap;
    // }

    // if($totalp != 100){
    //   return back()->withInput()->with([
    //     'alert' => 'Total percentage is not 100',
    //     'a_type' => 'danger'
    //   ]);
    // }

    // $rolemap = collect($req->roles)->map(function ($i) {
    //     return ['perc' => $i];
    // });
    $rolemap = $req->roles;

    $user = User::find($req->staff_id);
    if($user){

      // check for existing exp
      $existing = Involvement::where('user_id', $user->id)
        ->where('bau_experience_id', $req->bexpid)->first();

      $cur_tot_perc = $user->Involvements->sum('perc');

      if($existing){
        // tolak perc asal sebelum kira semula
        $future_tot_perc = $cur_tot_perc + $req->sysperc - $existing->perc;
        if($future_tot_perc > 100){
          return back()->withInput()->with([
            'alert' => 'Total percentage will exceed 100%',
            'a_type' => 'danger'
          ]);
        }

        $existing->roles()->sync($rolemap);
        $existing->perc = $req->sysperc;
        $existing->save();

        return redirect(route('ps.exps', ['staff_id' => $req->staff_id]))
          ->with([
            'alert' => 'Involvement roles updated',
            'a_type' => 'info'
          ]);
      } else {
        $future_tot_perc = $cur_tot_perc + $req->sysperc;
        if($future_tot_perc > 100){
          return back()->withInput()->with([
            'alert' => 'Total percentage will exceed 100%',
            'a_type' => 'danger'
          ]);
        }

        $neu = new Involvement;
        $neu->user_id = $user->id;
        $neu->added_by = $req->user()->id;
        $neu->bau_experience_id = $req->bexpid;
        $neu->perc = $req->sysperc;
        $neu->save();
        $neu->roles()->sync($rolemap);

        return redirect(route('ps.exps', ['staff_id' => $req->staff_id]))
          ->with([
            'alert' => 'New involvement added',
            'a_type' => 'success'
          ]);
      }

    } else {
      return redirect(route('staff'));
    }
  }

  public function editexp(Request $req){

  }

  public function delexp(Request $req){

    if($req->user()->id != $req->uid){
      if(\App\common\UserRegisterHandler::isInReportingLine($req->uid, $req->user()->id)){
      } else {
        // added by someone not relevant
        return redirect()->back()->with([
          'alert' => 'You are not allowed to modify entry for this person',
          'a_type' => 'danger'
        ]);
      }
    }

    $exp = Involvement::find($req->beid);
    if($exp){
      $exp->delete();
      return redirect(route('ps.exps', ['staff_id' => $req->uid]))
        ->with([
          'alert' => 'Involvement removed',
          'a_type' => 'secondary'
        ]);
    } else {
      return redirect(route('ps.exps', ['staff_id' => $req->uid]))
        ->with([
          'alert' => 'Involvement no longer in the list',
          'a_type' => 'warning'
        ]);
    }
  }

  public function pendingapprove(Request $req){
    // dd($req->user()->report_to);
    $mypersno = $req->user()->persno;

    $subsids = User::where('report_to', $mypersno)->where('status', 1)->pluck('id');
    $pslist = PersonalSkillset::whereIn('staff_id', $subsids)
      ->whereIn('status', ['N', 'C'])->get();

    return view('staff.skillstaffpendapprove', [
      'pss' => $pslist
    ]);
  }

  public function reportInvolvements(Request $req){
    // check if compgroup id is provided
    if($req->filled('cgid')){
      $cgrp = CompGroup::find($req->cgid);
    } else {
      // get current user's group
      $cgrp = UserHelper::GetUserGroup($req->user()->id);
    }

    if($cgrp){
      // current user have comp group
    } else {
      abort(403);
    }

    // prep the data
    $unitsdata = [];
    $unitsummlabel = [];
    $unitsummdata = [];

    $pielabel = ['N/A', 'x = 0', '0 < x < 50', '50 <= x < 75', '75 <= x < 100', 'x = 100'];
    $piebgcolor = [
      'rgba(0, 0, 0, 0.6)',
      'rgba(255, 0, 0, 0.6)',
      'rgba(200, 44, 0, 0.6)',
      'rgba(200, 200, 25, 0.6)',
      'rgba(0, 0, 255, 0.6)',
      'rgba(0, 255, 0, 0.6)'
    ];

    foreach($cgrp->Members as $unit){
      $stafflist = $unit->Staffsvitu;
      // first get the total staff count for this unit
      $staffcount = $stafflist->count();
      if($staffcount == 0){
        // skip zero divs
        continue;
      }
      
      $noentry = 0;
      $l50 = 0;
      $l75 = 0;
      $l100 = 0;
      $e100 = 0;
      $e0 = 0;

      // then check the entry for each staff
      foreach ($stafflist as $key => $onestaff) {
        $entrycount = 0;
        $sumexperc = 0;
        foreach ($onestaff->Involvements as $exp) {
          $entrycount++;
          $sumexperc += $exp->perc;
        }

        // increase the corrent buckets
        if($entrycount == 0){
          $noentry++;
        } else {
          if($sumexperc == 100){
            $e100++;
          } elseif($sumexperc == 0){
            $e0++;
          } elseif($sumexperc < 50){
            $l50++;
          } elseif($sumexperc < 75){
            $l75++;
          } else { // < 100
            $l100++;
          }
        }
      }

      // update the graph data
      $bbt_pie = app()->chartjs
           ->name('bbytag' . $unit->id)
           ->type('pie')
           ->size(['width' => 400, 'height' => 400])
           ->labels($pielabel)
           ->datasets([
               [
                   'label' => '% total involvements',
                   'backgroundColor' => $piebgcolor,
                   'data' => [$noentry, $e0, $l50, $l75, $l100, $e100]
               ]
           ])
           ->options([
             'responsive' => true,
             // 'maintainAspectRatio' => true,
             'tooltips' => [
               'mode' => 'index',
               'intersect' => true,
             ],
             'hover' => [
               'mode' => 'nearest',
               'intersect' => true,
             ],
           ]);

      array_push($unitsdata, [
        'name' => $unit->pporgunitdesc,
        'graph' => $bbt_pie
      ]);
      $unitsummlabel[] = $unit->pporgunitdesc;

      // update the summary
      if($staffcount == 0){
        $perc = 0;
      } else {
        $perc = round(($staffcount - $noentry) / $staffcount * 100);
      }
      $unitsummdata[] = $perc;
    }

    $tvt_height = 100 + count($unitsummlabel) * 15;
    // the summary graph
    $tvt_graph = app()->chartjs
         ->name('tvt_graph')
         ->type('horizontalBar')
         ->size(['width' => 800, 'height' => $tvt_height])
         ->labels($unitsummlabel)
         ->datasets([
             [
                 "label" => "% Completion",
                 'backgroundColor' => 'rgba(75, 192, 192, 0.6)',
                 'data' => $unitsummdata
             ]
         ])
         ->options([
           'responsive' => true,
           'maintainAspectRatio' => true,
           'tooltips' => [
             'mode' => 'index',
             'intersect' => false,
           ],
           'hover' => [
             'mode' => 'nearest',
             'intersect' => true,
           ],
           'scales' => [
             'xAxes' => [[
               'scaleLabel' => [
                 'display' => true,
                 'LabelString' => 'Activity Tag',
               ]
             ]],
             'yAxes' => [[
               'scaleLabel' => [
                 'display' => true,
                 'LabelString' => 'Sum Hours',
               ]
             ]]
           ]
         ]);


    return view('report.involvesumm', [
      'cg' => $cgrp,
      'units' => $unitsdata,
      'summgraph' => $tvt_graph
    ]);

  }

}
