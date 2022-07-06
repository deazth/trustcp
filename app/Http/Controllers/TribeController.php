<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\common\TribeApiCallHandler;
use App\common\CommonHelper;

class TribeController extends Controller
{

    public function index(Request $req)
    {
        $user = $req->user();
        $token = TribeApiCallHandler::vt($user);
        $baseuri  = env('TRIBE_URL');
        $baseurl  = env('TRIBE_URL');
        $exturi   = '/tribe/api/system/login';
        $options = [

            'verify' => false,
            'headers' => ['Authorization' => $token],
            'allow_redirects' => true,
            'strict'          => false,
        ];
        $headers =  ['Authorization' => $token];
        $reclient = new \GuzzleHttp\Client(["base_uri" => $baseuri]);
        //$request = $reclient->request('GET', $exturi, $options);
        // dd($request);
        /*
        switch ($request->getStatusCode()) {
        case 200:
           // echo("here");
           return Redirect::to($baseurl.$exturi, '302', $options);
        break;
        case 302:
           return redirect($baseurl.$exturi, '302', $options);
        break;
        default:
         return $request;

         }
         */

        //return Redirect::to($baseurl.$exturi, '302', $options);
        //return redirect($baseurl.$exturi."/".$token, '302',[]);

        //return $request;
        //return redirect()->away($baseurl.$exturi, '302',$options );
        //return request('GET', $exturi, $options);
        //return redirect()->away($baseurl);
        return $reclient->request('GET', $exturi, $options)->getBody();
    }

    public function detect(Request $req)    
    {
        $user = $req->user();
        $cc = CommonHelper::GetCConfig('detect', '1');
        $token = TribeApiCallHandler::vt($user);
       // dd($cc);

        if ($cc == 1) {

  
            
            $baseuri  = env('TRIBE_URL');
            $baseurl  = env('TRIBE_URL');
            $exturi   = '/detect/api/system/login';
            $options = [

                'verify' => false,
                'headers' => ['Authorization' => $token],
                'allow_redirects' => true,
                'strict'          => false,
            ];
            $headers =  ['Authorization' => $token];
            $reclient = new \GuzzleHttp\Client(["base_uri" => $baseuri]);
            return $reclient->request('GET', $exturi, $options)->getBody();
        }
        else{
            return view('tribe.detect', ['token' => '']);
        }
    }

    public function Home(Request $req)
    {
        return view('tribe.home', ['token' => '']);
    }

    public function view()
    {
        return 'abc';
    }

    public function tribeAss(Request $req)
    {
        $user = $req->user();
        $assigments = TribeApiCallHandler::getTribeAssigment($user);
        return $assigments;
    }




    public function vt(Request $req)
    {

        $user = $req->user();
        $token = TribeApiCallHandler::vt($user);

        return $token;
    }
}
