<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\FloorSectionRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class FloorSectionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class FloorSectionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function __construct()
    {
      $this->middleware(['permission:infra-section']);
      parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\FloorSection::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/floorsection');
        CRUD::setEntityNameStrings('Floor Section', 'Floor Section');
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

        $req = CRUD::getRequest();
        if($req->filled('fid')){
          CRUD::addClause('where', 'floor_id', '=', $req->fid);
        }

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */

         CRUD::modifyColumn('status',
         [
           'label' => 'Status',
           'type' => 'select_from_array',
           'options' => [
             '0' => 'Inactive',
             '1' => 'Active'
           ]
         ]);
         CRUD::modifyColumn('floor_id', [
           'label' => 'Floor',
           'type' => 'relationship',
           'entity'    => 'Floor', // the method that defines the relationship in your Model
           'attribute' => 'long_label', // foreign key attribute that is shown to user
           'model'     => App\Models\Floor::class, // foreign key model
         ]);
         CRUD::removeColumn('tracked_seat_occupied');
         CRUD::removeColumn('tracked_seat_count');
         CRUD::removeColumn('layout_file');

         CRUD::addButtonFromModelFunction('line', 'getAllQRBtn', 'getAllQRBtn', 'start');
         CRUD::addButtonFromModelFunction('line', 'getAllSeats', 'getAllSeats', 'start');
         CRUD::addButtonFromModelFunction('line', 'viewLayoutBtn', 'viewLayoutBtn', 'end');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(FloorSectionRequest::class);

        CRUD::setFromDb(); // fields

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */

         CRUD::modifyField('status',
         [
           'label' => 'Status',
           'type' => 'radio',
           'options' => [
             '0' => 'Inactive',
             '1' => 'Active'
           ],
           'inline' => true,
           'default' => '1'
         ]);

         CRUD::modifyField('floor_id', [
           'label' => 'Floor',
           'type' => 'select2',
           'entity'    => 'Floor', // the method that defines the relationship in your Model
           'model'     => "App\Models\Floor", // foreign key model
           'attribute' => 'long_label', // foreign key attribute that is shown to user
           'options'   => (function ($query) {
               return $query->orderBy('building_id', 'ASC')->where('status', 1)->get();
           }),
         ]);

         CRUD::modifyField('layout_file', [
           'label' => 'Layout',
           'type'      => 'upload',
           'upload'    => true,
           'disk'      => 'local',
         ]);

         CRUD::removeField('tracked_seat_occupied');
         CRUD::removeField('tracked_seat_count');
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

    public function getAllQr(){
      set_time_limit(300);
      $req = CRUD::getRequest();
      // dd($req->all());

      $cab = CRUD::getCurrentEntry();


      $width = 300;

      if($req->filled('width')){
          $width = $req->width;
      }

      $colcount = 3;
      if($req->filled('colcount')){
          $colcount = $req->colcount;
      }

      return view('inventory.genallqr', [
        'build_id' => $cab->id,
        'seats' => $cab->seats,
        'fc_label' => $cab->long_label,
        'width' => $width,
        'colcount' => $colcount,
        'inimg' => $req->filled('inimg'),
      ]);
    }
}
