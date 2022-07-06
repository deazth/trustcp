<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\FloorRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class FloorCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class FloorCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function __construct()
    {
      $this->middleware(['permission:infra-floor']);
      parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Floor::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/floor');
        CRUD::setEntityNameStrings('New Office Floor', 'Work Place Management');
        // dd(session()->all());
        // $data = session()->all();

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
        // $table->bigIncrements('id');
        // $table->string('building_name', 50);
        // $table->string('floor_name', 50);
        // $table->string('remark', 255)->nullable();
        // $table->integer('status')->nullable();
        // $table->integer('created_by')->nullable();
        // $table->integer('seat_count')->nullable()->default(0);
        // $table->string('unit')->nullable();
        // $table->integer('building_id')->nullable();

        CRUD::addColumn([
          'name'  => 'building_id',// the db column for the foreign key
          'label' => 'Building Name',
          'type'  => 'relationship',
          'entity'  => 'Buildings', // the method that defines the relationship in your Model
          'attribute' => 'building_name',// foreign key attribute that is shown to user
          'model'     => App\Models\Building::class,
          // 'pivot'     => true,
        ]);
        CRUD::addColumn([
        'name'  => 'floor_name',
        'label' => 'Floor Name',
        'type'  => 'text']);
        CRUD::addColumn([
          'name'  => 'unit',
          'label' => 'Unit',
          'type'  => 'text']);
        CRUD::addColumn([
        'name'  => 'remark',
        'label' => 'Remark',
        'type'  => 'text']);
        CRUD::addColumn([
        'name'  => 'status',
        'label' => 'Status',
        'type' => 'select_from_array',
        'options' => [
          '0' => 'Inactive',
          '1' => 'Active'
        ]]);

        // CRUD::addColumn([
        //   'name'  => 'layout_file',
        //   'label' => 'Layout',
        //   'type'  => 'text'
        // ]);

        CRUD::addButtonFromModelFunction('line', 'getAllSections', 'getAllSections', 'start');
        CRUD::addButtonFromModelFunction('line', 'viewLayoutBtn', 'viewLayoutBtn', 'end');


       //  CRUD::addColumn(['name'  => 'building_id',
       //   'label' => 'Building id',
       //   'type'  => 'number'
       // ]);

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
        CRUD::setValidation(FloorRequest::class);

        // CRUD::setFromDb(); // fields

        CRUD::addField([
        'label' => 'Building Name',
        'type'  => 'select',
        'name'  => 'building_id',// the db column for the foreign key
        'entity'  => 'Buildings', // the method that defines the relationship in your Model
        'attribute' => 'building_name',// foreign key attribute that is shown to user
        'model'     => 'App\Models\Building',
        // 'pivot'     => true,
      ]);
        CRUD::addField([
        'name'  => 'floor_name',
        'label' => 'Floor Name',
        'type'  => 'text']);
        CRUD::addField([
          'name'  => 'unit',
          'label' => 'Unit',
          'type'  => 'text']);
        CRUD::addField([
        'name'  => 'remark',
        'label' => 'Remark',
        'type'  => 'text']);
        CRUD::addField([
        'name'  => 'building_name',
        'value'     => '-',
        'type'  => 'hidden']);

        CRUD::addField([
        'name'        => 'status',
        'label'       => 'Status',
        'type'        => 'radio',
        'options'     => [
        1 => 'Active',
        0 => 'Inactive' ],
        'inline' => true,
        'default' => '1'
        ]);

        CRUD::addField([
          'name'  => 'layout_file',
          'label' => 'Layout',
          'type'      => 'upload',
          'upload'    => true,
          'disk'      => 'local',
        ]);

        // CRUD::field('created_at');
        // CRUD::field('updated_at');
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
    }
}
