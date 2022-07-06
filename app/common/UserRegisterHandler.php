<?php

namespace App\common;

use App\Models\User;
use App\Models\Unit;
use App\Models\SubUnit;
use App\Models\Attendance;
use App\Models\LocationHistory;
use App\Mail\VerifyMail;
use \Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
//use App\Api\V1\Controllers\LdapHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UserRegisterHandler
{
  public static function userLogin($username, $password, $isweb = 0, $pushid = ''){
    // first, check if this user exists
    $errormsg = "success";
    $ecode = 200;
    $field = 'staff_no';

    if(strpos($username, '@') !== false){
      $field = 'email';
    }

    $user = User::where($field, $username)->first();

    if($user && $user->isvendor == 1 && isset($user->partner_id)){
      // is vendor. do normal login

      if($user->verified == 0){
        $errormsg = "email";
      } elseif($user->status == 1){
        if(backpack_auth()->attempt([
          $field => $username,
          'password' => $password
        ])){
          $user->status = 1;
          $user->unit = $user->divName();
          $user->save();
        } else {
          $errormsg = "failed";
        }
      } else{
        $errormsg = "pending";
      }


    } else {

      if($user && $field == 'email'){
        $username = $user->staff_no;
      }

      // user not exist or is TM staff. Try login through LDAP
       //dd(config('APP_ENV'));

     if(env('APP_ENV') == 'local'){
        $ldapresp = self::callLoginApi($username, $password);
        //dd(gettype($ldapresp));


     } else {
        $ld = LdapHelper::doLogin($username, $password);
        $ldapresp = (object) $ld;
        $ldapresp->data = (object)$ld['data'];
       //dd(gettype($ldapresp));


      }

      if($ldapresp->code != 200){
        // bad ldap login

        $errormsg = $ldapresp->msg;
        $ecode = 500;
  		} else {
        // update the data back to the User
        $user = UserRegisterHandler::updateFromLdap($ldapresp);

        // update the division info
        UserRegisterHandler::updateUserDiv($user);

        if($isweb == 1){
          backpack_auth()->login($user);
        }

      }
    }

    if($errormsg == 'success' && $isweb == 0){
      if($pushid != ''){
        $user->pushnoti_id = $pushid;
      }

      $user->save();
      $user['token'] = $user->createToken('trUSt')->accessToken;
    }

    return [
      'user' => $user,
      'msg' => $errormsg,
      'ecode' => $ecode
    ];
  }

  /**
   in case cannot access the page while in securevpn
   */
  public static function callLoginApi($username, $password){

    $reclient = new \GuzzleHttp\Client(["base_uri" => "https://trustbp.tm.com.my"]);
    $options = [
      'query' => ['staff_no' => $username, 'password' => $password]
    ];

    $request = $reclient->request('POST', 'api/UserJustLogin', $options)->getBody()->getContents();
    return json_decode($request);
  }

  private static function createOneWeekDF($staff_id){
    $cdate = new Carbon();
    $ldate = new Carbon();

    $daterange = new \DatePeriod(
      $cdate->subDays(7),
      \DateInterval::createFromDateString('1 day'),
      $ldate
    );

    // dd($daterange);

    foreach($daterange as $indate){
      GDWActions::GetDailyPerfObj($staff_id, $indate);
    }

  }

  private static function updateFromLdap($ldapresp){
    // get the username
		$ldapstaffid = $ldapresp->data->STAFF_NO;
    $ldappersno = $ldapresp->data->PERSNO;
    $staffdata = false;

    // find from User table
    if(isset($ldappersno) && strlen($ldappersno) > 0){
      $staffdata = User::where('persno', $ldappersno)->first();
    }

    if($staffdata){
      $staffdata->staff_no = $ldapstaffid;
    } else {
      $staffdata = User::where('staff_no', $ldapstaffid)->first();
      if($staffdata){
        $staffdata->persno = $ldappersno;
      }
    }

		if($staffdata){
      // fix for leasing staffs
      if(isset($staffdata->lob) == false || $staffdata->lob == 0){
        $staffdata->lob = $ldapresp->data->DEPARTMENT;
    		$staffdata->unit = $ldapresp->data->UNIT;
    		$staffdata->subunit = $ldapresp->data->SUBUNIT;
        $staffdata->report_to = $ldapresp->data->SP_PERSNO;
      }
		} else {
			// new data. create it
			$staffdata = new User;
			$staffdata->staff_no = $ldapstaffid;
			$staffdata->status = 1;
			$staffdata->role = 3;
  		$staffdata->name = $ldapresp->data->NAME;
  		$staffdata->lob = $ldapresp->data->DEPARTMENT;
  		$staffdata->unit = $ldapresp->data->UNIT;
  		$staffdata->subunit = $ldapresp->data->SUBUNIT;
      $staffdata->report_to = $ldapresp->data->SP_PERSNO;
      $staffdata->report_to_id = $ldapresp->data->SP_TRUSTID;

      if($ldapresp->data->DEPARTMENT == 123){
        $staffdata->isvendor = 1;
      } else {
        $staffdata->isvendor = 0;
        $staffdata->persno = $ldappersno;
      }
		}

    // just in case, if vendor
    if($staffdata->isvendor == 1){
      $staffdata->lob = 123;
      $staffdata->unit = $ldapresp->data->UNIT;
      $staffdata->subunit = $ldapresp->data->SUBUNIT;
      if(isset($staffdata->report_to)){
        // to prevent from overwriting existing vendor assignment
      } else {
        $staffdata->report_to = $ldapresp->data->SP_PERSNO;
      }

    }

		$tmobile = strlen($ldapresp->data->MOBILE_NO) > 13 ? substr($ldapresp->data->MOBILE_NO, 0, 13) : $ldapresp->data->MOBILE_NO;
		if(substr($tmobile, 0, 1) === '0'){
			$tmobile = '6' . $tmobile;
		}

		// overwrite with ldap data
		$staffdata->email = $ldapresp->data->EMAIL;
    $staffdata->new_ic = $ldapresp->data->NIRC;
    if(!isset($staffdata->job_grade)){
      // only overwrite job_grade if there's no previous grade
      if(isset($ldapresp->data->JOB_GRADE)){
        $staffdata->job_grade = $ldapresp->data->JOB_GRADE;
      } else {
        $staffdata->job_grade = 1;
      }
    }


		$staffdata->mobile_no = $tmobile;
		$staffdata->save();

    if(!isset($staffdata->lob)){
      $bus = $staffdata->Boss;
      $staffdata->unit = $bus->unit;
      $staffdata->unit_id = $bus->unit_id;
      $staffdata->lob = $bus->lob;
      $staffdata->save();
    }

    return $staffdata;
  }


  private static function inactivateUser($staff_id){
    $user = User::find($staff_id);
    $user->status = 0;
    $user->save();

    return $user;

  }

  public static function updateUserDiv($staffdata){
    // first, check if current division exist
    if($staffdata->lob == 'Vendors'){
      return;
    }
    //
    // if($staffdata->unit == 'TM ELIT'){
    //   if(isset($staffdata->unit_id)){
    //     return;
    //   }
    //
    //   $div = Unit::where('pporgunit', 124)->first();
    //   $staffdata->unit_id = $div->id;
    //   $staffdata->save();
    //   return;
    // }

    $div = Unit::where('pporgunit', $staffdata->lob)->first();
    if($div){
      //
    } else {
      // create this division
      $div = new Unit;
      $div->pporgunit = $staffdata->lob;
      $div->pporgunitdesc = $staffdata->unit;
      $div->lob = 3000;
      $div->save();
    }

    // update the unit id for this user
    $staffdata->unit_id = $div->id;
    $staffdata->save();
  }

  public static function attClockIn(Request $req){

    $user = User::find($req->staff_id);

    if($req->filled('reason')){
      $reason = $req->reason;
      $reasonco = $req->reason;
    } else {
      $reasonco = 're check-in';
      $reason = '';
    }

    $lat = 0;
    $long = 0;

    if($req->filled('lat')){
      $lat = $req->lat;
    }

    if($req->filled('long')){
      $long = $req->long;
    }

    if($req->filled('in_time')){
      $intime = $req->in_time;
    } else {
      $intime = Carbon::now();
    }

    if($req->filled('address')){
      $address = $req->address;
    } else {
      $address = '';
    }

    // double check if there's existing clock in
    if(isset($user->curr_attendance)){
      // clock out that existing attendance
      UserRegisterHandler::attClockOut($user->id, $intime, $lat, $long, $reasonco, $address);
    }

    $lochist = new LocationHistory;
    $lochist->user_id = $user->id;
    $lochist->latitude = $lat;
    $lochist->longitude = $long;

    if($reason != ''){
      $lochist->note = $reason;
    }

    if($address != ''){
      $lochist->address = $address;
    }

    $lochist->action = 'Check-in';
    $lochist->save();

    // update df start work time
    UserHelper::updateDfStartWorkTime($user->id, $intime);

    // create new attendance
    $att = new Attendance;
    $att->user_id = $user->id;
    $att->in_latitude = $lat;
    $att->in_longitude = $long;
    $att->clockin_time = $intime;
    $att->isvendor = $user->isvendor;

    if($user->isvendor == 1){
      $att->division_id = $user->partner_id;
    } else {
      $att->division_id = $user->unit_id;
    }

    $att->save();

    $user->curr_attendance = $att->id;
    $user->last_location_id = $lochist->id;
    $user->save();

    return $user;

  }

  public static function attUpdateLoc($staff_id, $lat, $long, $reason, $address = ''){
    $user = User::find($staff_id);
    if($user){
      $lochist = new LocationHistory;
      $lochist->user_id = $user->id;
      $lochist->latitude = $lat;
      $lochist->longitude = $long;

      if($reason != ''){
        $lochist->note = $reason;
      }

      if($address != ''){
        $lochist->address = $address;
      }

      $lochist->action = 'Update Location';
      $lochist->save();

      UserHelper::updateDfStartWorkTime($user->id, $lochist->created_at);

      $user->last_location_id = $lochist->id;
      $user->save();
    }
  }

  public static function attClockOut($staff_id, $time, $olat, $olong, $reason, $address = ''){
    $user = User::find($staff_id);
    if(isset($user->curr_attendance)){
      $curratt = Attendance::find($user->curr_attendance);
      $curratt->clockout_time = $time;
      $curratt->out_latitude = $olat;
      $curratt->out_longitude = $olong;
      $curratt->out_reason = $reason;

      // check for overnight
      $indate = Carbon::parse($curratt->clockin_time);
      $outdate = Carbon::parse($curratt->clockout_time);

      if($indate->format('Ymd') != $outdate->format('Ymd')){
        // overnight checkout
        $overnightime = Carbon::parse($outdate->format('Y-m-d'));
        $curratt->overnight = true;
        $curratt->overnight_time = $overnightime;

        $curratt->minute_work = $overnightime->diffInMinutes($indate);
        $curratt->minute_work_overnight = $outdate->diffInMinutes($overnightime);

      } else {
        $curratt->minute_work = $outdate->diffInMinutes($indate);
      }

      $curratt->save();

      $lochist = new LocationHistory;
      $lochist->user_id = $staff_id;
      $lochist->latitude = $olat;
      $lochist->longitude = $olong;

      if($reason != ''){
        $lochist->note = $reason;
      }

      if($address != ''){
        $lochist->address = $address;
      }

      $lochist->action = 'Check-out';
      $lochist->save();

      $user->curr_attendance = null;
      $user->last_location_id = $lochist->id;
      $user->save();
    } else {
      $curratt = 'Not currently checked-in';
    }

    return $curratt;
  }

  public static function attLocHistory($staff_id){
    $items = LocationHistory::where('user_id', $staff_id)
      ->latest('id')->limit(10)->get();

    return $items;
  }

  public static function updateStaffInfoFromJI($staffno, $staffname, $position, $reportingto, $div, $orgunit, $divid, $pporgunit, $persno, $reportpersno){

    $trimmedstaffno = str_replace(' ', '', $staffno);

    // find if this staff is registered
    $user = User::where('staff_no', $trimmedstaffno)->first();
    if($user){

    } else {
      // try to find by persno
      $user = User::where('persno', $persno)->first();
      if($user){
        $user->staff_no = $trimmedstaffno;
      } else {
        $user = new User;
        $user->staff_no = $trimmedstaffno;
        $user->status = 1;
        $user->role = 3;
        $user->verified = true;
      }
    }

    $user->persno = $persno;
    $user->name = $staffname;
    $user->jobtype = $position;
    $user->unit = $div;
    $user->subunit = $orgunit;
    $user->lob = $pporgunit;
    $user->unit_id = $divid;
    $user->report_to = $reportpersno;
    $user->save();

    // delete old subords info
    // Subordinate::where('sub_staff_no', $staffno)->delete();
    // $subobj = new Subordinate;
    // $subobj->superior_name = $reportingto;
    // $subobj->sub_staff_no = $staffno;
    // $subobj->sub_name = $staffname;
    // $subobj->subordinate_id = $user->id;
    //
    // // find superior_id
    // $sup = User::where('name', $reportingto)->first();
    // if($sup){
    //     $subobj->superior_id = $sup->id;
    // } else {
    //   $subobj->superior_id = 0;
    // }
    //
    // $subobj->save();

  }

  public static function getDivision($pporgunit, $divdesc){
    $thatdiv = Unit::where('pporgunit', $pporgunit)->first();
    if($thatdiv){

    } else {
      $thatdiv = new Unit;
      $thatdiv->pporgunit = $pporgunit;
      $thatdiv->allowed = true;
    }

    $thatdiv->pporgunitdesc = $divdesc;
    $thatdiv->save();

    return $thatdiv->id;
  }

  public static function getUnit($pporgunit, $divdesc, $ppsuborg, $unitdesc){
    $thatunit = SubUnit::where('ppsuborg', $ppsuborg)->first();
    if($thatunit){

    } else {
      $thatunit = new SubUnit;
      $thatunit->lob = 3000;
      $thatunit->ppsuborg = $ppsuborg;
    }

    $thatunit->pporgunit = $pporgunit;
    $thatunit->pporgunitdesc = $divdesc;
    $thatunit->ppsuborgunitdesc = $unitdesc;
    $thatunit->save();

    return $thatunit->id;
  }

  public static function isInReportingLine($subs_id, $boss_id){
    $cuser = User::find($subs_id);
    if($cuser){
      // find the owner of report_to persno
      if(isset($cuser->report_to) && $cuser->report_to != 0){
        $superior = User::where('persno', $cuser->report_to)->first();
        if($superior){
          if($superior->id == $boss_id){
            return true;
          } else {
            return UserRegisterHandler::isInReportingLine($superior->id, $boss_id);
          }
        } else {
          return false;
        }

      } else {
        return false;
      }

    } else {
      return false;
    }
  }

  public static function IsCaretaker($user, $staff){
    // check for caretaker
    if($staff->unit_id){
      $grp = $staff->Division->Group;
      if(isset($grp)){
        if($grp->Users->where('id', $user->id)->count() == 0){
          return false;
        }
        return true;
      } else {
        // no group. meaning no caretaker
        return false;
      }
    }
    return false;
  }

  public static function GetUserLdap($staffno){
    $puser = LdapHelper::fetchUser($staffno);
    if($puser['code'] == 200){
      return $puser->data;
    }
    return false;
  }
}
