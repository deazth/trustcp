<?php

namespace App\Api\V1\Controllers\Tribe;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
   public function getDetail(Request $req){
    $usr= User::where('id',$req->user()->id)->get(['name','email','staff_no','persno']);
    //return $req->user()
    return $usr;
    }

    public function validateToken(Request $req){    
      $user = $req->user();
      //$token = $user->createToken('tribe')->accessToken;
      // $token = $user->createToken('trUSt')->accessToken;
      // $token = $user->tokens();
      //$token = Auth::user()->token();
     
       return $req;
    }



}
