<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Models\LocationHistory;
use App\common\UserRegisterHandler;
use App\common\IopHandler;


class WorkAnywhereController extends Controller
{

	public function GetCurrentStatus(Request $req){
		// placeholders
		$lastloc = null;
		$is_clocked = isset($req->user()->curr_attendance);
		// get last known location
		$lastknown = LocationHistory::where('user_id', $req->user()->id)
			->latest()->first();

		if($lastknown){
			$lastloc = [
				'lat' => $lastknown->latitude,
				'long' => $lastknown->longitude,
				'addr' => $lastknown->address
			];
		}

		$retval = [
			'lastloc' => $lastloc,
			'is_clocked' => $is_clocked
		];

		return $this->respond_json(200, 'Success', $retval);
	}

	public function CoordToAddr(Request $req){
		$resp = IopHandler::ReverseGeoAPIgate($req->latitude, $req->longitude);
		return $this->respond_json(200, 'Success', $resp);
	}

	public function CheckInCoord(Request $req){
		$req->staff_id = $req->user()->id;
		UserRegisterHandler::attClockIn($req);
		return $this->respond_json(200, 'Success');
	}

	public function UpdateCoord(Request $req){
		UserRegisterHandler::attUpdateLoc($req->user()->id,
			$req->lat, $req->long,
			$req->filled('reason') ? $req->reason : '',
			$req->address
		);

		return $this->respond_json(200, 'Success');
	}

	public function CheckOutCoord(Request $req){
		UserRegisterHandler::attClockOut($req->user()->id, \Carbon\Carbon::now(),
			$req->lat, $req->long,
			$req->filled('reason') ? $req->reason : '',
			$req->address
		);

		return $this->respond_json(200, 'Success');
	}

}
