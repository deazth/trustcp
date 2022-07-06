<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AreaBookingRequest;
use App\Http\Requests\AreaFinderRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use \Carbon\Carbon;
use App\common\MeetingAreaHelper;
use App\Models\Seat;
use App\Models\CommonConfig;

/**
 * Class UserAreaBookingCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserAreaBookingCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation ;
    // {
    //   update as protected traitUpdate;
    // }
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \App\Http\Controllers\Admin\Operations\CancelOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\AreaBooking::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/userareabooking');
        CRUD::setEntityNameStrings('Area Booking', 'My Area Bookings');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // CRUD::setFromDb(); // columns
        CRUD::setSubheading(' ');
        CRUD::addClause('where', 'user_id', '=', backpack_user()->id);

        $req = CRUD::getRequest();
        if($req->filled('id')){
          CRUD::addClause('where', 'id', '=', $req->id);
        }

        CRUD::addColumn([
          'name' => 'seat_id',
          'label' => 'Meeting Area',
          'type' => 'relationship',
          'entity'    => 'Meetingarea',
          'attribute' => 'label',
          'model'     => Seat::class,
          "relation_type" => "BelongsTo",
          'priority' => 1
        ]);

        CRUD::addColumn([
          'name' => 'longloc',
          'label' => 'Location',
          'type' => 'relationship',
          'entity'    => 'Meetingarea',
          'attribute' => 'parent_long_label',
          'model'     => Seat::class,
          "relation_type" => "BelongsTo",
          'priority' => 2
        ]);

        CRUD::addColumn([
          'name' => 'start_time',
          'label' => 'Start Time',
          'type' => 'datetime',
          'priority' => 1
        ]);

        CRUD::addColumn([
          'name' => 'event_name',
          'label' => 'Event',
          'type' => 'text',
          'priority' => 1
        ]);

        CRUD::addColumn([
          'name' => 'status',
          'label' => 'Status',
          'type' => 'text',
        ]);

        CRUD::addColumn([
          'name' => 'end_time',
          'label' => 'End Time',
          'type' => 'datetime'
        ]);

        CRUD::addColumn([
          'name' => 'user_remark',
          'label' => 'User Remark',
          'type' => 'text'
        ]);

        CRUD::addColumn([
          'name' => 'admin_remark',
          'label' => 'Admin Remark',
          'type' => 'text',
        ]);

        $this->crud->addButtonFromView('top', 'areafinder', 'areafinder', 'end');
        $this->crud->addButtonFromView('top', 'areacalendar', 'areacalendar', 'end');
        CRUD::addButtonFromModelFunction('line', 'getQRBtn', 'getQRBtn', 'end');
        CRUD::addButtonFromModelFunction('line', 'getAttendance', 'getAttendance', 'end');
        // $this->crud->addButtonFromView('line', 'cancel', 'cancel', 'end');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(AreaBookingRequest::class);

        CRUD::setFromDb(); // fields

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
      $cab = CRUD::getCurrentEntry();
      if($cab->user_id != backpack_user()->id){
        abort(403);
      }
      // dd($cab->extra_equip->pluck('id')->toArray());
      $seat = $cab->Meetingarea;

        // $this->setupCreateOperation();

      // visible forms
      CRUD::addField([
          'name'  => 'area_txt',
          'label' => 'Meeting Area',
          'type'  => 'text',
          'value' => $seat->long_label,
          'attributes' => ['readonly' => 'readonly']
      ]);

      CRUD::addField([
          'name'  => 'start_time',
          'label' => 'Start Time',
          'type'  => 'text',
          'attributes' => ['readonly' => 'readonly'],
          'wrapper'   => [
            'class'      => 'form-group col-lg-6'
          ],
      ]);

      CRUD::addField([
          'name'  => 'end_time',
          'label' => 'End Time',
          'type'  => 'text',
          'attributes' => ['readonly' => 'readonly'],
          'wrapper'   => [
            'class'      => 'form-group col-lg-6'
          ],
      ]);

      CRUD::addField([
          'name'  => 'event_name',
          'label' => 'Event Name',
          'type'  => 'text',
          'attributes' => [
            'placeholder' => 'Meeting / Activity name',
            'required' => 'required'
          ]
      ]);

      $requip = \App\Models\EquipmentType::whereNotIn('id', $seat->EquipmentTypes->pluck('id')->toArray())
        ->get()->pluck('name', 'id')->toArray();

      // CRUD::addField([
      //     'name'  => 'extra_equip',
      //     'label' => 'Request Additional Equipment',
      //     'type'  => 'select2_from_array',
      //     'options' => $requip,
      //     'allows_null' => true,
      //     'allows_multiple' => true,
      //     'value' => $cab->extra_equip->pluck('id')->toArray(),
      //     'attributes' => ['disabled' => 'disabled'],
      // ]);

      $curreqeq = [];
      foreach($cab->extra_equip as $eqp){
        // dd($eqp->pivot);
        $curreqeq[] = ['extra_equip_eq' => $eqp->id, 'extra_equip_count' => $eqp->pivot->count, 'extra_equip_status' => $eqp->pivot->status];
      }

      CRUD::addField([
        'name'            => 'extra_equip_list',
        'label'           => 'Requested Additional Equipment (not editable)',
        'type'            => 'repeatable',
        'new_item_label' => 'Modification will be ignored', // used on the "Add X" button
        'fields' => [
            [
                'name'    => 'extra_equip_eq',
                'type'    => 'select2_from_array',
                'label'   => 'Equipment Type',
                'wrapper' => ['class' => 'form-group col-md-8'],
                'options' => $requip,
                'allows_null' => false,
                'attributes' => ['disabled' => 'disabled']
            ],
            [
                'name'    => 'extra_equip_count',
                'type'    => 'number',
                'label'   => 'Count',
                'attributes' => [
                  'step' => '1',
                  'min' => '1',
                  'disabled' => 'disabled'
                ],
                'wrapper' => ['class' => 'form-group col-md-2'],

            ],
            [
                'name'    => 'extra_equip_status',
                'type'    => 'text',
                'label'   => 'Status',
                'attributes' => ['disabled' => 'disabled'],
                'wrapper' => ['class' => 'form-group col-md-2'],
            ],
        ],
        'min_rows' => 0, // maximum rows allowed in the table
        'max_rows' => 5,
        'value' => $curreqeq,
      ]);

      CRUD::addField([
          'name'  => 'user_remark',
          'label' => 'Remark for the request',
          'type'  => 'textarea'
      ]);

      if($cab->status == 'Pending SB'){
        CRUD::addField([
            'name'  => 'end_time',
            'label' => 'End Time',
            'type'  => 'text',
            'attributes' => ['readonly' => 'readonly'],
            'wrapper'   => [
              'class'      => 'form-group col-lg-6'
            ],
        ]);
      }

    }

    // public function update(){
    //
    //   $savereq = $this->crud->getRequest();
    //   $extraeq = array_filter($savereq->extra_equip);
    //   $this->crud->getRequest()->request->remove('extra_equip');
    //   $this->crud->getRequest()->request->add(['extra_equip'=> $extraeq]);
    //
    //
    //   dd($savereq);
    //
    //   $response = $this->traitUpdate();
    //     // do something after save
    //   return $response;
    // }

    public function areafinder(){
      $this->data['title'] = 'Area Booking';
      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'Area Bookings' => backpack_url('userareabooking'),
          'Area Finder' => false
      ];

      $kerud = app()->make('crud');
      $kerud->setValidation(AreaFinderRequest::class);

      $kerud->addField([   // DateTime
          'name'  => 'start_time',
          'label' => 'Start Time',
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

      $kerud->addField([   // DateTime
          'name'  => 'end_time',
          'label' => 'End Time',
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

      $blist = \App\Models\Building::all()->pluck('building_name', 'id')->toArray();

      $kerud->addField(
      [
        'name' => 'building_id',
        'label' => 'Building',
        'type' => 'select2_from_array',
        'options' => $blist,
        'wrapper'   => [
          'class'      => 'form-group col-lg-6'
        ],
      ]);

      $kerud->addField(
      [
        'name' => 'capacity',
        'label' => 'Minimum Capacity',
        'type' => 'number',
        'attributes' => ["step" => "1", "min" => "1", "max" => "300"],
        'wrapper'   => [
          'class'      => 'form-group col-lg-6'
        ],
      ]);

      $this->data['crud'] = $kerud;

      // dd($this->crud);

      return view('inventory.areafinder', $this->data);
    }

    public function areafinderresult(){
      $this->crud->setValidation(AreaFinderRequest::class);
      $this->crud->validateRequest();
      $req = $this->crud->getRequest();

      $mincaps = 1;
      $mcdisp = 'Any';
      $filteredlist = false;
      $stime = new Carbon($req->start_time);
      $stime->second = 0;
      $etime = new Carbon($req->end_time);
      $etime->second = 0;

      if($stime->gte($etime)){
        \Alert::error('Invalid date range')->flash();
        return redirect()->back();
      }

      // check if the optional params is provided
      if($req->filled('capacity')){
        $mincaps = $req->capacity;
        $mcdisp = $mincaps;
      }

      $filteredlist = false;

      if($req->filled('building_id')){
        $floorlist = \App\Models\Floor::where('status', 1)->where('building_id', $req->building_id)->get()->pluck('id')->toArray();
        $fclist = \App\Models\FloorSection::where('status', 1)->whereIn('floor_id', $floorlist)->get()->pluck('id')->toArray();
        $filter = \App\Models\Seat::where('status', 1)
          ->where('seat_type', 'Meeting Area')
          ->whereIn('floor_section_id', $fclist)
          ->where('seat_capacity', '>=', $mincaps)->get();
        $filteredlist = $filter->pluck('id')->toArray();
      }

      // list occupied area
      if($filteredlist){
        $overlap = \App\Models\AreaBooking::where('start_time', '<', $etime->toDateTimeString())
          ->where('end_time', '>', $stime->toDateTimeString())
          ->whereIn('seat_id', $filteredlist)
          ->whereIn('status', ['Active', 'Pending SB'])->get()->pluck('seat_id')->toArray();
      } else {
        $overlap = \App\Models\AreaBooking::where('start_time', '<', $etime->toDateTimeString())
          ->where('end_time', '>', $stime->toDateTimeString())
          ->whereIn('status', ['Active', 'Pending SB'])->get()->pluck('seat_id')->toArray();
      }

      // then list the available area
      $available = \App\Models\Seat::query();
      $available = $available->where('seat_type', 'Meeting Area');
      $available = $available->whereNotIn('id', $overlap);
      if($filteredlist){
        $available = $available->whereIn('id', $filteredlist);
      }

      $sd = \App\common\CommonHelper::GetCConfig('sqldebug', 'false');
      // CommonConfig::where('key','sqldebug')->first();
      //dd($sd->value);
      if($sd  ==  'true'){
      $this->data['sqldebug'] = [
        'sql' => $available->toSql(),
        'filter' => $filteredlist,
        'not_available' => $overlap
      ];
    }

      $result = $available->get();
      $reswid = [];
      foreach($result as $ar){
        $reswid[] = [
            'type'          => 'clickable',
            'class'         => 'card mb-2',
            'value'         => $ar->label,
            'description'   => $ar->floor_section->long_label,
            'progress'      => 100, // integer
            'progressClass' => 'progress-bar bg-primary',
            'hint'          => implode(', ', $ar->EquipmentTypes->pluck('name')->toArray()),
            'route'         => backpack_url('userareabooking/bookform?sid='.$ar->id.'&stime=' . $stime->toDateTimeString() . '&etime=' . $etime->toDateTimeString())
        ];

      }

      Widget::add([
          'type'    => 'div',
          'class'   => 'row',
          'content' => $reswid
      ])->to('after_content');

      $this->data['title'] = 'Meeting Area Search Result';
      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'Area Bookings' => backpack_url('userareabooking'),
          'Area Finder' => route('userareabooking.finder'),
          'Search Result' => false
      ];

      $this->data['start_time'] = $stime->toDateTimeString();
      $this->data['end_time'] = $etime->toDateTimeString();

      return view('inventory.arearesult', $this->data);
    }

    public function bookform(){
      $req = $this->crud->getRequest();
      // dd($req->all());
      if(!$req->filled('sid') || !$req->filled('stime') || !$req->filled('etime')){
        abort(403);
      }
      // fetch the given data
      $seat = \App\Models\Seat::find($req->sid);
      if(!$seat){
        abort(404);
      }

      $stime = new Carbon($req->stime);
      $etime = new Carbon($req->etime);


      $this->data['title'] = 'Area Booking';
      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'Area Bookings' => backpack_url('userareabooking'),
          'Area Finder' => false
      ];

      $kerud = app()->make('crud');

      // visible forms
      $kerud->addField([
          'name'  => 'area_txt',
          'label' => 'Meeting Area',
          'type'  => 'text',
          'value' => $seat->long_label,
          'attributes' => ['readonly' => 'readonly']
      ]);

      $kerud->addField([
          'name'  => 'start_time',
          'label' => 'Start Time',
          'type'  => 'text',
          'value' => $stime->toDateTimeString(),
          'attributes' => ['readonly' => 'readonly'],
          'wrapper'   => [
            'class'      => 'form-group col-lg-6'
          ],
      ]);

      $kerud->addField([
          'name'  => 'end_time',
          'label' => 'End Time',
          'type'  => 'text',
          'value' => $etime->toDateTimeString(),
          'attributes' => ['readonly' => 'readonly'],
          'wrapper'   => [
            'class'      => 'form-group col-lg-6'
          ],
      ]);

      $kerud->addField([
          'name'  => 'event_name',
          'label' => 'Event Name',
          'type'  => 'text',
          'attributes' => [
            'placeholder' => 'Meeting / Activity name',
            'required' => 'required'
          ]
      ]);

      $kerud->addField([
          'name'  => 'av_eq',
          'label' => 'Available Equipment',
          'type'  => 'text',
          'value' => implode(', ', $seat->EquipmentTypes->pluck('name')->toArray()),
          'attributes' => [
            'readonly' => 'readonly'
          ]
      ]);

      $requip = \App\Models\EquipmentType::whereNotIn('id', $seat->EquipmentTypes->pluck('id')->toArray())
        ->get()->pluck('name', 'id')->toArray();
      //
      // $kerud->addField([
      //     'name'  => 'extra_equip',
      //     'label' => 'Request Additional Equipment',
      //     'type'  => 'select2_from_array',
      //     'options' => $requip,
      //     'allows_null' => true,
      //     'allows_multiple' => true
      // ]);

      $kerud->addField([
        'name'            => 'extra_equip_list',
        'label'           => 'Request Additional Equipment',
        'hint'            => 'Will require SB approval',
        'type'            => 'repeatable',
        'new_item_label' => 'Add Item', // used on the "Add X" button
        'fields' => [
            [
                'name'    => 'extra_equip_eq',
                'type'    => 'select2_from_array',
                'label'   => 'Equipment Type',
                'wrapper' => ['class' => 'form-group col-md-8'],
                'options' => $requip,
                'allows_null' => false,
            ],
            [
                'name'    => 'extra_equip_count',
                'type'    => 'number',
                'label'   => 'Count',
                'attributes' => [
                  'step' => '1',
                  'min' => '1',
                ],
                'value' => 1,
                'wrapper' => ['class' => 'form-group col-md-4'],

            ],
        ],
        'min_rows' => 0, // maximum rows allowed in the table
        'max_rows' => 5,
      ]);

      $kerud->addField([
          'name'  => 'extra_remark',
          'label' => 'Remark for the request',
          'type'  => 'textarea'
      ]);

      // add the hidden fields
      $kerud->addField([
          'name'  => 'seat_id',
          'type'  => 'hidden',
          'value' => $seat->id
      ]);

      $this->data['crud'] = $kerud;
      return view('inventory.areabook', $this->data);
    }

    public function dobooking(){
      $req = $this->crud->getRequest();
      // dd($req->extra_equip_list);

      $start_time = new Carbon($req->start_time);
      $end_time = new Carbon($req->end_time);

      // check for overlap
      if(MeetingAreaHelper::CheckAvailability($req->seat_id, $start_time, $end_time)){
        $extra = [];
        if($req->filled('extra_equip_list')){
          $extra = json_decode($req->extra_equip_list);
        }

        $msg = MeetingAreaHelper::BookArea($req->seat_id, $start_time, $end_time, backpack_user()->id, $req->event_name, $extra, $req->extra_remark);
        \Alert::info($msg)->flash();
        return redirect(backpack_url('userareabooking'));
      } else {
        \Alert::error('Selected room no longer available for the given time range')->flash();
        return redirect()->back();
      }


    }

    public function getqr($id)
    {

        $obj = CRUD::getCurrentEntry();

        // dd($obj);
        if($obj){
          if($obj->status == 'Active'){
            $obj->label = $obj->event_name;
            // prepare the fields you need to show
            $url = route('inv.event.docheckin', ['qr' => $obj->qr_code] );
            $this->data['content'] = $url;
            $this->data['obj'] = $obj;
            $this->data['title'] = 'Meeting Event QR';

            // load the view
            return view("inventory.singleqr", $this->data);
          }

          abort(403);

        } else {
          abort(404);
        }

    }

    // public function cancel()
    // {
    //     $this->crud->hasAccessOrFail('cancel');
    //
    //     $data = CRUD::getCurrentEntry() ??  $this->crud->model->findOrFail($id);
    //     if($data->user_id == backpack_user()->id){
    //       $data->status = 'Cancelled';
    //       $data->save();
    //       return "Success";
    //     } else {
    //       abort(403);
    //     }
    //
    // }


}
