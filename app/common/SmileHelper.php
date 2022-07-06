<?php

namespace App\common;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\HappyMeter;
use App\Models\HappyReason;
use App\Models\HappyType;

class SmileHelper
{
    public static function SubmitHappyMeter(
        $req,
        $typeid,
        $reasonid,
        $remark,
        $agent,
        $sourcefromtrust
    )
    {
        // $req = Request::all();
        $rndesc = HappyReason::where('id', $reasonid)->first();
        $tydesc = HappyType::where('id', $typeid)->first();

        $happymtr = new HappyMeter;
        $happymtr->type_id = $typeid;
        $happymtr->type_desc = $tydesc->type;
        $happymtr->reason_id = $reasonid;
        $happymtr->reason_desc = $rndesc->reason;
        $happymtr->remark = $remark;
        $happymtr->staff_no = $req->user()->staff_no;
        $happymtr->user_id = $req->user()->id;
        $happymtr->sourcefromtrust = $sourcefromtrust;
        $happymtr->agent = $agent;
        $happymtr->save();
        // dd($happymtr);
        return $happymtr;
    }
}
