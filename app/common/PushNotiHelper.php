<?php

namespace App\common;

use GuzzleHttp\Client;
use App\Models\PushNotiHistory;
use Illuminate\Support\Facades\Log;

class PushNotiHelper
{
  public static function SendPushNoti($noti_id, $title, $body, $sender, $recipient_id, $trigger_event, $data = [] ){

      if(!isset($noti_id) || strlen($noti_id) < 10){
        $ret = [
          'data' => 'Invalid push ID',
          'status' => 'Failed'
        ];
      } else {
        $client = new Client();
        $param = [
          'to' => $noti_id,
          'title' => $title,
          'body' => $body,
          'badge' => 1
        ];
        $head = [
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
          'accept-encoding' => 'gzip, deflate',
          'host' => 'exp.host'
        ];

        // return $param;
        for ($i=0; $i < 10; $i++) {
          try {
            $resp = $client->request(
              'POST',
              'https://exp.host/--/api/v2/push/send', [
                // 'headers' => $head,
                'form_params' => $param,
                'connect_timeout' => 10
              ]

            );

            if ($resp->getStatusCode() !== 200) {
              Log::error($resp);
              $ret = [
                'data' => 'Error ' . $resp->getStatusCode(),
                'status' => 'Failed'
              ];
            } else {
              $rpbody = json_decode($resp->getBody()->getContents());
              $rpbody->tries = $i + 1;
              $ret = [
                'data' => json_encode($rpbody),
                'status' => 'Success'
              ];
            }

            break;
          } catch (\Exception $e) {
            if($i == 9){
              Log::error($e);
            }

            $ret = [
              'data' => $e->getMessage(),
              'status' => 'Failed'
            ];
          }
        }

      }

      // add to sent history
      $pni = new PushNotiHistory;
      $pni->user_id = $recipient_id;
      $pni->sender = $sender;
      $pni->trigger_event = $trigger_event;
      $pni->sent_date = date('Y-m-d');
      $pni->title = $title;
      $pni->content = $body;
      $pni->status = $ret['status'];
      $pni->resp_data = $ret['data'];
      $pni->pushnoti_id = $noti_id;
      $pni->save();

      return $ret;
    }

}
