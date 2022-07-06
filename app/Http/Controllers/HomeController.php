<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seat;
use App\Models\Building;
use App\Models\SapLeaveInfo;
use App\Models\UserSeatBooking;
use App\common\PushNotiHelper;
use App\common\SeatHelper;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $req)
    {
        $this->middleware('auth');
        //$req=$this->crud->getRequest();
        //return redirect(route('backpack'));
        $user = backpack_user();


        return view('home2', ['user' => $user, 'title' => 'User Home']);

    }

    public function testSendEmail(Request $req){
      $pnid = backpack_user()->pushnoti_id;
      $namae = backpack_user()->name;
      if($pnid){
        $ret = PushNotiHelper::SendPushNoti(
          $pnid,
          'hai ' . $namae,
          'sent from ' . gethostname(),
          backpack_user()->staff_no,
          backpack_user()->id,
          'manual test'
        );
      } else {
        $ret = [
          'name' => $namae,
          'err' => 'null pushnotify id. login with mobile app first'
        ];
      }


      dd($ret);

    }

    public function playground(Request $req){

            // $reflFunc = new \ReflectionMethod($this->crud, 'validateRequest');
            // dd($reflFunc->getFileName());
      // $build = Building::find(1);
      // $asal = SeatHelper::GetBuildingSeatSummary($build, '2021-08-17');
      // $mods = SeatHelper::GetBuildingSeatSummary($build, '2021-08-17');
      //
      // usort($mods, function($a, $b) {
      //   return $a['max_occupied_seat'] <=> $b['max_occupied_seat'];
      // });
      //
      // dd([
      //   'ori' => $asal,
      //   'mod' => $mods
      // ]);

      return view('inventory.dlqr');


      // $test = \App\common\UserHelper::GetUserStat('2022-02-02');
      // dd($test);
    }

    public function ldapRaw(Request $req){
      $sid = backpack_user()->staff_no;
      if($req->filled('staff_no')){
        $sid = $req->staff_no;
      }

      return \App\common\LdapHelper::fetchUserRaw($sid);
    }

    public function ldapRet(Request $req){
      $sid = backpack_user()->staff_no;
      if($req->filled('staff_no')){
        $sid = $req->staff_no;
      }

      return \App\common\LdapHelper::fetchUser($sid);
    }

    public function updateseatrefs(){
      $start = new \Carbon\Carbon;
      // patch the seat obj
      $seats = Seat::all();
      foreach ($seats as $key => $value) {
        $flooro = $value->floor_section->Floor;
        $value->building_id = $flooro->building_id;
        $value->floor_id = $flooro->id;
        $value->save();
      }

      $doneseat = new \Carbon\Carbon;

      $usbs = UserSeatBooking::all();
      foreach ($usbs as $key => $value) {
        $seat = $value->Seat;
        if($seat){
          $value->floor_id = $seat->floor_id;
          $value->floor_section_id = $seat->floor_section_id;
          $value->building_id = $seat->building_id;
          $value->save();

        } else {
          // delete if the seat is not exist
          $value->delete();
        }
      }


      $doneusb = new \Carbon\Carbon;

      dd([
        'start' => $start->toDateTimeString(),
        'done_seat' => $doneseat->toDateTimeString(),
        'done_usb' => $doneusb->toDateTimeString()
      ]);
    }


}
