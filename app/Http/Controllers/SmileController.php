<?php

namespace App\Http\Controllers;

use App\common\SmileHelper;
use Illuminate\Http\Request;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Auth;
// use App\McoTravelReq;
// use App\SapLeaveInfo;
use \Carbon\Carbon;
use App\User;
use DB;
use App\Models\HappyReason;
use App\Models\HappyType;
use App\Models\UserInfo;
use App\Models\HappyMeter;
use GuzzleHttp\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Token;
use Laravel\Passport\TokenRepository;
use Illuminate\Support\Facades\Log;
use App\common\UserHelper;


class SmileController extends Controller
{

    public function index(Request $req)
    {
        return view('smile.index', []);
    }
    public function form(Request $req)
    {
        $reasons = HappyReason::where('type_id', $req->type)->get();
        $type = HappyType::where('id', $req->type)->first();

        // dd($req->type,$reasons,$type);
        return view('smile.form', ['reasons' => $reasons,'ty'=>$type]);
    }
    public function submit(Request $req)
    {
        $api_uri = config('custom.era.api_uri');
        $api_key = config('custom.era.api_key');
        $error_source = "none";


        $sendHpM = SmileHelper::SubmitHappyMeter(
            $req,
            $req->type,
            $req->reason,
            $req->remark,
            $req->header('user-agent'),
            'web'
        );

        $json =[

            'type' => $req->type,
            'reason' => $req->reason,
            'remark' => $req->remark,
            'staffno'=>$req->user()->staff_no,
            'source'=>"trUSt bp"
        ] ;

        if (env('ERA_AUTH')=='query') {
            $options = [
                'json' => $json ,
              'query' => ['api_key' => config('custom.era.api_key')] ,

            ];
        } else {
            $options = [
                'json' => $json ,
              'headers' => ['Authorization' => 'Bearer '.config('custom.era.api_key')],
              ];
        }

        //$request = "";
        $alert_class = "info";
        $response="";
        $alert= 'Hi. Thanks for contributing. Your input have been sent to ERA.';
        try {
            $reclient = new \GuzzleHttp\Client(["base_uri" => $api_uri]);
            $request = $reclient->request('POST', 'happy/meter/external', $options);
            $status = $request->getStatusCode();
            if ($status == 200) {
                $response = $request->getBody()->getContents();
                
                $alert = "Yay... We received your input. <br />
                Come back here if your feeling has changed or you can go to <a href='https://era.tm.com.my'> https://era.tm.com.my </a>." ;

            } else {
                // The server responded with some error. You can throw back your exception
                // to the calling function or decide to handle it here
                $error_source = "trUSt";
                Log::error($status);
                Log::error($request->getBody()->getContents());
                throw new \Exception('Failed');


            }
        } catch (\Exception $e) {
            try {
                if ($error_source == "trUSt") {
                    $response = json_encode((string)$e->getResponse()->getBody());
                    $response = json_decode($response);
                    $alert= 'Uh oh!. You did something and our partner at ERA are angry !!!.. They said ' .$response;
                } else {
                    $err = $e->getMessage();
                    $response = json_encode((string) $err);
                    $response = json_decode($response);
                    $alert= "Uh oh!. Something went wrong. Contact trUSt admin and tell this to them: ". $api_uri."  ".$response.
                "</br>In the meantime you can go <a href='https://era.tm.com.my'> https://era.tm.com.my </a> to tell how you feels.";
                }
            } catch (\Exception $e2) {
                $alert = $e2->getMessage();
                Log::error($$e2->getMessage());
            }

            $alert_class = 'danger';
        }

        
        return redirect(route('smile'))->with([
            'alert' => $alert,
            'alert_class' => $alert_class,
            'happyMeter' => $sendHpM
          ]);

    }
}
