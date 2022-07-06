<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AreaBookingRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class AreaBookingCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AreaBookingCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function __construct()
    {
      $this->middleware(['permission:infra-area-booking']);
      parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\AreaBooking::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/areabooking');
        CRUD::setEntityNameStrings('Area Booking', 'Area Bookings');
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
        CRUD::setHeading('Admin - Area Booking Management');
        // CRUD::addClause('where', 'status', '=', 'Pending SB');

        CRUD::addFilter([
          'type' => 'simple',
          'name' => 'pending',
          'label' => 'Pending SB only'
        ], false, function(){
          CRUD::addClause('where', 'status', '=', 'Pending SB');
        });

        CRUD::addFilter([
          'type' => 'simple',
          'name' => 'extrareq',
          'label' => 'Has Extra Req'
        ], false, function(){
          CRUD::addClause('where', 'has_extra_req', '=', 1);
        });

        CRUD::addFilter([
          'type' => 'simple',
          'name' => 'start_today',
          'label' => 'Start Today'
        ], false, function(){
          CRUD::addClause('whereDate', 'start_time', '=', date('Y-m-d'));
        });

        $this->crud->addFilter([
          'type'  => 'date_range',
          'name'  => 'act_range',
          'label' => 'Start Date Range'
        ],
            false,
            function ($value) { // if the filter is active, apply these constraints
                $dates = json_decode($value);
                $this->crud->addClause('where', 'start_time', '>=', $dates->from);
                $this->crud->addClause('where', 'start_time', '<=', $dates->to);
            }
        );

        CRUD::addFilter([
          'type' => 'simple',
          'name' => 'hideend',
          'label' => 'Hide ended'
        ], false, function(){
          $skrg = new \Carbon\Carbon;

          CRUD::addClause('where', 'end_time', '>', $skrg->toDateTimeString());
        });

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
          'name' => 'user_id',
          'label' => 'Organizer',
          'type' => 'relationship',
          'entity'    => 'organizer', // the method that defines the relationship in your Model
          'attribute' => 'name', // foreign key attribute that is shown to user
          'model'     => App\Models\User::class, // foreign key model
          "relation_type" => "BelongsTo",
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

        CRUD::setHeading('Manual book meeting area');
        CRUD::setSubheading('Overlap will hijack existing reservations');

        CRUD::modifyField('seat_id', [
          'label' => 'Meeting Area',
          'type' => 'select2',
          'entity'    => 'Meetingarea',
          'attribute' => 'long_label',
          'model'     => \App\Models\Seat::class,
          'attributes' => ['required' => 'required'],
          "relation_type" => "BelongsTo",
          'options' => (function($query){
            return $query->where('seat_type', 'Meeting Area')->get();
          })
        ]);

        CRUD::modifyField('user_id', [
         'label'       => 'Organizer',
         'type'        => 'select2_from_ajax',
         'entity'      => 'organizer', // the method that defines the relationship in your Model
         'model'       => "App\Models\User", // foreign key model
         'attribute'   => "name", // foreign key attribute that is shown to user
         'data_source' => route('wa.finduser'), // url to controller search function (with /{id} should return model)
         'attributes' => ['required' => 'required'],
         // OPTIONAL
         'delay'                 => 500, // the minimum amount of time between ajax requests when searching in the field
         'placeholder'           => "Staff No. or Name", // placeholder for the select
         'minimum_input_length'  => 4, // minimum characters to type before querying results
         // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)

        ]);

        CRUD::modifyField('start_time', [
          'attributes' => ['required' => 'required'],
          'type'  => 'datetime_picker',

          // optional:
          'datetime_picker_options' => [
              'format' => 'DD/MM/YYYY HH:mm',
              'language' => 'en'
          ],
          'wrapper'   => [
            'class'      => 'form-group col-lg-6'
          ]
        ]);

        CRUD::modifyField('end_time', [
          'attributes' => ['required' => 'required'],
          'type'  => 'datetime_picker',

          // optional:
          'datetime_picker_options' => [
              'format' => 'DD/MM/YYYY HH:mm',
              'language' => 'en'
          ],
          'wrapper'   => [
            'class'      => 'form-group col-lg-6'
          ]
        ]);

        CRUD::modifyField('event_name', [
          'attributes' => ['required' => 'required'],
        ]);

        CRUD::modifyField('admin_remark', [
          'attributes' => ['readonly' => 'readonly'],
          'value' => 'Booked by admin'
        ]);

        CRUD::modifyField('admin_id', [
          'type' => 'hidden',
          'value' => backpack_user()->id
        ]);

        CRUD::modifyField('qr_code', [
          'type' => 'hidden',
          'value' => \Illuminate\Support\Str::orderedUuid()->toString()
        ]);

        CRUD::modifyField('status', [
          'type' => 'hidden',
          'value' => 'Active'
        ]);

        CRUD::removeField('is_full_day');
        CRUD::removeField('has_extra_req');
        CRUD::removeField('is_long_term');

        // hiddens

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
        // $this->setupCreateOperation();

      CRUD::setHeading('Admin - Area Booking Management');

      $cab = CRUD::getCurrentEntry();
      // dd($cab->extra_equip->pluck('id')->toArray());
      $seat = $cab->Meetingarea;
      if($seat){

      } else {
        // meeting area deleted. so just delete this request
        $cab->delete();
        \Alert::error('Related meeting area has been deleted. The booking is now deleted as well')->flash();
        abort(404);
      }
      $requip = \App\Models\EquipmentType::whereNotIn('id', $seat->EquipmentTypes->pluck('id')->toArray())
        ->get()->pluck('name', 'id')->toArray();

      CRUD::addField([
          'name'  => 'area_txt',
          'label' => 'Meeting Area',
          'type'  => 'text',
          'value' => $seat->long_label,
          'attributes' => ['readonly' => 'readonly']
      ]);

      CRUD::addField([
          'name'  => 'org_user',
          'label' => 'Organizer',
          'type'  => 'text',
          'value' => $cab->organizer->id_name,
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

      CRUD::addField([
          'name'  => 'user_remark',
          'label' => 'User Remark',
          'type'  => 'textarea',
          'attributes' => ['readonly' => 'readonly'],
      ]);

      CRUD::addField(
      [
        'name' => 'is_long_term',
        'attributes' => ['disabled' => 'disabled'],
        'wrapper'   => [
          'class'      => 'form-group col-lg-6'
        ],
      ]);

      CRUD::addField(
      [
        'name' => 'has_extra_req',
        'attributes' => ['disabled' => 'disabled'],
        'wrapper'   => [
          'class'      => 'form-group col-lg-6'
        ],
      ]);


      // CRUD::addField([
      //     'name'  => 'extra_equip',
      //     'label' => 'Requested Additional Equipment',
      //     'type'  => 'select2_from_array',
      //     'options' => $requip,
      //     'allows_null' => true,
      //     'allows_multiple' => true,
      //     'value' => $cab->extra_equip->pluck('id')->toArray(),
      //     'attributes' => ['disabled' => 'disabled'],
      // ]);

      $curreqeq = [];
      $cab = CRUD::getCurrentEntry();
      $seat = $cab->Meetingarea;
      $requip = \App\Models\EquipmentType::whereNotIn('id', $seat->EquipmentTypes->pluck('id')->toArray())
        ->get()->pluck('name', 'id')->toArray();
      foreach($cab->extra_equip as $eqp){
        // dd($eqp->pivot);
        $curreqeq[] = ['extra_equip_eq' => $eqp->id, 'extra_equip_count' => $eqp->pivot->count, 'extra_equip_status' => $eqp->pivot->status];
      }

      CRUD::addField([
        'name'            => 'extra_equip_list',
        'label'           => 'Requested Additional Equipment',
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
                'wrapper' => ['class' => 'form-group col-md-2'],

            ],
            [
                'name'    => 'extra_equip_status',
                'type'    => 'select2_from_array',
                'label'   => 'Status',
                'options' => [
                  'New' => 'New',
                  'Approved' => 'Approved',
                  'Rejected' => 'Rejected',
                ],
                'wrapper' => ['class' => 'form-group col-md-2'],
            ],
        ],
        'min_rows' => 0, // maximum rows allowed in the table
        'max_rows' => 5,
        'value' => $curreqeq,
      ]);


      CRUD::addField(
      [
        'name' => 'status',
        'label' => 'Status',
        'type' => 'select_from_array',
        'options' => [
          'Active' => 'Active',
          'Cancelled' => 'Cancelled',
          'Pending SB' => 'Pending SB',
          'Rejected' => 'Rejected'
        ]
      ]);

      CRUD::addField([
          'name'  => 'admin_remark',
          'label' => 'Admin Remark',
          'type'  => 'textarea'
      ]);

      CRUD::addField([
          'name'  => 'last_admin',
          'label' => 'Last Admin',
          'type'  => 'text',
          'value' => $cab->last_admin ? $cab->last_admin->id_name : '',
          'attributes' => ['readonly' => 'readonly']
      ]);

      CRUD::addField(
      [
        'name' => 'admin_id',
        'type' => 'hidden',
        'value' => backpack_user()->id
      ]);

      CRUD::addField([
        'name' => 'temp',
        'type' => 'text',
        'label' => 'QR Code',
        'value'=> $cab->qr_code,
        'attributes' => ['readonly' => 'readonly']
      ]);


    }

    public function store() {
      $response = $this->traitStore();

      // check for hijacked bookings
      \App\common\MeetingAreaHelper::SendHijackAlert(CRUD::getCurrentEntry());

      return $response;
    }

    public function update(){
      $obj = CRUD::getCurrentEntry();
      $req = $this->crud->getRequest();

      $exreq = [];
      if($req->filled('extra_equip_list')){
        $extra = json_decode($req->extra_equip_list);
        foreach($extra as $ext){
          $exreq[$ext->extra_equip_eq] = ['count' => $ext->extra_equip_count, 'status' => $ext->extra_equip_status];
        }
      }

      if(sizeof($exreq) > 0){
        $obj->extra_equip()->sync($exreq);
        $this->crud->getRequest()->request->add(['has_extra_req'=> 1]);
      } else {
        $obj->extra_equip()->detach();
        $this->crud->getRequest()->request->add(['has_extra_req'=> 0]);
      }

      $response = $this->traitUpdate();

      return $response;
    }
}
