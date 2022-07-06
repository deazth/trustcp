<?php

namespace App\common;

use App\Models\CommonConfig;
use App\Models\Announcement;
use App\Models\CompGroup;
use App\Models\User;
use App\Models\AreaBooking;
use App\Models\Assistant;

class CommonHelper
{
  public static function GetCConfig($key, $default = ''){
    $cc = CommonConfig::where('key', $key)->first();
    if($cc){

    } else {
      $cc = new CommonConfig;
      $cc->key = $key;
      $cc->value = $default;
      $cc->save();
    }

    return $cc->value;
  }

  public static function GetAnnouncements(){
    $today = date('Y-m-d');
    $announcelist = Announcement::whereDate('start_date', '<=', $today)
      ->whereDate('end_date', '>=', $today)->get();

    return $announcelist;
  }

  public static function UserCanAccessGroup($group_id, $user_id){
    $uz = User::findOrFail($user_id);
    // todo: maybe allow for admin
    if($uz->hasPermissionTo('diary-admin')){
      return true;
    }

    // check if this  user is the caretaker
    $cg = CompGroup::find($group_id);
    if($cg){
      $nom = $cg->Caretakers->where('id', $user_id);
      // dd($nom);
      return sizeof($nom) != 0;
    }

    return false;
  }

  public static function UserIsCtakerFor($viewer, $target_user){
    // first check if this user is in any comp group
    $unit = $target_user->Division;
    if($unit){
      $cg = $unit->comp_group;
      if($cg){
        $nom = $cg->Caretakers->where('id', $viewer->id);
        // dd($nom);
        return sizeof($nom) != 0;
      } else {
        // this user doesnt belong to any group. meaning it wont have any caretaker
        return false;
      }
    } else {
      // doesnt have a valid unit
      return false;
    }
  }


  /**
    return values:
    0 / false - not allowed
    1 - self
    2 - line superior
    3 - caretaker
    4 - diary-admin
    5 - super-admin
  */
  public static function UserCanAccessUser($viewer_id, $target_user_id){
    if($viewer_id == $target_user_id) return 1;

    $uz = User::findOrFail($viewer_id);
    if($uz->hasPermissionTo('diary-admin')){
      return 4;
    }

    if($uz->hasPermissionTo('super-admin')){
      return 5;
    }

    $tu = User::findOrFail($target_user_id);

    // check for caretaker
    if(self::UserIsCtakerFor($uz, $tu)){
      return 3;
    }

    //return self::UserIsSuper($curuser, $targetboss);
    return self::UserIsSuper($uz, $tu) ? 2 : 0;
  }

  private static function UserIsAssistant($current_user, $target_user){
    $gotassist = Assistant::where('user_id', $target_user->id)->where('assist_id', $current_user->id)->first();

    return $gotassist ? true : false;
  }

  private static function UserIsSuper($curuser, $targetuser){
    // current user is the super
    if($targetuser->report_to == $curuser->persno){
      return true;
    }

    // check if current user is the assistant of the target user
    if(self::UserIsAssistant($curuser, $targetuser)){
      return true;
    }

    if($targetuser->report_to){
      $targetboss = User::where('persno', $targetuser->report_to)->first();
      if($targetboss){
        return self::UserIsSuper($curuser, $targetboss);
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  public static function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
  }

  public static function SBGetPendingBookingCount(){
    return AreaBooking::where('status', 'Pending SB')->count();
  }

  public static function SBGetTodayBookingReqCount(){
    $now = new \Carbon\Carbon;
    return AreaBooking::where('status', 'Active')
      ->where('has_extra_req', 1)
      ->whereDate('start_time', $now->toDateString())
      ->where('start_time', '>', $now->toDateTimeString())
      ->count();
  }

  public static function Log403Err($cuser, $target_user, $action, $remark){
    \Illuminate\Support\Facades\Log::warning('403 user:' . $cuser->staff_no .
      ', target:' . ($target_user ? $target_user->staff_no : 'global') . ', action:' . $action . ', detail:' . $remark);
  }
}
