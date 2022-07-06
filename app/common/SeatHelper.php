<?php

namespace App\common;

use App\Models\Seat;
use App\Models\FloorSection;
use App\Models\Floor;
use App\Models\Building;
use App\Models\User;
use App\Models\SeatCheckin;
use App\Models\UserSeatBooking;
use App\Models\SeatBooking;
use App\Models\UtilFloorSectionIntv;
use App\Models\UtilFloorSectionDaily;
use \Carbon\Carbon;


class SeatHelper
{

  /*
  'booking_status' => [
    'Booked' => 'Booked',
    'Expired' => 'Expired',
    'Ended' => 'Ended',
    'Checked-in' => 'Checked-in',
    'Rejected' => 'Rejected',
    'Cancelled' => 'Cancelled'
  ]

  'time_slot' => [
    1 => 'Morning (8 - 13)',
    2 => 'Evening (14 - 18)',
    3 => 'Full Day (8 - 18)'
  ]

  */

  public static function GetBookableBuilding(){
    // fc with bookable seat
    $fclist = Seat::where('allow_booking', true)->where('seat_type', 'Seat')
      ->where('status', 1)->select('floor_section_id')->distinct()->pluck('floor_section_id');

    // floor with bookable fc
    $flist = FloorSection::whereIn('id', $fclist)
      ->where('status', 1)
      ->select('floor_id')->distinct()->pluck('floor_id');

    // building with bookable f
    $blist = Floor::whereIn('id', $flist)
      ->where('status', 1)
      ->select('building_id')->distinct()->pluck('building_id');

    // return the building info
    return Building::find($blist)->pluck('building_name', 'id')->sort();
  }

  public static function FindBookableSeat($building_id, $timeslot, $date){
    $seatlist = [];

    // if today, check if the timeslot is in the past
    $sameday = self::CheckSlotIsStillValid($timeslot, $date);
    if(isset($sameday['error'])){
      return $sameday;
    }


    $building = Building::find($building_id);

    // for each seats in the building
    if($building){
      foreach($building->Floors as $floor){
        foreach($floor->FloorSections as $fc){
          foreach($fc->BookableSeats as $seat){
            $sedata = self::CheckSeatAvailabilityForBook($seat, $timeslot, $date, $sameday);
            if($sedata){
              $seatlist[] = $sedata;
            }
          }
        }
      }
    }

    return ['seatlist' => $seatlist, 'building' => $building];
  }

  public static function FindBookableSeatV2($start_time, $end_time, $building_id, $floor_id = false, $fc_id = false){
    // find seat that has been reserved
    $stime = new Carbon($start_time);
    $stime->second = 0;
    $etime = new Carbon($end_time);
    $etime->second = 0;

    // find the seat that is not available
    $qb = UserSeatBooking::query();
    $qb->where('start_time', '<', $etime->toDateTimeString());
    $qb->where('end_time', '>', $stime->toDateTimeString());

    if($fc_id){
      $qb->where('floor_section_id', $fc_id);
    } else {
      if($floor_id){
        $qb->where('floor_id', $floor_id);
      } else {
        $qb->where('building_id', $building_id);
      }
    }

    $qb->whereIn('status', ['Booked','Checked-in']);

    $notavail = $qb->get()->pluck('seat_id')->toArray();

    // list out the available seats
    $qb = Seat::query();

    $qb->where('status', 1);
    $qb->where('seat_type', 'Seat');
    $qb->where('allow_booking', true);
    if($fc_id){
      $qb->where('floor_section_id', $fc_id);
    } else {
      if($floor_id){
        $qb->where('floor_id', $floor_id);
      } else {
        $qb->where('building_id', $building_id);
      }
    }
    if(sizeof($notavail) > 0){
      $qb->whereNotIn('id', $notavail);
    }

    $res = $qb->get();
    $ret = [];

    foreach ($res as $key => $value) {
      $ret[$value->floor_id]['name'] = $value->Floor->floor_name;
      $ret[$value->floor_id]['gotlayout'] = $value->Floor->layout_file ? true : false;
      if(isset($ret[$value->floor_id]['count'])){
        $ret[$value->floor_id]['count']++;
      } else {
        $ret[$value->floor_id]['count'] = 1;
        $ret[$value->floor_id]['id'] = $value->floor_id;
      }

      $ret[$value->floor_id]['fcs'][$value->floor_section_id]['name'] = $value->floor_section->label;
      $ret[$value->floor_id]['fcs'][$value->floor_section_id]['gotlayout'] = $value->floor_section->layout_file ? true : false;
      if(isset($ret[$value->floor_id]['fcs'][$value->floor_section_id]['count'])){
        $ret[$value->floor_id]['fcs'][$value->floor_section_id]['count']++;
      } else {
        $ret[$value->floor_id]['fcs'][$value->floor_section_id]['count'] = 1;
        $ret[$value->floor_id]['fcs'][$value->floor_section_id]['id'] = $value->floor_section_id;
      }
      $ret[$value->floor_id]['fcs'][$value->floor_section_id]['seats'][$value->id] = ['name' => $value->label, 'id' => $value->id];
      // $ret[$value->floor_id][$value->floor_section_id][$value->id] = $value->label;
    }

    return $ret;

  }

  public static function CheckSlotIsStillValid($timeslot, $date){
    $now = new \Carbon\Carbon;
    $datecheck = new \Carbon\Carbon;

    // if today, check if the timeslot is in the past
    if($date == $now->toDateString()){
      // same day booking.
      $datecheck->hour = 13;
      $datecheck->minute = 0;
      $datecheck->second = 0;

      // past 2. no longer allowed to book for today
      if($now->gte($datecheck)){
        return ['error' => 'No longer allowed to book for today'];
      }

      // then check if try to book morning slot after 8.30
      $datecheck->hour = 8;
      if($now->gte($datecheck) && $timeslot != 2){
        return ['error' => 'Already passed the morning slot'];
      }

      return true;
    }

    return false;
  }

  public static function CheckSeatAvailabilityForBook($seat, $timeslot, $date, $sameday){
    $availc = $seat->seat_capacity;
    $utilizedc = 0;

    // for same day booking, take current occupied into consideration
    if($sameday){
      $utilizedc = $seat->seat_utilized;
      $availc -= $utilizedc;
    }

    if($timeslot == 3){
      // check both timeslot
      $sb1 = $seat->SeatBooking($date, 1);
      $sb2 = $seat->SeatBooking($date, 2);
      $booked = $sb1->booked_count < $sb2->booked_count ? $sb2->booked_count : $sb1->booked_count;
      if($booked < $availc){
        $perc = ($utilizedc + $booked) / $seat->seat_capacity * 100;
        return [
          'seat' => $seat,
          'free' => $availc - $booked,
          'occupied' => $utilizedc,
          'booked' => $booked,
          'perc' => intval($perc),
          'max' => $seat->seat_capacity
        ];
      }
    } else {
      $sb = $seat->SeatBooking($date, $timeslot);
      $booked = $sb->booked_count;
      // if still got leftover
      if($booked < $availc){
        $perc = ($utilizedc + $booked) / $seat->seat_capacity * 100;
        return [
          'seat' => $seat,
          'free' => $availc - $booked,
          'occupied' => $utilizedc,
          'booked' => $booked,
          'perc' => intval($perc),
          'max' => $seat->seat_capacity
        ];
      }
    }

    return false;
  }

  public static function DoSeatBookingV2($seat_id, $from, $to, $user){
    // check if this seat is still available
    $stime = new Carbon($from);
    $stime->second = 0;
    $etime = new Carbon($to);
    $etime->second = 0;

    $seat = Seat::find($seat_id);
    if($seat){

    } else {
      return [
        'error' => 'Seat no longer exist'
      ];
    }

    $qb = UserSeatBooking::where('start_time', '<', $etime->toDateTimeString())
      ->where('end_time', '>', $stime->toDateTimeString())
      ->where('seat_id', $seat_id)
      ->whereNotIn('status', ['Cancelled', 'Expired', 'Ended'])
      ->first();

    if($qb){
      return [
        'error' => 'No occupied'
      ];
    }

    // no overlap. proceed
    $usb = new UserSeatBooking;
    $usb->user_id = $user->id;
    $usb->seat_id = $seat_id;
    $usb->start_time = $stime->toDateTimeString();
    $usb->end_time = $etime->toDateTimeString();
    $usb->status = 'Booked';
    $usb->time_slots = 0;
    $usb->building_id = $seat->building_id;
    $usb->floor_id = $seat->floor_id;
    $usb->floor_section_id = $seat->floor_section_id;
    $usb->save();

    return $usb;

  }

  public static function DoSeatBooking($seat_id, $slot, $date, $user){
    // double check if the slot is still valid
    $sameday = self::CheckSlotIsStillValid($slot, $date);
    if(isset($sameday['error'])){
      return $sameday;
    }

    // double check seat availability
    $seat = Seat::find($seat_id);
    if($seat){
      // get seat availability
      $av = SeatHelper::CheckSeatAvailabilityForBook($seat, $slot, $date, $sameday);
      if($av['free'] > 0){
        // book the seat
        if($slot == 3){
          $sb1 = $seat->SeatBooking($date, 1);
          $sb2 = $seat->SeatBooking($date, 2);
          $sb1->increment('booked_count');
          $sb2->increment('booked_count');
        } else {
          $sb = $seat->SeatBooking($date, $slot);
          $sb->increment('booked_count');
        }
        $starttime = new \Carbon\Carbon($date);
        $endtime = new \Carbon\Carbon($date);
        $starttime->hour = 8;
        $starttime->minute = 30;
        $endtime->hour = 18;
        $endtime->minute = 0;

        if($slot == 1){
          $endtime->hour = 13;
        }

        if($slot == 2){
          $starttime->hour = 14;
          $starttime->minute = 0;
        }

        // cancel overlaps
        self::CancelOverlapBooking($user->id, $date, $slot);

        // create the user seat booking obj
        $usb = new UserSeatBooking;
        $usb->user_id = $user->id;
        $usb->seat_id = $seat->id;
        $usb->start_time = $starttime->toDateTimeString();
        $usb->end_time = $endtime->toDateTimeString();
        $usb->status = 'Booked';
        $usb->time_slots = $slot;
        $usb->save();

        return true;
      }

      // no more seat available
      return ['error' => 'Selected seat is already full'];
    }
    return ['error' => 'Selected seat no longer exist'];
  }

  public static function CancelOverlapBooking($user_id, $date, $slot){
    if($slot == 3){
      $slot_to_find = [1,2,3];
    } elseif ($slot == 2) {
      $slot_to_find = [2,3];
    } else {
      $slot_to_find = [1,3];
    }

    $overlaps = UserSeatBooking::where('user_id', $user_id)
      ->whereDate('start_time', $date)
      ->where('status', 'Booked')
      ->whereIn('time_slots', $slot_to_find)->get();

    foreach($overlaps as $usb){
      $usb->CancelBooking('Cancelled', 'Overlapping bookings');
    }
  }

  public static function GetFloorSecSeatUtilSummary($floor, $date){
    $data = [];
    foreach($floor->FloorSections->sortBy('label') as $afc){
      $floordetail = UtilFloorSectionDaily::where('floor_section_id', $afc->id)
        ->whereDate('report_date', $date)->first();

      if($floordetail){
        $data[] = [
          'fc_id' => $afc->id,
          'fc_name' => $afc->label,
          'total_seat' => $floordetail->total_seat,
          'max_occupied_seat' => $floordetail->max_occupied_seat,
          'free_seat' => $floordetail->free_seat,
          'utilization' => $floordetail->utilization
        ];
      } else {
        $data[] = [
          'fc_id' => $afc->id,
          'fc_name' => $afc->label,
          'total_seat' => 0,
          'max_occupied_seat' => 0,
          'free_seat' => 0,
          'utilization' => 0
        ];
      }
    }

    return $data;
  }

  public static function GetBuildingSeatSummary($building, $date){
    $data = [];
    foreach($building->Floors->sortBy('floor_name') as $afc){
      $floordetail = UtilFloorSectionDaily::where('floor_id', $afc->id)
        ->whereDate('report_date', $date)->get();

      $data[] = [
        'fc_id' => $afc->id,
        'fc_name' => $afc->floor_name,
        'total_seat' => $floordetail->sum('total_seat'),
        'max_occupied_seat' => $floordetail->sum('max_occupied_seat'),
        'utilization' => round($floordetail->average('utilization') ?? 0)
      ];
    }

    return $data;
  }

  public static function GetBuildingSeatMonthlySummary($building, $date){
    $data = [];

    $indate = new Carbon($date);

    foreach($building->Floors->sortBy('floor_name') as $afc){
      $floordetail = UtilFloorSectionDaily::where('floor_id', $afc->id)
        ->whereMonth('report_date', $indate->month)
        ->whereYear('report_date', $indate->year)
        ->get();

      $utilization = $floordetail->average('utilization') ?? 0 ;

      $data[] = [
        'fc_id' => $afc->id,
        'fc_name' => $afc->floor_name,
        'total_seat' => $floordetail->average('total_seat'),
        'max_occupied_seat' => $floordetail->average('max_occupied_seat'),
       // 'utilization' => $floordetail->average('utilization') ?? 0,
        'utilization' => $utilization,
        'pretty_util' => number_format($utilization , 2,'.','')
      ];
    }

    return $data;
  }

  public static function GetFsIntvData($fs, $date){
    $data = [];
    $fsintv = UtilFloorSectionIntv::where('floor_section_id', $fs->id)
      ->whereDate('report_time', $date)
      ->orderBy('report_time', 'ASC')
      ->get();

    foreach($fsintv as $afc){
      $ti = new \Carbon\Carbon($afc->report_time);
      $data[] = [
        'time' => $ti->format('H'),
        'total_seat' => $afc->total_seat,
        'occupied_seat' => $afc->occupied_seat,
        'free_seat' => $afc->free_seat,
        'utilization' => $afc->utilization
      ];
    }

    return $data;
  }

  public static function GetFloorIntvData($fs, $date){
    $data = [];
    $fsintv = UtilFloorSectionIntv::where('floor_id', $fs->id)
      ->whereDate('report_time', $date)
      ->orderBy('report_time', 'ASC')
      ->groupBy('report_time')
      ->selectRaw('report_time, SUM(total_seat) as total_seat, SUM(occupied_seat) as occupied_seat, SUM(free_seat) as free_seat, AVG(utilization) as utilization')
      ->get();

    foreach($fsintv as $afc){
      $ti = new \Carbon\Carbon($afc->report_time);
      $data[] = [
        'time' => $ti->format('H'),
        'total_seat' => $afc->total_seat,
        'occupied_seat' => $afc->occupied_seat,
        'free_seat' => $afc->free_seat,
        'utilization' => $afc->utilization
      ];
    }

    return $data;
  }

  public static function GetBuildIntvData($fs, $date){
    $data = [];
    $fsintv = UtilFloorSectionIntv::where('building_id', $fs->id)
      ->whereDate('report_time', $date)
      ->orderBy('report_time', 'ASC')
      ->groupBy('report_time')
      ->selectRaw('report_time, SUM(total_seat) as total_seat, SUM(occupied_seat) as occupied_seat, SUM(free_seat) as free_seat, AVG(utilization) as utilization')
      ->get();

    foreach($fsintv as $afc){
      $ti = new \Carbon\Carbon($afc->report_time);
      $data[] = [
        'time' => $ti->format('H'),
        'total_seat' => $afc->total_seat,
        'occupied_seat' => $afc->occupied_seat,
        'free_seat' => $afc->free_seat,
        'utilization' => $afc->utilization
      ];
    }

    return $data;
  }

  public static function GetBuildMonthIntvData($fs, $date){
    $data = [];
    $seat = [];
    $indate = new Carbon($date);
    $fsintv = UtilFloorSectionDaily::where('building_id', $fs->id)
      ->whereMonth('report_date', $indate->month)
      ->whereYear('report_date', $indate->year)
      ->orderBy('floor_id', 'ASC')
      ->get();

    $seatidcounter = -1;
    $lastseatid = 0;
    $datastorage = [];

    foreach($fsintv as $afc){
      $ti = new \Carbon\Carbon($afc->report_date);
      if($afc->floor_id != $lastseatid){

        // push the previous values into data
        foreach ($datastorage as $key => $value) {
          $data[] = [$key, $seatidcounter, $value];
        }

        // reset the container
        $datastorage = [];

        $lastseatid = $afc->floor_id;
        $seat[] = $afc->Floor->floor_name ?? 'Deleted';
        $seatidcounter++;
      }

      $day = $ti->format('j') + 0;
      $val = intval($afc->utilization);
      $datastorage[$day] = $val;

      // isset($datastorage[$day]) ? $datastorage[$day] += $val : $datastorage[$day] = $val;
    }

    // push the last set of data
    foreach ($datastorage as $key => $value) {
      $data[] = [$key, $seatidcounter, $value];
    }

    return [
      'data' => $data,
      'seat' => $seat
    ];
  }

  public static function GetSeatCurrentStatus($seat_qr, $user_id){
    $date = date('Y-m-d');
    $seat = Seat::where('qr_code', $seat_qr)->first();
    if($seat){

      $status = 'Available';

      // check for status
      if($seat->status == 1 && $seat->floor_section->status == 1 && $seat->Floor->status == 1){
        // all level still active
      } else {
        return [
          'id' => $seat->id,
          'label' => $seat->label,
          'status' => 'Inactive',
          'type' => $seat->seat_type,
          'location' => $seat->floor_section->long_label,
          'extra' => []
        ];
      }

      $extra = [];

      // check if the seat is already fully occupied
      if($seat->seat_type == 'Seat'){

        // check for self reserve
        $usbs = UserSeatBooking::where('seat_id', $seat->id)
          ->whereDate('start_time', date('Y-m-d'))
          ->where('status', 'Booked')
          ->where('user_id', $user_id)->count();

        if($usbs > 0){
          $status = 'Available';
        } else {
          if($seat->seat_utilized >= $seat->seat_capacity){
            $status = 'Occupied / Full';
          } else {
            // check for upcoming booking
            $sb1 = $seat->SeatBooking($date, 1)->booked_count;
            $sb2 = $seat->SeatBooking($date, 2)->booked_count;
            if($sb1 + $sb2 > 0){
              $status = 'Seat reserved by others';
            }
          }
        }
      } else {
        // meeting area. check for nearby events
        $events = CheckinHelper::GetAreaEvents($seat);
        foreach($events as $ev){
          $extra[] = [
            'ev_id' => $ev->id,
            'name' => $ev->event_name,
            'org' => $ev->organizer->id_name,
            'startt' => $ev->start_time,
            'endt' => $ev->end_time,
            'qr_code' => $ev->qr_code
          ];
        }
      }

      return [
        'id' => $seat->id,
        'label' => $seat->label,
        'status' => $status,
        'type' => $seat->seat_type,
        'location' => $seat->floor_section->long_label,
        'extra' => $extra
      ];
    } else {
      return [
        'id' => 0,
        'label' => 'Invalid QR',
        'status' => '404',
        'type' => 'N/A',
        'location' => '',
        'extra' => []
      ];
    }

  }

  public static function GetFloorList($building_id, $filter = false){
    $list = Floor::query();
    $list->where('building_id', $building_id);
    if($filter != false && $filter != ''){
      $list->where('floor_name', 'LIKE', '%' . $filter . '%');
    }

    return $list->orderBy('floor_name')->paginate(10);
  }

  public static function GetFloorSectionList($floor_id, $filter = false){
    $list = FloorSection::query();
    $list->where('floor_id', $floor_id);
    if($filter != false && $filter != ''){
      $list->where('label', 'LIKE', '%' . $filter . '%');
    }

    return $list->orderBy('label')->paginate(10);
  }

  // public static function GetFloorSeatSummary($floor, $date){
  //   $data = [];
  //   foreach($floor->FloorSections as $afc){
  //     $floordetail = UtilFloorSectionDaily::where('floor_section_id', $afc->id)
  //       ->whereDate('report_date', $date)->first();
  //
  //     $data[] = [
  //       'fc_id' => $afc->id,
  //       'fc_name' => $afc->label,
  //       'total_seat' => $floordetail->sum('total_seat'),
  //       'max_occupied_seat' => $floordetail->sum('max_occupied_seat'),
  //       'utilization' => $floordetail->average('utilization') ?? 0
  //     ];
  //   }
  //
  //   return $data;
  // }


}
