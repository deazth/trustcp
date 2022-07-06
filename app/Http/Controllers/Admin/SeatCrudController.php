<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SeatRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\FloorSection;
use App\Models\Seat;
use App\Models\Floor;

/**
 * Class SeatCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SeatCrudController extends CrudController
{
    use \Backpack\ReviseOperation\ReviseOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function __construct()
    {
      $this->middleware(['permission:infra-seat']);
      parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Seat::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/seat');
        CRUD::setEntityNameStrings('Seat', 'Seats');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
      CRUD::setFromDb();
      CRUD::modifyColumn('status',
      [
        'label' => 'Status',
        'type' => 'select_from_array',
        'options' => [
          '0' => 'Inactive',
          '1' => 'Active'
        ]
      ]);

      // CRUD::modifyColumn('qr_code', ['priority' => 3]);
      // CRUD::modifyColumn('allow_booking', ['priority' => 2]);

      CRUD::modifyColumn('floor_section_id', [
        'label' => 'Floor Section',
        'type' => 'relationship',
        'entity'    => 'floor_section', // the method that defines the relationship in your Model
        'attribute' => 'long_label', // foreign key attribute that is shown to user
        'model'     => \App\Models\FloorSection::class, // foreign key model
      ]);


      // CRUD::removeColumn('qr_code');
      CRUD::removeColumn('priviledge');
      CRUD::removeColumn('created_by');
      CRUD::removeColumn('updated_by');
      CRUD::removeColumn('seat_type');
      CRUD::removeColumn('building_id');
      CRUD::removeColumn('seat_utilized');
      CRUD::removeColumn('floor_id');
      CRUD::removeColumn('qr_code');
      CRUD::addClause('where', 'seat_type', '!=', 'Meeting Area');

      $req = CRUD::getRequest();
      if($req->filled('fcid')){
        CRUD::addClause('where', 'floor_section_id', '=', $req->fcid);
      }

      CRUD::addButtonFromModelFunction('line', 'getQRBtn', 'getQRBtn', 'end');
      $this->crud->addButtonFromView('top', 'bulkseatadd', 'bulkseatadd', 'end');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(SeatRequest::class);
        // CRUD::setFromDb();

        CRUD::addField([
          'name' => 'building_id',
          'label' => 'Building',
          'type' => 'select2',
          'entity'    => 'Building', // the method that defines the relationship in your Model
          'model'     => "App\Models\Building", // foreign key model
          'attribute' => 'building_name', // foreign key attribute that is shown to user
          'options'   => (function ($query) {
              return $query->orderBy('building_name', 'ASC')->get();
          }),
          'wrapper'   => [
            'class'      => 'form-group col-md-4'
          ],
        ]);

        CRUD::addField([
          'name' => 'floor_id',
          'type' => 'select2_from_ajax',
          'label' => 'Floor',
          'entity' => 'Floor',
          'attribute' => 'floor_name',
          'model'       => Floor::class,
          'data_source' => route('wa.getFloorList'),
          'allows_null' => false,
          'include_all_form_fields' => true,
          'dependencies' => ['building_id'],
          'attributes' => ['required' => true],
          'minimum_input_length' => 0,
          'placeholder' => 'Please select',
          'wrapper'   => [
            'class'      => 'form-group col-md-4'
          ],
        ]);

        CRUD::addField([
          'name' => 'floor_section_id',
          'type' => 'select2_from_ajax',
          'label' => 'Section',
          'entity' => 'floor_section',
          'attribute' => 'label',
          'model'       => FloorSection::class,
          'data_source' => route('wa.getFloorSectionList'),
          'allows_null' => false,
          'include_all_form_fields' => true,
          'dependencies' => ['building_id', 'floor_id'],
          'attributes' => ['required' => true],
          'minimum_input_length' => 0,
          'placeholder' => 'Please select',
          'wrapper'   => [
            'class'      => 'form-group col-md-4'
          ],
        ]);

        CRUD::addField([
          'name' => 'label',
          'type' => 'text',
        ]);

        CRUD::addField([
          'name' => 'remark',
          'type' => 'text',
        ]);

        CRUD::addField([
          'name' => 'status',
          'label' => 'Status',
          'type' => 'radio',
          'options' => [
            '0' => 'Inactive',
            '1' => 'Active'
          ],
          'inline' => true,
          'default' => '1'
        ]);

        // CRUD::removeField('seat_utilized');
        // CRUD::removeField('created_by');
        // CRUD::removeField('updated_by');
        // CRUD::removeField('priviledge');
        // CRUD::removeField('qr_code');

        // CRUD::modifyField('seat_type',
        // [
        //   'label' => 'Seat Type',
        //   'type' => 'select_from_array',
        //   'options' => [
        //     'Seat' => 'Seat',
        //     'VIP' => 'VIP',
        //   ],
        //   'allows_null' => false,
        //   'default'     => 'Seat',
        // ]);

        CRUD::addField(
        [
          'name' => 'seat_type',
          'type' => 'hidden',
          'value'     => 'Seat',
        ]);

        CRUD::addField(
        [
          'name' => 'seat_capacity',
          'type' => 'hidden',
          'value'     => '1',
        ]);

        CRUD::addField(
        [
          'name' => 'free_seat',
          'wrapper'   => [
            'class'      => 'form-group col-md-6'
          ],
        ]);

        CRUD::addField(
        [
          'name' => 'allow_booking',
          'label' => 'Require Booking',
          'wrapper'   => [
            'class'      => 'form-group col-md-6'
          ],
        ]);



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
        $this->setupCreateOperation();
        CRUD::addField([
          'name' => 'temp',
          'type' => 'text',
          'label' => 'QR Code',
          'value'=> CRUD::getCurrentEntry()->qr_code,
          'attributes' => ['readonly' => 'readonly']
        ]);
    }

    public function getqr($id)
    {
        // $obj = \App\Models\Seat::find($id);
        $obj = CRUD::getCurrentEntry();
        if($obj){
          // prepare the fields you need to show
          $this->data['obj'] = $obj;
          $url = route('inv.seat.docheckin', ['qr' => $obj->qr_code] );
          $this->data['content'] = $url;
          $this->data['title'] = 'Inventory QR';

          // load the view
          return view("inventory.singleqr", $this->data);
        } else {
          abort(404);
        }

    }

    public function store()
    {
      $response = $this->traitStore();

      // increment the fc counter
      $this->crud->entry->floor_section->increment('tracked_seat_count');
      return $response;
    }

    public function bulkaddform(){
      $this->data['title'] = 'Area Booking';
      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'Seat' => backpack_url('seat'),
          'Bulk Add' => false
      ];

      $kerud = app()->make('crud');
      // $kerud->setValidation(AreaFinderRequest::class);

      $kerud->addField([
        'name' => 'building_id',
        'label' => 'Building',
        'type' => 'select2',
        'entity'    => 'Building', // the method that defines the relationship in your Model
        'model'     => "App\Models\Building", // foreign key model
        'attribute' => 'building_name', // foreign key attribute that is shown to user
        'options'   => (function ($query) {
            return $query->orderBy('building_name', 'ASC')->get();
        }),
        'wrapper'   => [
          'class'      => 'form-group col-md-4'
        ],
      ]);

      $kerud->addField([
        'name' => 'floor_id',
        'type' => 'select2_from_ajax',
        'label' => 'Floor',
        'entity' => 'Floor',
        'attribute' => 'floor_name',
        'model'       => Floor::class,
        'data_source' => route('wa.getFloorList'),
        'allows_null' => false,
        'include_all_form_fields' => true,
        'dependencies' => ['building_id'],
        'minimum_input_length' => 0,
        'placeholder' => 'Please select',
        'wrapper'   => [
          'class'      => 'form-group col-md-4'
        ],
      ]);

      $kerud->addField([
        'name' => 'floor_section_id',
        'type' => 'select2_from_ajax',
        'label' => 'Section',
        'entity' => 'floor_section',
        'attribute' => 'label',
        'model'       => FloorSection::class,
        'data_source' => route('wa.getFloorSectionList'),
        'allows_null' => false,
        'include_all_form_fields' => true,
        'dependencies' => ['building_id', 'floor_id'],
        'minimum_input_length' => 0,
        'placeholder' => 'Please select',
        'wrapper'   => [
          'class'      => 'form-group col-md-4'
        ],
      ]);

      // $kerud->addField([
      //   'name' => 'floor_section_id',
      //   'label' => 'Floor Section',
      //   'type' => 'select2',
      //   'entity'    => 'floor_section', // the method that defines the relationship in your Model
      //   'model'     => "App\Models\FloorSection", // foreign key model
      //   'attribute' => 'long_label', // foreign key attribute that is shown to user
      //   'options'   => (function ($query) {
      //       return $query->orderBy('label', 'ASC')->where('status', 1)->get();
      //   }),
      //   'wrapper'   => [
      //     'class'      => 'form-group col-md-6'
      //   ],
      // ]);

      $kerud->addField([
        'name' => 'add_count',
        'label' => 'Number of seats to add',
        'type' => 'number',
        'value' => 1,
        'attributes' => ["step" => "1", "min" => "1", "max" => "500", "required" => true],
        'wrapper'   => [
          'class'      => 'form-group col-md-4'
        ],
      ]);

      $kerud->addField([
        'name' => 'free_seat',
        'label' => 'Free seats',
        'type' => 'boolean',
        'wrapper'   => [
          'class'      => 'form-group col-md-4'
        ],
      ]);

      $kerud->addField([
        'name' => 'allow_booking',
        'label' => 'Allow booking',
        'type' => 'boolean',
        'wrapper'   => [
          'class'      => 'form-group col-md-4'
        ],
      ]);

      $this->data['crud'] = $kerud;

      // dd($this->crud);

      return view('inventory.bulkseatadd', $this->data);
    }

    public function doBulkAdd(){
      $req = $this->crud->getRequest();
      // dd($req->all());

      $fc = FloorSection::where('id', $req->floor_section_id)->where('status', 1)->first();
      // $fc = FloorSection::find(44);

      if($fc){
        $curc = $fc->tracked_seat_count + 1;
        $endlimit = $curc + $req->add_count;
        $data = [];
        for($curc; $curc < $endlimit; $curc++ ){
          // create seats
          $seat = new Seat;
          $seat->floor_section_id = $fc->id;
          $seat->building_id = $req->building_id;
          $seat->floor_id = $req->floor_id;
          $seat->label = $fc->label . ' ' . $curc;
          $seat->seat_type = 'Seat';
          $seat->remark = 'Bulk add';
          $seat->status = 1;
          $seat->seat_capacity = 1;
          $seat->seat_utilized = 0;
          $seat->free_seat = $req->free_seat;
          $seat->allow_booking = $req->free_seat;
          $seat->save();
          // $data[] = $fc->label . ' ' . $curc;
          $fc->increment('tracked_seat_count');
        }

        // dd($data);

        \Alert::info($req->add_count . ' seats added')->flash();
        return redirect(backpack_url('seat'));
      } else {
        \Alert::error('Selected floor section no longer exist or active')->flash();
        return redirect()->back()->withInput();
      }
    }
}
