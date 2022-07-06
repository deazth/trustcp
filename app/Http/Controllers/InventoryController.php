<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Floor;
use App\Models\FloorSection;
use App\Models\User;
use App\Models\AreaBooking;
use App\Models\SeatCheckin;
use App\Models\Seat;
use Backpack\CRUD\app\Library\Widget;
use App\common\CheckinHelper;
use App\common\MeetingAreaHelper;
use App\common\SeatHelper;

class InventoryController extends Controller
{
  public function showCount($t, $id){
    /* type
      f = floor
      fs = floor section
      b = building
      m = meeting?
    */

    // defaults
    $nexttype = 'b';
    $class = \App\Models\Building::class;

    if(isset($t) && isset($id)){
      if ($t == 'f') {
        $nexttype = 'fc';
        $class = \App\Models\Building::class;
      } elseif ($t == 'fc') {
        $nexttype = 'm';
        $class = \App\Models\Floor::class;
      } else {
        abort(403);
      }

      $this->data['title'] = 'Agile Seats'; // set the page title

      return $this->showInvCount($nexttype, $class, $id, $t);
    }

    // no type. return the building caps
    return $this->showBuildingCount();
  }

  public function showInvCount($nexttype, $class, $id, $curtype){

    $theobj = $class::find($id);

    if($theobj){
      if($curtype == 'f'){
        $this->data['breadcrumbs'] = [
            'Home' => backpack_url('dashboard'),
            'Building overview' => route('inventory.seat.showbuilding'),
            $theobj->building_name => false
        ];
      } else {
        $this->data['breadcrumbs'] = [
            'Home' => backpack_url('dashboard'),
            'Building overview' => route('inventory.seat.showbuilding'),
            $theobj->Buildings->building_name => route('inventory.seat.showcount', ['t' => 'f', 'id' => $theobj->building_id]),
            $theobj->floor_name => false
        ];
      }


      $anaks = $theobj->GetAnak();
      $catkontrol = new \App\Http\Controllers\Admin\Charts\InvUsageChartController($curtype, $id);
      $thechards = [
        'chart' => $catkontrol->chart,
        'path' => $catkontrol->getLibraryFilePath(),
        'title' => $theobj->GetLabel() . ' Utilization'
      ];
      $this->data['aca'] = $thechards;
    }

    $this->data['nexttype'] = $nexttype;
    $this->data['title'] = 'Workspace Availability';

    return view('inventory.buildlist', $this->data);
  }

  public function showBuildingCount(){
    $this->data['title'] = 'Agile Seats';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'Agile Office' => false,
    ];
    // $buildings = Building::all();

    $catkontrol = new \App\Http\Controllers\Admin\Charts\InvUsageChartController('b', 1);
    $thechards = [
      'chart' => $catkontrol->chart,
      'path' => $catkontrol->getLibraryFilePath(),
      'title' => 'Building Utilization'
    ];
    $this->data['aca'] = $thechards;
    $this->data['nexttype'] = 'f';

    // dd($this->data);


    return view('inventory.buildlist', $this->data);
  }

  public function showAreaCalendar(Request $req){
    $areaid = 0;

    if($req->filled('areaid')){
      $areaid = $req->areaid;
    }
    $this->data['title'] = 'Meeting Area';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'My Area Bookings' => backpack_url('userareabooking'),
        'Area Calendar' => false
    ];

    // get the list of meeting area
    $arealist = [];
    $buildings = Building::all();

    foreach($buildings as $ab){
      foreach($ab->Floors as $af){
        foreach ($af->FloorSections as $afc) {
          foreach($afc->MeetingAreas as $area){
            $arealist[] = $area;
          }
        }
      }
    }

    $this->data['arealist'] = $arealist;

    $thearea = false;
    // then check for input, if any
    if($areaid){
      $thearea = \App\Models\Seat::where('id', $areaid)->where('status', 1)->where('seat_type', 'Meeting Area')->first();
    } else {
      // get the first area instead
      if(sizeof($arealist) > 0){
        $thearea = $arealist[0];
      }
    }

    if($thearea){

      $yesturday = new \Carbon\Carbon;
      $yesturday->subMonths(3);

      // find all events that ends yesterday onwards
      $bookings = \App\Models\AreaBooking::where('seat_id', $thearea->id)
        ->where('end_time', '>', $yesturday->toDateTimeString())
        ->whereIn('status', ['Active', 'Pending SB'])->get();

      $event = [];
      foreach($bookings as $buk){
        $event[] = \Calendar::event(
          $buk->event_name, //event title
          false, //full day event?
          $buk->start_time, //start time, must be a DateTime object or valid DateTime format (http://bit.ly/1z7QWbg)
          $buk->end_time, //end time, must be a DateTime object or valid DateTime format (http://bit.ly/1z7QWbg),
        	$buk->id, //optional event ID
          ['url' => route('inv.event.info', ['id' => $buk->id])]
        );
      }

      $calendar = \Calendar::addEvents($event);
      $calendar->setOptions([
        'header' => [
            'left' => 'prev,next, today',
            'center' => 'title',
            'right' => 'month,agendaWeek',
        ],
        'eventLimit' => true,
      ]);

      $this->data['gotdata'] = true;
      $this->data['calendar'] = $calendar;
      $this->data['marea'] = $thearea;
    } else {
      $this->data['gotdata'] = false;
    }

    return view('inventory.areacalendar', $this->data);
  }

  public function webCheckInForm(){
    return view('inventory.checkin', ['title' => 'Workspace QR Scan']);
  }


  public function doEventCheckIn($qr){
    // dd(request()->all());
    // check if the event is valid
    $ab = AreaBooking::where('qr_code', $qr)->where('status', 'Active')->first();
    $lat = 0;
    $long = 0;
    if($ab){
      $data = CheckinHelper::GetMeetingAreaStatus($qr);
      // dd($data);
      return view('inventory.scanresult', $data);
    } else {
      \Alert::error('Invalid Event QR')->flash();
      return redirect()->route('inv.landing');
    }

  }

  public function reallyDoEventCheckin(Request $req){
    // dd(request()->all());
    // check if the event is valid
    $ab = AreaBooking::where('id', $req->sid)->where('status', 'Active')->first();
    if($ab){

      // check if event has ended
      $now = new \Carbon\Carbon;
      $event_end_time = new \Carbon\Carbon($ab->end_time);
      if($now->gt($event_end_time)){
        if($now->diffInMinutes($event_end_time) > 30){
          // dont allow to checkin event that ended 30 minutes ago
          \Alert::error('Event has ended')->flash();
          return redirect()->route('inv.landing');
        }
      }
      // or event havent started
      $event_start_time = new \Carbon\Carbon($ab->start_time);
      if($event_start_time->gt($now)){
        if($now->diffInMinutes($event_start_time) > 30){
          // dont allow to checkin event that ended 30 minutes ago
          \Alert::error('Event has not started yet')->flash();
          return redirect()->route('inv.landing');
        }
      }

      CheckinHelper::EventCheckIn(backpack_user(), $ab, 'Web Checkin', $req->lat, $req->long);
      \Alert::success('Attended ' . $ab->event_name)->flash();
      return redirect()->route('inv.landing');
    } else {
      \Alert::error('Invalid Event QR')->flash();
      return redirect()->route('inv.landing');
    }

  }

  public function workspaceLandingPage(){
    $this->data['title'] = 'Workspace';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'Workspace' => false
    ];
    $kerud = app()->make('crud');
    $this->data['crud'] = $kerud;
    $this->data['curc'] = backpack_user()->current_checkins;
    $this->data['resv'] = CheckinHelper::GetUserUpcomingReservation(backpack_user());
    return view('inventory.workspacelanding', $this->data);
  }

  public function doWebCheckIn($qr){
    $seat = Seat::where('qr_code', $qr)->where('status', 1)->first();
    if($seat){
      $data = SeatHelper::GetSeatCurrentStatus($qr, backpack_user()->id);
      // dd($data);
      return view('inventory.scanresult', $data);
    } else {
      \Alert::error('Invalid Seat QR')->flash();
      return redirect()->route('inv.landing');
    }


  }

  public function reallyDoWebCheckin(Request $req){
    $seat = Seat::where('id', $req->sid)->where('status', 1)->first();
    if($seat){

      if($seat->seat_type == 'Seat' && $seat->seat_utilized >= $seat->seat_capacity){
        \Alert::error('Seat full / occupied')->flash();
        return redirect()->route('inv.landing');
      }

      $ck = CheckinHelper::SeatCheckIn(backpack_user(), $seat, 'Web Checkin', $req->lat, $req->long);

      if(isset($ck['error'])){
        \Alert::error($ck['error'])->flash();
        return redirect()->route('inv.landing');
      }

      \Alert::success('Checked in at ' . $seat->label)->flash();
      return redirect()->route('inv.landing');
    } else {
      \Alert::error('Invalid Seat QR')->flash();
      return redirect()->route('inv.landing');
    }
  }

  public function doWebCheckOut($id){

    $sc = CheckinHelper::SeatCheckout(backpack_user(), $id);
    if($sc){
      \Alert::success('Check-out Successful')->flash();
      return redirect()->route('inv.landing');
    } else {
      \Alert::error('Not a valid checkin ID')->flash();
      return redirect()->route('inv.landing');
    }
  }

  public function doWebCheckOutAll(){
    CheckinHelper::SeatCheckoutAll(backpack_user());
    return redirect()->route('inv.landing');
  }

  public function getEventInfo($id){
    $book = AreaBooking::findOrFail($id);
    $this->data['event'] = $book;
    $this->data['title'] = 'Event Info';
    $this->data['breadcrumbs'] = [
        'Home' => backpack_url('dashboard'),
        'Area Bookings' => backpack_url('userareabooking'),
        'Area Finder' => route('inventory.area.calendar', ['areaid' => $book->seat_id]),
        'Event Info' => false
    ];

    $evinfo = MeetingAreaHelper::GetEventInfo($book);

    $this->data['evinfo'] = $evinfo;

    return view('inventory.eventdetails', $this->data);
  }

  public function getfloorlayout($id){

    $floor = Floor::find($id);
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
  }

  public function getfclayout($id){

    $floor = FloorSection::find($id);
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
  }


}
