<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\common\UserRegisterHandler;
use App\common\LdapHelper;

class LoginController extends Controller
{

	/**
	*	authenticate the provided credential with ldap
	*/
	function doLogin(Request $req){

		set_error_handler(array($this, 'errorHandler'));

		$input = app('request')->all();

		$rules = [
			'staff_no' => ['required'],
			'password' => ['required']
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}
		$push = $req->filled('pnid') ? $req->pnid : '';

		$logresp = UserRegisterHandler::userLogin($req->staff_no, $req->password, 0, $push);

		// dd($logresp);

		if($logresp['msg'] == 'failed' || $logresp['msg'] == 'Invalid credentials'){
			return $this->respond_json(403, 'Invalid Credential', []);
		} elseif($logresp['ecode'] != 200){
			return $this->respond_json($logresp['ecode'], $logresp['msg'], []);
		} elseif ($logresp['msg'] == 'email') {
			return $this->respond_json(403, 'Pending Email Validation', $logresp);
		} elseif ($logresp['msg'] == 'pending') {
			return $this->respond_json(403, 'Pending Admin Approval', $logresp);
		} elseif ($logresp['msg'] == 'Div not allowed') {
			return $this->respond_json(403, 'Your division is not yet registered', $logresp);
		}

		return $this->respond_json(200, 'OK', $logresp);
	}

	function doExternalLogin($username, $password){

	}

	function justLogin(Request $req){
		$input = app('request')->all();

		$rules = [
			'staff_no' => ['required'],
			'password' => ['required']
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

		$username = $req->staff_no;
		$password = $req->password;

		return LdapHelper::doLogin($username, $password);
	}

	// to be called by API
	function getUserInfo(Request $req){
		// first, validate the input
		$rules = [
			'key' => ['required'],
			'type' => ['required']
		];

		$val = $this->validate($rules);

		if($val['code'] != 200){
			return $val;
		}

		return LdapHelper::fetchUser($req->key, $req->type);
	}
}
