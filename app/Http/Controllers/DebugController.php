<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\common\LdapHelper;
use App\common\UserRegisterHandler;
use App\Models\User;

class DebugController extends Controller
{
  public static function LdapLogin(Request $req){
    return LdapHelper::doLogin($req->u, $req->p);
  }

  public static function LdapFetch(Request $req){
    return LdapHelper::fetchUser($req->u);
  }

  public static function UserLogin(Request $req){
    // return UserRegisterHandler::userLogin($req->u, $req->p);
    $username = $req->u;
    $password = $req->p;

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
        $ldapresp = UserRegisterHandler::callLoginApi($username, $password);
        //dd(gettype($ldapresp));


     } else {
        $ld = LdapHelper::doLogin($username, $password);
        $ldapresp = (object) $ld;
        $ldapresp->data = (object)$ld['data'];
       //dd(gettype($ldapresp));
      }

      dd($ldapresp);

    }

    dd('failed');

  }


}
