<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\common\UserRegisterHandler;
use App\common\IopHandler;
use App\common\SeatHelper;
use App\common\CheckinHelper;
use App\common\CommonHelper;
use App\Models\AreaBooking;
use App\Models\Seat;
use App\Models\Building;
use App\Models\FloorSection;
use App\Models\Floor;

class AgileOfficeController extends Controller
{

  // validate token
  public function locationUpdate(Request $req){
    $input = app('request')->all();

		$rules = [
			'lat' => ['required'],
      'long' => ['required'],
      'address' => ['required'],
      'action' => ['required']
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

    $luser = $req->user();

    if($req->action == 'updateloc'){
      UserRegisterHandler::attUpdateLoc($luser->id,
        $req->lat, $req->long,
        $req->filled('reason') ? $req->reason : '',
        $req->address
      );
    } elseif ($req->action == 'clockout') {
      UserRegisterHandler::attClockOut($luser->id, \Carbon\Carbon::now(),
        $req->lat, $req->long,
        $req->filled('reason') ? $req->reason : '',
        $req->address
      );
    } elseif ($req->action == 'clockin') {
      $req->staff_id = $luser->id;
      UserRegisterHandler::attClockIn($req);
    } else {
      return $this->respond_json(412, 'Invalid input', $input);
    }

    return $this->respond_json(200, 'Success', []);


  }

  public function reverseGeo(Request $req) {
    $input = app('request')->all();

		$rules = [
			'lat' => ['required'],
      'lon' => ['required']
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

    return $this->respond_json(200, 'Success', [
      'addr' => IopHandler::ReverseGeoAPIgate($req->lat, $req->lon)
    ]);

  }

  public function getCurrentCheckins(Request $req){
    // $luser = $req->user();
    $ret = [];

    foreach($req->user()->current_checkins as $ck){
      $et = new \Carbon\Carbon($ck->in_time);
      $ret[] = [
        'id' => $ck->id,
        'location' => $ck->event_attendance_id ? $ck->EventAttendance->name : $ck->Seat ? $ck->Seat->label : 'Deleted Seat',
        'from' => $et->toDayDateTimeString()
      ];
    }

    return $this->respond_json(200, 'Success', [
      'checkins' => $ret
    ]);

  }

  public function getCurrentReservations(Request $req){
    // $luser = $req->user();
    $ret = [];

    foreach(CheckinHelper::GetUserUpcomingReservation($req->user()) as $ck){
      $st = new \Carbon\Carbon($ck->start_time);
      $et = new \Carbon\Carbon($ck->end_time);
      $ret[] = [
        'id' => $ck->id,
        'seat' => $ck->Seat ? $ck->Seat->label : 'Deleted Seat',
        'location' => $ck->FloorSection->long_label,
        'from' => $st->toDayDateTimeString(),
        'to' => $et->toDayDateTimeString()
      ];
    }

    return $this->respond_json(200, 'Success', [
      'checkins' => $ret
    ]);

  }

  public function getSeatStatus(Request $req){
    $input = app('request')->all();

		$rules = [
			'code' => ['required']
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

    $data = SeatHelper::GetSeatCurrentStatus($req->code, $req->user()->id);
    return $this->respond_json(200, 'Success', $data);
  }

  public function getEventStatus(Request $req){
    $input = app('request')->all();

		$rules = [
			'code' => ['required']
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

    $data = CheckinHelper::GetMeetingAreaStatus($req->code);
    return $this->respond_json(200, 'Success', $data);
  }

  public function doSeatCheckin(Request $req){
    $input = app('request')->all();

		$rules = [
			'id' => ['required'],
      'lat' => ['required'],
      'long' => ['required'],
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

    $seat = Seat::find($req->id);
    if($seat){

      if($seat->status != 1){
        return $this->respond_json(200, 'Seat / Area disabled');
      }

      if($seat->seat_type == 'Seat' && $seat->seat_utilized >= $seat->seat_capacity){
        return $this->respond_json(200, 'Seat full / occupied');
      }

      $ckresp = CheckinHelper::SeatCheckIn($req->user(), $seat, 'Mobile Checkin', $req->lat, $req->long);
      if(isset($ckresp['error'])){
        return $this->respond_json(200, $ckresp['error']);
      }
      return $this->respond_json(200, 'Success');
    } else {
      abort(404);
    }

  }

  public function doEventCheckin(Request $req){
    $input = app('request')->all();

		$rules = [
			'id' => ['required'],
      'lat' => ['required'],
      'long' => ['required'],
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

    $ab = AreaBooking::find($req->id);
    if($ab){
      if($ab->status != 'Active'){
        return $this->respond_json(200, 'Event status: ' . $ab->status);
      }
      // check if event has ended
      $now = new \Carbon\Carbon;
      $event_end_time = new \Carbon\Carbon($ab->end_time);
      if($now->gt($event_end_time)){
        if($now->diffInMinutes($event_end_time) > 30){
          // dont allow to checkin event that ended 30 minutes ago
          return $this->respond_json(200, 'Event has ended', $ab);
        }
      }
      // or event havent started
      $event_start_time = new \Carbon\Carbon($ab->start_time);
      if($event_start_time->gt($now)){
        if($now->diffInMinutes($event_start_time) > 30){
          // dont allow to checkin event that ended 30 minutes ago
          return $this->respond_json(200, 'Event has not started yet');
        }
      }

      CheckinHelper::EventCheckIn($req->user(), $ab, 'Mobile Checkin', $req->lat, $req->long);
      return $this->respond_json(200, 'Success');
    } else {
      abort(404);
    }


  }

  public function doCheckout(Request $req){
    $input = app('request')->all();

		$rules = [
			'id' => ['required'],
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

    $sc = CheckinHelper::SeatCheckout($req->user(), $req->id);
    return $this->respond_json(200, 'Success');


  }

  public function getBuildingList(Request $req){
    $rets = Building::select('building_name', 'id')->get();
    return $this->respond_json(200, 'Success', $rets);
  }

  public function getFloorList(Request $req){
    $rets = Floor::where('building_id', $req->building_id)
      ->where('status', 1)
      ->select('floor_name', 'id')->get();
    return $this->respond_json(200, 'Success', $rets);
  }

  public function getSectionList(Request $req){
    $rets = FloorSection::where('floor_id', $req->floor_id)->where('status', 1)->select('label', 'id')->get();
    return $this->respond_json(200, 'Success', $rets);
  }

  public function searchAvailableSeat(Request $req){
    $today = new \Carbon\Carbon;
    $today->addMinutes(3);
    $sinput = new \Carbon\Carbon($req->start_time);
    $einput = new \Carbon\Carbon($req->end_time);

    // return $this->respond_json(200, 'Success', [
    //   's' => $sinput->toDateTimeString(),
    //   'e' => $einput->toDateTimeString()
    // ]);

    if($sinput->gt($einput)){
      return $this->respond_json(201, 'To time is before from time');
    }

    if($sinput->lt($today)){
      return $this->respond_json(201, 'From time must be at least 5 minute from current time');
    }

    if($sinput->diffInDays($einput) > CommonHelper::GetCConfig('long booking days', 7)){
      return $this->respond_json(201, 'Reservation duration is too long');
    }

    $bd = Building::find($req->building_id);
    if($bd){

    } else {
      return $this->respond_json(201, 'Selected building no longer exist');
    }

    $res = SeatHelper::FindBookableSeatV2($req->start_time, $req->end_time, $req->building_id, $req->floor_id, $req->floor_section_id);
    // return $this->respond_json(200, 'Success', $res);
    // rearrange to format that react native can handle
    $finale = [];
    foreach ($res as $floorid => $floor) {
      $fcs = [];
      foreach ($floor['fcs'] as $fcid => $section) {
        $seats = [];
        foreach ($section['seats'] as $seatid => $seat) {
          $seats[] = $seat;
        }
        $fcs[] = [
          'id' => $fcid,
          'name' => $section['name'],
          'count' => $section['count'],
          'gotlayout' => $section['gotlayout'],
          'seats' => $seats
        ];
      }
      $finale[] = [
        'id' => $floorid,
        'name' => $floor['name'],
        'count' => $floor['count'],
        'gotlayout' => $floor['gotlayout'],
        'fcs' => $fcs
      ];
    }

    return $this->respond_json(200, 'Success', $finale);
  }

  public function doSeatReserve(Request $req){
    $av = SeatHelper::DoSeatBookingV2($req->seat_id, $req->stime, $req->etime, $req->user());
    if(isset($av['error'])){
      return $this->respond_json(200, $av['error']);
    }

    return $this->respond_json(200, 'Success');
  }

  public function getFloorLayout(Request $req){
    if($req->filled('id')){
      $floor = Floor::find($req->id);
      if($floor && $floor->layout_file){
        if (!\Storage::exists($floor->layout_file)) {
            return response()->file(public_path('img/notavailable.png'));
          }

          $file = \Storage::get($floor->layout_file);
          $type = \Storage::mimeType($floor->layout_file);
          $response = \Response::make($file, 200)->header("Content-Type", $type);

          return $response;
      } else {
        return response()->file(public_path('img/notavailable.png'));
      }
    } else {
      return response()->file(public_path('img/notavailable.png'));
    }

  }

  public function getSectionLayout(Request $req){
    if($req->filled('id')){
      $floor = FloorSection::find($req->id);
      if($floor && $floor->layout_file){
        if (!\Storage::exists($floor->layout_file)) {
            return response()->file(public_path('img/notavailable.png'));
          }

          $file = \Storage::get($floor->layout_file);
          $type = \Storage::mimeType($floor->layout_file);
          $response = \Response::make($file, 200)->header("Content-Type", $type);

          return $response;
      } else {
        return response()->file(public_path('img/notavailable.png'));
      }
    } else {
      return response()->file(public_path('img/notavailable.png'));
    }

  }

}
