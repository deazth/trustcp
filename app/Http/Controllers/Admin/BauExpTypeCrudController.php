<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BauExpTypeRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class BauExpTypeCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BauExpTypeCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function __construct()
    {
      $this->middleware(['permission:skill-admin']);
      parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\BauExpType::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/bau-exp-type');
        CRUD::setEntityNameStrings('bau exp type', 'bau exp types');
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
        CRUD::setValidation(BauExpTypeRequest::class);

        CRUD::setFromDb(); // fields

        CRUD::addField([
          'name' => 'BauExperiences',
          'label' => 'Applications / Module',
          'type' => 'select2_multiple',
          'entity'    => 'BauExperiences', // the method that defines the relationship in your Model
          'model'     => "App\Models\BauExperience", // foreign key model
          'attribute' => 'name', // foreign key attribute that is shown to user
          'pivot'     => true,
          'options'   => (function ($query) {
              return $query->orderBy('name', 'ASC')->get();
          }),
        ]);

        CRUD::addField([
          'name' => 'Jobscopes',
          'label' => 'Roles / Jobscopes',
          'type' => 'select2_multiple',
          'entity'    => 'Jobscopes', // the method that defines the relationship in your Model
          'model'     => "App\Models\Jobscope", // foreign key model
          'attribute' => 'name', // foreign key attribute that is shown to user
          'pivot'     => true,
          'options'   => (function ($query) {
              return $query->orderBy('name', 'ASC')->get();
          }),
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
    }
}
