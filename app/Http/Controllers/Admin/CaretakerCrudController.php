<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CompGroupRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CompGroupCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CaretakerCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\CompGroup::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/caretaker');
        CRUD::setEntityNameStrings('Caretaker', 'Caretaker');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {

      // dd(backpack_user()->CompGroups);
        CRUD::setFromDb(); // columns

        if(!backpack_user()->hasPermissionTo('diary-admin')){
          // get allowed group
          CRUD::addClause('whereIn', 'id', backpack_user()->CompGroups->pluck('id')->toArray());
        }



        CRUD::removeColumn('created_by');
        CRUD::removeColumn('deleted_by');

        // CRUD::addColumn(['name' => 'Units',
        //   'label' => 'Units',
        //   'type' => 'relationship',
        //   'entity'    => 'Units',
        //   'attribute' => 'pporgunitdesc',
        //   'model'     => App\Models\Unit::class,
        //   "relation_type" => "hasMany"
        // ]);

        CRUD::addButtonFromModelFunction('line', 'showActTypeList', 'showActTypeList', 'end');
        CRUD::addButtonFromModelFunction('line', 'showUserList', 'showUserList', 'end');
        CRUD::addButtonFromModelFunction('line', 'showBatchRpt', 'showBatchRpt', 'end');


    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(CompGroupRequest::class);

        CRUD::setFromDb(); // fields
        CRUD::removeField('created_by');
        CRUD::removeField('deleted_by');
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

    protected function setupShowOperation()
    {
      $this->crud->set('show.setFromDb', false);
      CRUD::setFromDb(); // columns

      // get allowed group
      // CRUD::addClause('whereIn', 'id', backpack_user()->CompGroups->pluck('id')->toArray());

      CRUD::removeColumn('created_by');
      CRUD::removeColumn('deleted_by');

      CRUD::addColumn(['name' => 'Units',
        'label' => 'Units',
        'type' => 'relationship',
        'entity'    => 'Units',
        'attribute' => 'pporgunitdesc',
        'model'     => App\Models\Unit::class,
        "relation_type" => "hasMany"
      ]);

      CRUD::addColumn(['name' => 'Caretakers',
        'label' => 'Caretakers',
        'type' => 'relationship',
        'entity'    => 'Caretakers',
        'attribute' => 'id_name',
        'model'     => App\Models\User::class,
        "relation_type" => "belongsToMany"
      ]);

      CRUD::addButtonFromModelFunction('line', 'showActTypeList', 'showActTypeList', 'end');
      CRUD::addButtonFromModelFunction('line', 'showUserList', 'showUserList', 'end');
    }
}
