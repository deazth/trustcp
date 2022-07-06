<?php

namespace App\common;
use App\Models\User;
use App\Models\LocationHistory;



class DashHelper
{
  public static function getLatestLocationHist($user){
    $checkin = LocationHistory::where('user_id',$user->id)->orderBy('created_at','DESC')->orderBy('id', 'DESC')->first();
    return $checkin;

  }





}
