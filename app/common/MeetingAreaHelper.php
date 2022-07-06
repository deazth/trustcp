<?php

namespace App\common;

use App\Models\MeetingArea;
use App\Models\AreaBooking;
use App\Models\EventAttendance;
use App\Models\Seat;
use App\Models\User;
use App\Models\AreaUtilIntv;
use App\Models\AreaUtilDaily;
use App\Models\SeatCheckin;
use App\Notifications\AreaHijacked;
use \Carbon\Carbon;

class MeetingAreaHelper
{

  /*
  'booking_status' => [
    'Active' => 'Active',
    'Cancelled' => 'Cancelled',
    'Pending SB' => 'Pending SB',
    'Rejected' => 'Rejected'
  ]

  */

  /**
    start and end time are carbon obj
  */
  public static function CheckAvailability($seat_id, $start_time, $end_time){
    $seat = Seat::find($seat_id);
    if($seat){
      $overlap = AreaBooking::where('seat_id', $seat_id)
        ->where('start_time', '<', $end_time->toDateTimeString())
        ->where('end_time', '>', $start_time->toDateTimeString())
        ->whereIn('status', ['Active', 'Pending SB'])
        ->first();

      if($overlap){
        return false;
      }

      return true;
    } else {
      return false;
    }
  }

  /**
    start and end time are carbon obj
  */
  public static function BookArea($seat_id, $start_time, $end_time, $user_id, $event_name, $extras = [], $user_remark = ''){
    $retmsg = 'Booking Successful';

    $ab = new AreaBooking;
    $ab->seat_id = $seat_id;
    $ab->user_id = $user_id;
    $ab->start_time = $start_time->toDateTimeString();
    $ab->end_time = $end_time->toDateTimeString();
    $ab->event_name = $event_name;
    $ab->status = 'Active';
    $ab->user_remark = $user_remark;
    $ab->qr_code = \Illuminate\Support\Str::orderedUuid()->toString();

    $ltb_limit = CommonHelper::GetCConfig('long booking days', 7);
    // check for long term booking
    if($end_time->diffInDays($start_time) > $ltb_limit){
      $ab->is_long_term = true;
      $ab->status = 'Pending SB';
      $retmsg = 'Long term booking. Pending SB approval';
    }
    $ab->save();

    $exreq = [];
    foreach($extras as $ext){
      $exreq[$ext->extra_equip_eq] = ['count' => $ext->extra_equip_count, 'status' => 'New'];
    }

    // check for extra request
    if(sizeof($exreq) > 0){
      $ab->extra_equip()->sync($exreq);
      $ab->has_extra_req = true;
      $ab->status = 'Pending SB';
      $ab->save();
      if($retmsg == 'Booking Successful'){
        $retmsg = 'Extra request - pending SB approval';
      }
    }

    if($ab->status == 'Active'){
      self::CreateEventAttendance($ab);
    }

    return $retmsg;
  }

  public static function GetEventAttendance($areabooking, $date){
    $eat = EventAttendance::firstOrCreate(
      ['area_event_id' => $areabooking->id, 'event_date' => $date],
      ['name' => $areabooking->event_name]
    );

    return $eat;
  }

  public static function CreateEventAttendance($areabooking){
    $start_time = new \Carbon\Carbon($areabooking->start_time);
    $end_time = new \Carbon\Carbon($areabooking->end_time);

    if($start_time->toDateString() == $end_time->toDateString()){
      // if same day, just create 1
      $eat = EventAttendance::firstOrCreate(
        ['area_event_id' => $areabooking->id, 'event_date' => $start_time->toDateString()],
        ['name' => $areabooking->event_name]
      );
    } else {
      // multiple day. create multi day attendances
      $start_time->hour = 0;
      $counter = 1;
      while($start_time->lt($end_time)){
        $eat = EventAttendance::firstOrCreate(
          ['area_event_id' => $areabooking->id, 'event_date' => $start_time->toDateString()],
          ['name' => $areabooking->event_name . ' Day ' . $counter]
        );
        $start_time->addDay();
        $counter++;
      }
    }
  }

  public static function GetEventInfo($book){
    // get all participant
    $partlist = User::find(SeatCheckin::where('area_boooking_id', $book->id)
      ->get()->unique('user_id')->pluck('user_id'));

    // get list of day
    $datelist = [];
    $evat = [];
    $start_time = new \Carbon\Carbon($book->start_time);
    $end_time = new \Carbon\Carbon($book->end_time);
    $start_time->hour = 0;
    while($start_time->lt($end_time)){
      $datelist[] = $start_time->format('j M');
      $evat[] = self::GetEventAttendance($book, $start_time->toDateString())->id;
      $start_time->addDay();
    }

    // then for each staff
    foreach($partlist as $us){
      // get their attendance
      $attended = [];
      foreach($evat as $evid){
        $ck = SeatCheckin::where('user_id', $us->id)
          ->where('event_attendance_id', $evid)->first();
        if($ck){
          $attended[] = true;
        } else {
          $attended[] = false;
        }
      }
      $us->attended = $attended;
    }

    return [
      'user' => $partlist,
      'head' => $datelist
    ];
  }

  public static function SendHijackAlert(AreaBooking $ab){
    // find any overlapping bookings
    $overlap = AreaBooking::where('status', 'Active')
      ->where('start_time', '<', $ab->end_time)
      ->where('end_time', '>', $ab->start_time)
      ->where('seat_id', $ab->seat_id)
      ->where('id', '!=', $ab->id)->get();

    foreach ($overlap as $key => $value) {
      // check if it's fully overlap
      $mestart = new Carbon($value->start_time);
      $meend = new Carbon($value->end_time);
      $hstart = new Carbon($ab->start_time);
      $hend = new Carbon($ab->end_time);

      if($hstart->lte($mestart) && $hend->gte($meend)){
        // fully overlap
        $value->status = 'Cancelled';
        $value->admin_id = $ab->admin_id;
        $value->admin_remark = 'Overlap with another booking by admin';
        $value->save();
        $value->organizer->notify(new AreaHijacked($value->id, $ab->id, 2));
      } else {
        // partial overlap
        $value->admin_id = $ab->admin_id;
        $value->admin_remark = 'Partial overlap with another booking by admin';
        $value->save();
        $value->organizer->notify(new AreaHijacked($value->id, $ab->id, 1));
      }
    }


  }

  public static function GetBuildIntvData($fs, $date){
    $data = [];
    $seat = [];
    $fsintv = AreaUtilIntv::where('building_id', $fs->id)
      ->whereDate('record_hour', $date)
      ->orderBy('seat_id', 'ASC')
      ->orderBy('record_hour', 'ASC')
      ->get();

    $seatidcounter = -1;
    $lastseatid = 0;

    foreach($fsintv as $afc){
      $ti = new \Carbon\Carbon($afc->record_hour);
      if($afc->seat_id != $lastseatid){
        $lastseatid = $afc->seat_id;
        $seat[] = $afc->MeetingArea->label ?? 'Deleted';
        $seatidcounter++;
      }

      $data[] = [$ti->format('G') + 0, $seatidcounter, $afc->is_used ? '1' : '-'];
    }

    return [
      'data' => $data,
      'area' => $seat
    ];
  }

  public static function GetBuildDailyData($fs, $date){
    $data = [];
    $seat = [];
    $fsintv = AreaUtilDaily::where('building_id', $fs->id)
      ->where('report_date', $date)
      ->orderBy('floor_id', 'ASC')
      ->get();

    $seatidcounter = -1;
    $lastseatid = 0;

    foreach($fsintv as $afc){
      $data[] = [
        'name' => $afc->MeetingArea->label ?? 'Deleted',
        'hours' => $afc->total_hour_used
      ];
    }

    return $data;
  }

  public static function GetBuildWeeklyIntvData($fs, $date){
    $data = [];
    $seat = [];

    $indate = new Carbon($date);

    $fsintv = AreaUtilDaily::where('building_id', $fs->id)
      ->whereMonth('report_date', $indate->month)
      ->whereYear('report_date', $indate->year)
      ->orderBy('seat_id', 'ASC')
      ->get();

    $seatidcounter = -1;
    $lastseatid = 0;
    $datastorage = [];

    foreach($fsintv as $afc){
      $ti = new \Carbon\Carbon($afc->report_date);
      if($afc->seat_id != $lastseatid){

        // push the previous values into data
        foreach ($datastorage as $key => $value) {
          $data[] = [$key, $seatidcounter, $value];
        }

        // reset the container
        $datastorage = [];

        $lastseatid = $afc->seat_id;
        $seat[] = $afc->MeetingArea->label ?? 'Deleted';
        $seatidcounter++;
      }

      $day = $ti->format('w') + 0;

      isset($datastorage[$day]) ? $datastorage[$day] += $afc->total_hour_used : $datastorage[$day] = $afc->total_hour_used;

    }

    // push the last set of data
    foreach ($datastorage as $key => $value) {
      $data[] = [$key, $seatidcounter, $value];
    }

    return [
      'data' => $data,
      'area' => $seat
    ];
  }

  public static function GetBuildMonthlyData($fs, $date){
    $data = [];
    $seat = [];
    $indate = new Carbon($date);

    $fsintv = AreaUtilDaily::where('building_id', $fs->id)
      ->whereMonth('report_date', $indate->month)
      ->whereYear('report_date', $indate->year)
      ->selectRaw('seat_id, sum(total_hour_used) as total_hours')
      ->groupBy('seat_id')
      ->orderBy('seat_id', 'ASC')
      ->get();

    $lastseatid = 0;

    foreach($fsintv as $afc){
      $area = Seat::find($afc->seat_id);
      $data[] = [
        'name' => $area ? $area->label : 'Deleted',
        'hours' => $afc->total_hours
      ];
    }

    return $data;
  }
}
