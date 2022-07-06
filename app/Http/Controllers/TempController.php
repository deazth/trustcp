<?php

namespace App\Http\Controllers;

use Session;
use Illuminate\Http\Request;
use App\Shared\LdapHelper;
use App\Models\User;
use App\Models\CommonConfig;
use DB;
use Illuminate\Support\Facades\Auth;

class TempController extends Controller
{
    // use AuthenticatesUsers;

    public function loadDummyUser(Request $req)
    {
        return LdapHelper::loadDummyAccount(3000);
    }

    public function helloKitten($uat)
    {
        $check1 = false;
        $ccEnv = CommonConfig::where('key', 'env2')->where('value', 'uat')->first();
        $uat = CommonConfig::where('key', 'uat')->where('value', $uat)->first();
        if ($ccEnv && $uat) {
            $check1 = true;
        };
        if ($check1) {
            return view('staff.loginoffline', []);
        } else {
            abort(404);
        }
    }

    public function login(Request $req)
    {
        $ccEnv = CommonConfig::where('key', 'env2')->where('value', 'uat')->first();

        if ($ccEnv) {
            $this->validate($req, ['staff_no' => 'required', 'password' => 'required',]);
            $pwd = $req->password;
            $pwdCheck = CommonConfig::where('key', 'uatpwd')->where('value', $pwd)->first();

            if ($pwdCheck) {
                $staff_no = str_replace(' ', '', strtoupper(trim($req->staff_no)));





                $user = User::where(DB::raw('REPLACE(UPPER(TRIM(staff_no))," ","")'), $staff_no)->first();

                //$cuser = User::where(DB::raw('UPPER(staff_no)'), $staff_no)->first();
                if ($user) {
                    $username = $user->staff_no;
                    //dd($user);
                    $field = 'staff_no';
                    if (strpos($username, '@') !== false) {
                        $field = 'email';
                    }
                    //session(['staffdata' => $user]);
                    session(['uat' => true]);
                    //return view(backpack_view('dashboard'), $this->data);
                    backpack_auth()->login($user);
                    $user['token'] = $user->createToken('trUSt')->accessToken;
                    return redirect(route('backpack.dashboard'));
                } else {
                    return abort(401);
                }
            } else {
                return response(trans('backpack::base.unauthorized'), 401);
            };
        }
        return abort(404);
    }

    public function eraLogin($erakey)
    {
        $client = new \GuzzleHttp\Client(); //GuzzleHttp\Client
        $url = "http://10.0.2.2:3000/api/get/useridera/" . $erakey;

        $options = [

            'strict'          => false,
        ];
        $response = $client->request('GET', $url, $options);
        $content = json_decode($response->getBody(), true);
        $staffno = $content['staffno'];


        $user = User::where(DB::raw('REPLACE(UPPER(TRIM(staff_no))," ","")'), $staffno)->first();

        //$cuser = User::where(DB::raw('UPPER(staff_no)'), $staff_no)->first();
        if ($user) {
            $username = $user->staff_no;
            //dd($user);
            $field = 'staff_no';
            if (strpos($username, '@') !== false) {
                $field = 'email';
            }
            //session(['staffdata' => $user]);
            session(['uat' => true]);
            //return view(backpack_view('dashboard'), $this->data);
            backpack_auth()->login($user);
            $user['token'] = $user->createToken('trUSt')->accessToken;
            return redirect(route('backpack.dashboard'));
        } else {
            return abort(401);
        }
    }
}
