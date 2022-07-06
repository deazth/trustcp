<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{

  // validate token
  public function validateToken(Request $req){
    $input = app('request')->all();

		$rules = [
			'staff_no' => ['required']
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

    $luser = $req->user();

    if(strcasecmp($luser->staff_no, $req->staff_no) == 0){
      return $this->respond_json(200, 'Success', ['user' => $luser]);
    }

    return $this->respond_json(403, 'Missmatched token', []);

  }


}
