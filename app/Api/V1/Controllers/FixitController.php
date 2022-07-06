<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Models\Seat;
use App\Models\Fixit;

class FixitController extends Controller
{
    public function fixitResponse(Request $req)
    {
        $seat_id = $req->workspace_id;
        $ticket_id = $req->ticket_id;
        $status = $req->status;
        $app = $req->app;


        //    $seat = new Seat();

        $seat = Seat::find($seat_id);

        //abort(404,$seat);

        if ($seat) {
        } else {
            return $this->respond_json(500, "Invalid workspace id");
        }



        try {
            $fx = new Fixit();
            $fx->seat_id = $seat_id;
            $fx->ticket_id = $ticket_id;
            $fx->status = $status;
            $fx->app = $app;

            $fx->save();


            if ($status == 'OPEN') {
                $seat2 = Seat::find($seat_id);
                $seat2->status = 0;
                $seat2->save();
            }

            if ($status == 'INPR') {
                $seat->status = 0;
                $seat->save();
            }
            if ($status == 'CLOSE') {
                $seat->status = 1;
                $seat->save();
                Fixit::where('ticket_id', $ticket_id)
                    ->update(['resolve_id' => $fx->id]);
            }




            return $this->respond_json(200, 'Success');
        } catch (\Exception $e) {
            return $this->respond_json(500, $e->getMessage());
        }
    }


    public function getQRbySeat($seatID)
    {
        $seat = Seat::find($seatID);
        $ret = [
            "qr" => $seat->qr_code,
            "label" => $seat->label,
            "label" => $seat->long_label
            ] ;

        if ($seat) {
            return $ret;
        } else {
            return $this->respond_json(500, "Invalid workspace id","");
        }
    }
}
