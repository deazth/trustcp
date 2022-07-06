<?php

namespace App\common;

use App\Models\MeetingArea;
use App\Models\AreaBooking;
use App\Models\Seat;
use App\Models\User;
use App\Models\SeatCheckin;
use App\Models\UserSeatBooking;
use App\Models\LocationHistory;
use App\Models\Attendance;
use \Carbon\Carbon;

class CheckinHelper
{
  public static function AddLocationHist($user, $lat, $long, $action, $note, $addr = ''){
    $nulh = new LocationHistory;
    $nulh->user_id = $user->id;
    $nulh->latitude = $lat;
    $nulh->longitude = $long;
    $nulh->action = $action;
    $nulh->note = $note;
    $nulh->address = $addr;
    $nulh->save();

  }

  public static function GetCheckInOut($user, $date){
    $intime = 'N/A';
    $outime = 'N/A';

    // first get the seat checkin if any
    $ins = SeatCheckin::where('user_id', $user->id)->whereDate('in_time', $date)
      ->orderBy('in_time', 'ASC')->first();

    if($ins){
      $intime = new Carbon($ins->in_time);
    }

    // in case location checkin is earlier
    $inl = Attendance::where('user_id', $user->id)->whereDate('clockin_time', $date)
      ->orderBy('clockin_time', 'ASC')->first();

    if($inl){
      $inltime = new Carbon($inl->clockin_time);
      if($intime == 'N/A' || $inltime->lt($intime)){
        $intime = $inltime;
      }
    }

    // then get the latest out time
    $outs = SeatCheckin::where('user_id', $user->id)->whereDate('out_time', $date)
      ->orderBy('out_time', 'DESC')->first();

    if($outs){
      $outime = new Carbon($outs->out_time);
    }

    // in case location checkout is later
    $outl = Attendance::where('user_id', $user->id)->whereDate('clockout_time', $date)
      ->orderBy('clockout_time', 'DESC')->first();

    if($outl){
      $outltime = new Carbon($outl->clockout_time);
      if($outime == 'N/A' || $outltime->lt($outime)){
        $outime = $outltime;
      }
    }

    return [
      'in' => $intime == 'N/A' ? 'N/A' : $intime->toTimeString(),
      'out' => $outime == 'N/A' ? 'N/A' : $outime->toTimeString()
    ];
  }


  public static function GetUserUpcomingReservation($user){
    $next5 = new \Carbon\Carbon;
    $now = new \Carbon\Carbon;
    $next5->addMinutes(5);

    // check if there is any reservation for this seat
    $usbs = UserSeatBooking::
    // where('start_time', '<=', $next5->toDateTimeString())
      //->
      where('end_time', '>', $now->toDateTimeString())
      ->whereNotIn('status', ['Cancelled', 'Expired', 'Ended'])
      ->where('user_id', $user->id)
      ->orderBy('start_time', 'ASC')
      ->limit(10)->get();

    return $usbs;
  }

  public static function UserGotReservation($user, $seat){
    $next5 = new \Carbon\Carbon;
    $now = new \Carbon\Carbon;
    $next5->addMinutes(5);

    // check if there is any reservation for this seat
    $usbs = UserSeatBooking::where('seat_id', $seat->id)
      ->where('start_time', '<=', $next5->toDateTimeString())
      ->where('end_time', '>', $now->toDateTimeString())
      ->whereNotIn('status', ['Cancelled', 'Expired', 'Ended'])
      ->where('user_id', $user->id)->first();

    return $usbs ?? false;
  }

  public static function SeatCheckIn($user, $seat, $remark, $lat, $long){
    // just in case, checkout previous seat
    self::SeatCheckoutAll($user);
    $now = new \Carbon\Carbon;

    $curbook = self::UserGotReservation($user, $seat);

    if($curbook){

    } else {
      if($seat->seat_type == 'Seat'){
        if($seat->allow_booking){
          // dont allow for seat that require booking
          return ['error' => 'You dont have valid reservation for this workspace'];
        }
      }
    }

    // then create new checkin
    $nu = new SeatCheckin;
    $nu->user_id = $user->id;
    $nu->seat_id = $seat->id;
    $nu->in_time = $now->toDateTimeString();
    $nu->remark = $remark;
    $nu->latitude = $lat;
    $nu->longitude = $long;
    $nu->save();

    // increment the seat utilized count
    $seat->increment('seat_utilized');

    // attach this checkin to the user's active checkins
    $user->current_checkins()->attach($nu->id);


    if($seat->seat_type == 'Seat'){
      if($curbook){
        // update the reservation status
        $curbook->status = 'Checked-in';
        $curbook->seat_checkin_id = $nu->id;
        $curbook->save();

        // cancel other unrelated reservations
        $usbs = UserSeatBooking::where('seat_id', $seat->id)
          ->where('start_time', '<=', $now->toDateTimeString())
          ->where('end_time', '>', $now->toDateTimeString())
          ->whereNotIn('status', ['Cancelled', 'Expired', 'Ended'])
          ->where('id', '!=', $curbook->id)
          ->where('user_id', $user->id)->get();
      } else {
        // cancel other unrelated reservations
        $usbs = UserSeatBooking::where('seat_id', $seat->id)
          ->where('start_time', '<=', $now->toDateTimeString())
          ->where('end_time', '>', $now->toDateTimeString())
          ->whereNotIn('status', ['Cancelled', 'Expired', 'Ended'])
          ->where('user_id', $user->id)->get();
      }

      foreach($usbs as $usb){
        $usb->CancelBooking('Cancelled', 'Checked-in elsewhere');
      }
    }

    // also add to location history
    self::AddLocationHist($user, $lat, $long, 'Agile Office', $seat->seat_type, $seat->long_label);

    return [
      'SeatCheckin' => $nu,
      'UserSeatBooking' => $curbook
    ];

  }

  /*
    this will check out all seat
  */
  public static function SeatCheckoutAll($user){
    foreach($user->current_checkins as $cc){
      $cc->doCheckOut();
    }

    $user->current_checkins()->detach();
  }

  public static function SeatCheckout($user, $checkin_id){
    // check if the checkin is for that user
    $sc = $user->current_checkins()->where('id', $checkin_id)->first();
    if($sc){
      $sc->doCheckOut();
      $user->current_checkins()->detach($checkin_id);
    }
    return $sc;
  }

  public static function EventCheckIn($user, $event, $remark, $lat, $long){
    // check if there is any active checkin for this user
    foreach ($user->current_checkins as $value) {
      if($value->Seat->seat_type == 'Meeting Area'){
        // checkout from previous event
        self::SeatCheckout($user, $value->id);
      } else {
        // is a seat checkin. checkout if it's not at the same floor
        if($event->Meetingarea->floor_section->floor_id != $value->Seat->floor_section->floor_id){
          self::SeatCheckout($user, $value->id);
        }
      }
    }

    $now = new \Carbon\Carbon;
    // get the event_attendance obj
    $eat = MeetingAreaHelper::GetEventAttendance($event, $now->toDateString());

    // do the checkin
    $nu = new SeatCheckin;
    $nu->user_id = $user->id;
    $nu->seat_id = $event->Meetingarea->id;
    $nu->in_time = $now->toDateTimeString();
    $nu->remark = $remark;
    $nu->latitude = $lat;
    $nu->longitude = $long;
    $nu->area_boooking_id = $event->id;
    $nu->event_attendance_id = $eat->id;
    $nu->save();

    // increment the seat utilized count
    $nu->Seat->increment('seat_utilized');

    // attach this checkin to the user's active checkins
    $user->current_checkins()->attach($nu->id);


  }

  public static function GetAreaEvents($seat){
    $now = new \Carbon\Carbon;
    $past30 = new \Carbon\Carbon;
    $future30 = new \Carbon\Carbon;
    $past30->subMinutes(30);
    $future30->addMinutes(30);

    // dd([
    //   'now' => $now->toDateTimeString(),
    //   'past' => $past30->toDateTimeString(),
    //   'future' => $future30->toDateTimeString(),
    // ]);

    $events = [];
    // find past event (within 30 minit)
    $past = AreaBooking::where('seat_id', $seat->id)
      ->where('end_time', '>', $past30->toDateTimeString())
      ->where('end_time', '<', $now->toDateTimeString())
      ->orderBy('end_time', 'DESC')
      ->first();

    if($past){
      $events[] = $past;
    }

    // find current event
    $cure = AreaBooking::where('seat_id', $seat->id)
      ->where('start_time', '<', $now->toDateTimeString())
      ->where('end_time', '>', $now->toDateTimeString())
      ->first();

    if($cure){
      $events[] = $cure;
    }

    // find upcoming event (within 30 minit)
    $futr = AreaBooking::where('seat_id', $seat->id)
      ->where('start_time', '>', $now->toDateTimeString())
      ->where('start_time', '<', $future30->toDateTimeString())
      ->orderBy('start_time', 'ASC')
      ->first();

    if($futr){
      $events[] = $futr;
    }

    return $events;
  }

  public static function GetMeetingAreaStatus($event_qr){
    $ev = AreaBooking::where('qr_code', $event_qr)->first();
    if($ev){
      $status = 'Available';
      $now = new \Carbon\Carbon;

      $evstart = new \Carbon\Carbon($ev->start_time);
      $evend = new \Carbon\Carbon($ev->end_time);
      $evstart->subMinutes(30);
      $evend->addMinutes(30);

      if($now->lt($evstart)){
        $status = 'Not started';
      }

      if($now->gt($evend)){
        $status = 'Ended';
      }

      $extra = [
        'ev_id' => $ev->id,
        'name' => $ev->event_name,
        'org' => $ev->organizer->id_name,
        'startt' => $ev->start_time,
        'endt' => $ev->end_time
      ];

      return [
        'id' => $ev->id,
        'label' => $ev->event_name,
        'status' => $status,
        'type' => 'event',
        'location' => $ev->Meetingarea->long_label,
        'extra' => [$extra]
      ];

    } else {
      abort(404);
    }
  }



}
