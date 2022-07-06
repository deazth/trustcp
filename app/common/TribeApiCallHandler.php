<?php

namespace App\common;

use GuzzleHttp\Client;

class TribeApiCallHandler
{
    public static function LogOut($token)
    {
        $baseuri = env('TRIBE_URI') ;
        $reclient = new \GuzzleHttp\Client(["base_uri" => $baseuri]);

        $options = [
       'verify' => false,
       'headers' => ['Authorization' => $token],
       'connect_timeout' => 5,
       'timeout' => 5
    ];
        sleep(2);
        $request = $reclient->request('GET', '/tribe/api/system/logout/', $options);
    }

    public static function vt($user)
    {
      
       $token = $user->createToken('tribe')->accessToken;
       return $token;
      
    }

    public static function getTribeAssigment($user)
    {
        //prepare all the variables
        $baseuri  = env('TRIBE_URI') ;
        $exturi   = '/tribe/api/system/assignment/';

        $token    = TribeApiCallHandler::vt($user);
        $persno   = $user->persno;
        $exturi   = $exturi.$persno;
        $options = [
      'verify' => false,
      'headers' => ['Authorization' => $token],
      'connect_timeout' => 10,
      'timeout' => 10
   ];

        $ret = [];
        try {
            $reclient = new \GuzzleHttp\Client(["base_uri" => $baseuri]);
    
            $request = $reclient->request('GET', $exturi, $options)->getBody();
    
            $response = response()->make($request, 200)->content();
            $ret = json_decode($response, true);
        } catch (\Exception $e) {
          $ret = [];
        }

        return [ "assigments" => $ret , "token"=>$token];
    }
}
