<?php

namespace App\Api\V1\Controllers\Smile;

use Illuminate\Http\Request;
use App\common\SmileHelper;
use App\Api\V1\Controllers\Controller;
use App\Models\HappyReason;
use App\Models\HappyType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SmileController extends Controller
{
    public function getReason()
    {
        $hpreason = HappyReason::all();
        $hptype = HappyType::all();
        // return response()->json($hpreason);
        return ["happType" => $hptype, "happyReason"=> $hpreason];
    }
    public function getReasonById($id)
    {
        $hpreason = HappyReason::find($id);
        return $hpreason;
    }


    public function getReasonByTypeID($type_id)
    {
        $hpt = HappyType::find($type_id);
        // $hptype->put("s","s");
        //dd($htca);
        $hptype = [
            "id" => $hpt->id,
            "value" => $hpt->type,
            "remark" => $hpt->remark,
            "imgLink" => $hpt->ImgLink(),

            "created_at"=> $hpt->created_at->format("Y-m-d H:i:s") ,
            "updated_at"=> $hpt->updated_at->format("Y-m-d H:i:s")

        ];
        $hpreason = HappyReason::where('type_id', $type_id)
        ->get([
            'id',
            'type_id','reason as value','remark','updated_at','created_at']);
        return ["happType" => $hptype, "happyReason"=> $hpreason];
    }

    public function happyMeter(Request $req)
    {
        $api_uri = config('custom.era.api_uri');
        $api_key = config('custom.era.api_key');
        $error_source = "none";
        $input = app('request')->all();

        $rules = [
            'type' => ['required'],
            'reason' => ['required'],
            'remark' => ['required'],
            'staffno' => ['required'],
            'source' => ['required'],
            'device' => ['required']
        ];
        $validator = app('validator')->make($input, $rules);
        if ($validator->fails()) {
            return $this->respond_json(412, 'Invalid input', $input);
        }


        $sendHpM = SmileHelper::SubmitHappyMeter(
            $req,
            $req->type,
            $req->reason,
            $req->remark,
            $req->device,
            'mobile'
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
                Log::error($sendHpM);
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
        }
        return $this->respond_json(
            200,
            'Success',
            [
            "json"=> $json, "alert"=>$alert, "happyMeter"=>$sendHpM]
        );
    }
}
