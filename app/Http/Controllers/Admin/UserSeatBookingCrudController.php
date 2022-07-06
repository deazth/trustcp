<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserSeatBookingRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\common\CommonHelper;
use App\common\SeatHelper;
use App\common\CheckinHelper;
use App\Models\Floor;
use App\Models\FloorSection;
use App\Models\Building;

/**
 * Class UserSeatBookingCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserSeatBookingCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\UserSeatBooking::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/reserveseat');
        CRUD::setEntityNameStrings('userseatbooking', 'My Seat Reservations');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
      CRUD::setSubheading(' ');
        // CRUD::setFromDb(); // columns
        CRUD::addClause('where', 'user_id', '=', backpack_user()->id);
        $req = CRUD::getRequest();
        if($req->filled('id')){
          CRUD::addClause('where', 'id', '=', $req->id);
        }

        /*
        seat
        loc
        from
        to
        status
        remark
        */

        CRUD::addColumn( [
          'name' => 'seat_id',
          'label' => 'Seat No',
          'type' => 'relationship',
          'entity'    => 'Seat',
          'attribute' => 'label',
          'model'     => Seat::class,
          "relation_type" => "BelongsTo",
        ]);

        CRUD::addColumn([
          'name' => 'loc',
          'label' => 'Location',
          'type' => 'relationship',
          'entity'    => 'Seat',
          'attribute' => 'parent_long_label',
          'model'     => Seat::class,
          "relation_type" => "BelongsTo",
          'priority' => 1
        ]);

        CRUD::addColumn([
          'name' => 'start_time',
          'label' => 'From',
          'type' => 'datetime'
        ]);

        CRUD::addColumn([
          'name' => 'end_time',
          'label' => 'To',
          'type' => 'datetime'
        ]);

        CRUD::addColumn([
          'name' => 'status',
          'label' => 'Status',
          'type' => 'text'
        ]);

        CRUD::addColumn([
          'name' => 'remark',
          'label' => 'Remark',
          'type' => 'text'
        ]);

        $this->crud->addButtonFromView('top', 'seatbooking', 'seatbooking', 'end');
        $this->crud->addButtonFromView('top', 'scanqr', 'scanqr', 'end');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(UserSeatBookingRequest::class);

        CRUD::setFromDb(); // fields

    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function seatfinder(){
      $today = new \Carbon\Carbon;
      $today->addMinutes(5);

      $this->data['title'] = 'Seat Reservation';
      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'Seat Bookings' => backpack_url('userseatbooking'),
          'Seat Finder' => false
      ];
      $kerud = app()->make('crud');

      // dd(SeatHelper::GetBookableBuilding());

      $blist = SeatHelper::GetBookableBuilding(); // = \App\Models\Building::all()->pluck('building_name', 'id')->toArray();


      $kerud->addField(
      [
        'name' => 'building_id',
        'label' => 'Building',
        'type' => 'select2_from_array',
        'options' => $blist,
        'attributes' => ['required' => 'required'],
        'allows_null' => false,
        'wrapper'   => [
          'class'      => 'form-group col-md-4'
        ],
      ]);

      $kerud->addField([
        'name' => 'floor_id',
        'type' => 'select2_from_ajax',
        'label' => 'Floor',
        // 'entity' => 'ActivityType',
        'attribute' => 'floor_name',
        'model'       => Floor::class,
        'data_source' => route('wa.getFloorList'),
        'allows_null' => true,
        'include_all_form_fields' => true,
        'dependencies' => ['building_id'],
        'minimum_input_length' => 0,
        'placeholder' => 'Optional',
        'wrapper'   => [
          'class'      => 'form-group col-md-4'
        ],
      ]);

      $kerud->addField([
        'name' => 'floor_section_id',
        'type' => 'select2_from_ajax',
        'label' => 'Section',
        // 'entity' => 'ActivityType',
        'attribute' => 'label',
        'model'       => FloorSection::class,
        'data_source' => route('wa.getFloorSectionList'),
        'allows_null' => true,
        'include_all_form_fields' => true,
        'dependencies' => ['building_id', 'floor_id'],
        'minimum_input_length' => 0,
        'placeholder' => 'Optional',
        'wrapper'   => [
          'class'      => 'form-group col-md-4'
        ],
      ]);

      $kerud->addField([   // DateTime
          'name'  => 'start_time',
          'label' => 'From',
          'type'  => 'datetime_picker',

          // optional:
          'datetime_picker_options' => [
              'format' => 'DD/MM/YYYY HH:mm',
              'language' => 'en'
          ],
          'default' => $today->toDateTimeString(),
          'attributes' => ['required' => 'required'],
          'allows_null' => false,
          'wrapper'   => [
            'class'      => 'form-group col-lg-6'
          ],
          'hint' => 'Note: At least 5 minutes in advance'
      ]);

      $kerud->addField([   // DateTime
          'name'  => 'end_time',
          'label' => 'To',
          'type'  => 'datetime_picker',

          // optional:
          'datetime_picker_options' => [
              'format' => 'DD/MM/YYYY HH:mm',
              'language' => 'en'
          ],
          'attributes' => ['required' => 'required'],
          'allows_null' => false,
          'wrapper'   => [
            'class'      => 'form-group col-lg-6'
          ],
      ]);


      $this->data['crud'] = $kerud;

      return view('inventory.seatfinder', $this->data);
    }

    public function seatSearchResult(){
      $req = request();
      // dd($req->all());
      $today = new \Carbon\Carbon;
      $today->addMinutes(3);
      $sinput = new \Carbon\Carbon($req->start_time);
      $einput = new \Carbon\Carbon($req->end_time);

      if($sinput->gt($einput)){
        \Alert::error('To time is before from time')->flash();
        return redirect()->back()->withInput();
      }

      if($sinput->lt($today)){
        \Alert::error('From time must be at least 5 minute from current time')->flash();
        return redirect()->back()->withInput();
      }

      if($sinput->diffInDays($einput) > CommonHelper::GetCConfig('long booking days', 7)){
        \Alert::error('Reservation duration is too long')->flash();
        return redirect()->back()->withInput();
      }

      $bd = Building::find($req->building_id);
      if($bd){

      } else {
        \Alert::error('Selected building no longer exist')->flash();
        return redirect()->back()->withInput();
      }

      $res = SeatHelper::FindBookableSeatV2($req->start_time, $req->end_time, $req->building_id, $req->floor_id, $req->floor_section_id);

      // dd($res);
      $this->data['title'] = 'Seat Booking';
      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'Seat Bookings' => backpack_url('userseatbooking'),
          'Search Result' => false
      ];
      $kerud = app()->make('crud');
      $this->data['crud'] = $kerud;
      $this->data['seatlist'] = $res;
      $this->data['buildname'] = $bd->building_name;
      $this->data['stime'] = $req->start_time;
      $this->data['etime'] = $req->end_time;

      return view('inventory.seatbookresult', $this->data);
    }

    public function doSeatBooking(){
      $req = request();

      // dd($req->all());

      // $av = SeatHelper::DoSeatBooking($req->seat_id, $req->time_slot, $req->indate, backpack_user());
      $av = SeatHelper::DoSeatBookingV2($req->seat_id, $req->stime, $req->etime, backpack_user());
      if(isset($av['error'])){
        \Alert::error($av['error'])->flash();
        return redirect()->back()->withInput();
        // return redirect()->route('userseatbook.finder');
      }

      \Alert::success('Seat booked')->flash();
      return redirect(backpack_url('reserveseat'));


    }

    public function destroy($id){
      $did = $this->crud->getCurrentEntryId() ?? $id;
      $usb = \App\Models\UserSeatBooking::findOrFail($did);
      // just in case, dont allow delete other user's booking
      if($usb->user_id != backpack_user()->id){
        abort(403);
      }

      // if it's an active booking, reduce the counter for that slot
      $usb->CancelBooking('Deleted', 'Deleted');
      if(isset($usb->seat_checkin_id)){
        CheckinHelper::SeatCheckout($usb->User, $usb->seat_checkin_id);
      }

      // then do the actual delete
      return (string) $usb->delete();

      // this is to call the trait's delete
      // $response = $this->traitDestroy($id);
      // return $response;
    }
}
