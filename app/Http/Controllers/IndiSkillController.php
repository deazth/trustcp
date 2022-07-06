<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersJobType;
use App\Models\User;
use App\common\UserHelper;
use App\common\CommonHelper;

class IndiSkillController extends Controller
{
  public function MyJobCatForm(Request $req){
    $user = backpack_user();

    if($req->filled('uid')){
      $user = User::findOrFail($req->uid);
    }

    $perm = CommonHelper::UserCanAccessUser(backpack_user()->id, $user->id);
    $pjt = PersJobType::all();
    $dropdown = $pjt->pluck('category', 'id');
    $kerud = app()->make('crud');

    $ui = UserHelper::GetUserInfo($user->id);

    $kerud->addField(
    [
      'name' => 'current_jobcat',
      'label' => 'Current Job Category',
      'type' => 'select2_from_array',
      'options' => $dropdown,
      'attributes' => ['required' => 'required'],
      'value' => $ui->cur_job_type_id,
      'allows_null' => true,
      'wrapper'   => [
        'class'      => 'form-group col-md-6'
      ],
    ]);

    $kerud->addField(
    [
      'name' => 'prefer_jobcat',
      'label' => 'Preferred Job Category',
      'type' => 'select2_from_array',
      'options' => $dropdown,
      'attributes' => ['required' => 'required'],
      'value' => $ui->pref_job_type_id,
      'allows_null' => true,
      'wrapper'   => [
        'class'      => 'form-group col-md-6'
      ],
    ]);

    $kerud->addField(
    [
      'name' => 'user_id',
      'type' => 'hidden',
      'value' => $user->id
    ]);

    $this->data['crud'] = $kerud;
    $this->data['pjt'] = $pjt;
    $this->data['user'] = $user;
    $this->data['perm'] = $perm;

    return view('staff.skill.job_cat', $this->data);
  }

  public function MyJobCatSubmit(Request $req){
    $user = User::findOrFail($req->user_id);
    if(!CommonHelper::UserCanAccessUser(backpack_user()->id, $user->id)){
      CommonHelper::Log403Err(backpack_user(), $user, 'Team last loc', 'perm 0');
      abort(403);
    }

    $ui = UserHelper::GetUserInfo($user->id);
    $ui->cur_job_type_id = $req->current_jobcat;
    $ui->pref_job_type_id = $req->prefer_jobcat;
    $ui->save();

    \Alert::info('Job Category Updated')->flash();

    return redirect()->back();

  }
}
