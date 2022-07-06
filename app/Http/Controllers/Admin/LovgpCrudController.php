<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LovgpRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class LovgpCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class LovgpCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function __construct()
    {
      $this->middleware(['permission:diary-group-lov']);
      parent::__construct();
    }
    
    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Lovgp::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/lovgp');
        CRUD::setEntityNameStrings('Group LOV', 'Group LOVs');
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
        CRUD::addColumn(['name' => 'name', 'type' => 'text']);

        CRUD::addColumn([
          'name' => 'groups',
          'label' => 'Used By',
          'type' => 'relationship',
          'entity'    => 'groups', // the method that defines the relationship in your Model
          'attribute' => 'name', // foreign key attribute that is shown to user
          'model'     => App\Models\CompGroup::class, // foreign key model
        ]);

        CRUD::addColumn([
          'name' => 'taskcats',
          'label' => 'Activity Tags',
          'type' => 'relationship',
          'entity'    => 'taskcats', // the method that defines the relationship in your Model
          'attribute' => 'descr', // foreign key attribute that is shown to user
          'model'     => App\Models\TaskCategory::class, // foreign key model
        ]);

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
        CRUD::setValidation(LovgpRequest::class);

        // CRUD::setFromDb(); // fields

        CRUD::addField([
          'name' => 'name',
          'label' => 'Name',
          'type' => 'text'
        ]);

        CRUD::addField([
          'name' => 'taskcats',
          'label' => 'Activity Tags',
          'type' => 'select2_multiple',
          'entity'    => 'taskcats', // the method that defines the relationship in your Model
          'model'     => "App\Models\TaskCategory", // foreign key model
          'attribute' => 'descr', // foreign key attribute that is shown to user
          'pivot'     => true,
          'options'   => (function ($query) {
              return $query->orderBy('descr', 'ASC')->where('status', 1)->get();
          }),
        ]);

        CRUD::addField([
          'name' => 'groups',
          'label' => 'Div Groups',
          'type' => 'select2_multiple',
          'entity'    => 'groups', // the method that defines the relationship in your Model
          'model'     => "App\Models\CompGroup", // foreign key model
          'attribute' => 'name', // foreign key attribute that is shown to user
          'pivot'     => true,
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
