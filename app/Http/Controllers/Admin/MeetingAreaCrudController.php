<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SeatRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\FloorSection;
use App\Models\Seat;
use App\Models\Floor;

/**
 * Class MeetingAreaCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MeetingAreaCrudController extends CrudController
{
    use \Backpack\ReviseOperation\ReviseOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function __construct()
    {
      $this->middleware(['permission:infra-meeting-area']);
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
        CRUD::setRoute(config('backpack.base.route_prefix') . '/meetingarea');
        CRUD::setEntityNameStrings('Meeting Area', 'Meeting Areas');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::setFromDb(); // columns
        CRUD::modifyColumn('status',
        [
          'label' => 'Status',
          'type' => 'select_from_array',
          'options' => [
            '0' => 'Inactive',
            '1' => 'Active'
          ]
        ]);

        CRUD::modifyColumn('floor_section_id', [
          'label' => 'Floor Section',
          'type' => 'relationship',
          'entity'    => 'floor_section', // the method that defines the relationship in your Model
          'attribute' => 'long_label', // foreign key attribute that is shown to user
          'model'     => App\Models\FloorSection::class, // foreign key model
        ]);

        CRUD::addColumn([
          'name' => 'EquipmentTypes',
          'label' => 'Equipments',
          'type' => 'relationship',
          'entity'    => 'EquipmentTypes', // the method that defines the relationship in your Model
          'attribute' => 'name', // foreign key attribute that is shown to user
          'model'     => App\Models\EquipmentType::class, // foreign key model
        ]);


        // CRUD::removeColumn('qr_code');
        CRUD::removeColumn('priviledge');
        CRUD::removeColumn('created_by');
        CRUD::removeColumn('updated_by');
        CRUD::removeColumn('seat_type');
        CRUD::removeColumn('free_seat');
        CRUD::addClause('where', 'seat_type', '=', 'Meeting Area');

        CRUD::addButtonFromModelFunction('line', 'getQRBtn', 'getQRBtn', 'end');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
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
          'minimum_input_length' => 0,
          'placeholder' => 'Please select',
          'attributes' => ['required' => true],
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

        // CRUD::setFromDb(); // fields


        CRUD::addField(
        [
          'name' => 'seat_capacity',
          'label' => 'Area Capacity',
          'type' => 'number',
          'attributes' => ['required' => true],
          'wrapper'   => [
            'class'      => 'form-group col-md-6'
          ],
        ]);

        CRUD::addField(
        [
          'name' => 'status',
          'label' => 'Status',
          'type' => 'radio',
          'options' => [
            '0' => 'Inactive',
            '1' => 'Active'
          ],
          'inline' => true,
          'default' => '1',
          'wrapper'   => [
            'class'      => 'form-group col-md-6'
          ],
        ]);

        CRUD::addField([
          'name' => 'seat_type',
          'type' => 'hidden',
          'value' => 'Meeting Area'
        ]);

        CRUD::addField([
          'name' => 'EquipmentTypes',
          'label' => 'Equipments',
          'type' => 'select2_multiple',
          'entity'    => 'EquipmentTypes', // the method that defines the relationship in your Model
          'model'     => "App\Models\EquipmentType", // foreign key model
          'attribute' => 'name', // foreign key attribute that is shown to user
          'pivot'     => true,
          'options'   => (function ($query) {
              return $query->orderBy('name', 'ASC')->get();
          }),
        ]);

        // CRUD::addField(
        // [
        //   'name' => 'allow_booking',
        //   'wrapper'   => [
        //     'class'      => 'form-group col-md-6'
        //   ],
        // ]);

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
        $obj = \App\Models\Seat::find($id);
        if($obj){
          // prepare the fields you need to show
          $this->data['obj'] = $obj;
          $this->data['title'] = 'Inventory QR';

          // load the view
          return view("inventory.singleqr", $this->data);
        } else {
          abort(404);
        }

    }
}
