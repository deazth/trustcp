<?php

namespace App\common;

use GuzzleHttp\Client;
use App\Models\CoordMapping;
use App\Models\NeoWsrHistory;

class IopHandler
{
  public static $baseuri = "https://tmoip.tm.com.my/api/t/tm.com.my/";

  // reverse geo   https://tmoip.tm.com.my/api/t/tm.com.my/geosmartmap/1.0.0/search/reversegeocode?lat=2.788489299&lon=101.7182277
  // pic           https://tmoip.tm.com.my/api/t/tm.com.my/era/1.0.0/profile/image/S54113

  public static function GetStaffImage($staffno)
  {
    $reclient = new Client(["base_uri" => self::$baseuri]);
    $options = [
      'headers' => ['Authorization' => config('custom.tmoip_token')],
      'connect_timeout' => 10
    ];


    $request = $reclient->request('GET', 'era/1.0.0/profile/image/' . $staffno, $options)->getBody()->getContents();

    $response = response()->make($request, 200);
    $response->header('Content-Type', 'image/jpeg'); // change this to the download content type.
    return $response;
  }

  public static function GetStaffImageAPIgate($staffno)
  {
    $reclient = new Client(["base_uri" => env('APIGATE_URI')]);
    $options = [
      'verify' => false,
      'headers' => ['Authorization' => 'Bearer ' . env('APIGATE_KEY')],
      'connect_timeout' => 10
    ];
    // dd($options);

    $request = $reclient->request('GET', 'profile/image/' . $staffno, $options)->getBody()->getContents();

    $response = response()->make($request, 200);
    $response->header('Content-Type', 'image/jpeg'); // change this to the download content type.
    return $response;
  }



  public static function ReverseGeo($lat, $long)
  {
    $reclient = new Client(["base_uri" => self::$baseuri]);
    $options = [
      'query' => ['lat' => $lat, 'lon' => $long],
      'headers' => ['Custom' => config('custom.tmoip_token')]
    ];


    $request = $reclient->request('GET', 'geosmartmap/1.0.0/search/reversegeocode', $options)->getBody()->getContents();
    // $request->addHeader('Authorization: Bearer', '5a107934-68de-38cd-9a34-60fa4ae46267');
    // $resp = $reclient->send($request);

    $ret = json_decode($request);

    if (sizeof($ret) > 0) {
      return $ret[0]->formatted_address;
    } else {
      return "No result";
    }
  }

  public static function ReverseGeoAPIgate($lat, $long)
  {
    $rlat = round($lat, 5);
    $rlong = round($long, 5);
    $lastmon = new \Carbon\Carbon;
    $lastmon->subMonth();

    // check if there is any existing record for this coords;
    $existing = CoordMapping::where('latitude', $rlat)->where('longitude', $rlong)->first();

    if ($existing) {
      // check if it's not more than 1 month
      $recdate = new \Carbon\Carbon($existing->created_at);
      if ($recdate->gt($lastmon)) {
        return $existing->address;
      } else {
        // delete the old record
        $existing->delete();
      }
    }

    // either no record or already obsolete. fetch new
    $reclient = new Client(["base_uri" => env('GEO_URI')]);
    $options = [
      'verify' => false,
      'headers' => ['Authorization' => 'Bearer ' . env('GEO_TOKEN')],
      'query' => ['api_key' => env('GEO_KEY'), 'lat' => $lat, 'lon' => $long],
      'connect_timeout' => 10
    ];


    $request = $reclient->request('GET', 'search/reversegeocode', $options)->getBody()->getContents();
    $ret = json_decode($request);

    if (sizeof($ret) > 0) {
      // store the data for future use
      $ins = new CoordMapping;
      $ins->latitude = $rlat;
      $ins->longitude = $rlong;
      $ins->address = $ret[0]->formatted_address;
      $ins->save();

      return $ret[0]->formatted_address;
    } else {
      return "No result";
    }
  }


  public static function GetHappyReasons()
  {
    $reclient = new Client(["base_uri" => config('custom.era.api_uri')]);
    if (env('ERA_AUTH') == 'query') {
      $options = [
        'query' => ['api_key' => config('custom.era.api_key')],

      ];
    } else {
      $options = [
        'headers' => ['Authorization' => 'Bearer ' . config('custom.era.api_key')],
      ];
    }


    $request = $reclient->request('GET', 'happy/reasons', $options)->getBody()->getContents();

    return $request;
  }


  public static function GetHappyTypes()
  {
    //dd(config('custom.era.api_uri'));
    $reclient = new Client(["base_uri" => config('custom.era.api_uri')]);
    if (env('ERA_AUTH') == 'query') {
      $options = [
        'query' => ['api_key' => config('custom.era.api_key')],

      ];
    } else {
      $options = [
        'headers' => ['Authorization' => 'Bearer ' . config('custom.era.api_key')],
      ];
    }

    $request = $reclient->request('GET', 'happy/types', $options)->getBody()->getContents();
    return $request;
  }

  public static function GetNeoUsp($persno, $date)
  {
    // dd($persno);
    $reclient = new Client(["base_uri" => config('custom.neo_base_url')]);
    $rett = null;
    $neo = new NeoWsrHistory;
    $neo->persno = $persno;
    $neo->input_date = $date;

    try {
      $request = $reclient->request('GET', 'api/usp/get/' . $persno . '/' . $date, ['verify' => false])->getBody()->getContents();
      $rett = json_decode($request);
    } catch (\Exception $e) {
      \Illuminate\Support\Facades\Log::warning('GetNeoUsp ' . $persno . ' - ' . $date . ' : ' .'err1 :' . $e->getMessage());

      sleep(1);
      try {
        $request = $reclient->request('GET', 'api/usp/get/' . $persno . '/' . $date, ['verify' => false])->getBody()->getContents();
        $rett = json_decode($request);
      } catch (\Exception $e2) {
        \Illuminate\Support\Facades\Log::warning('GetNeoUsp ' . $persno . ' - ' . $date . ' : ' .'err2 :' . $e2->getMessage());
        $neo->remark = $e2->getMessage();
        $rett = null;
      }
    }

    if($rett){
      $neo->day_descr = $rett->check->day_descr;
      $neo->expected_hours = $rett->check->expected_hour;
      $neo->is_work_day = $rett->check->is_work_day;

      if($rett->hol){
        $neo->remark = $rett->hol->descr;
      }
    }

    $neo->save();
    return $rett;
  }
}
