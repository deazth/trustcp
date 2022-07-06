<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UnitRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class UnitCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UnitCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function __construct()
    {
      $this->middleware(['permission:menu-sys-config']);
      parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Unit::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/unit');
        CRUD::setEntityNameStrings('unit', 'units');
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
        CRUD::removeColumn('lob');
        CRUD::removeColumn('friday_hours');
        CRUD::modifyColumn('comp_group_id', [
          'label' => 'Division Group',
          'type' => 'relationship',
          'entity'    => 'comp_group', // the method that defines the relationship in your Model
          'attribute' => 'name', // foreign key attribute that is shown to user
          'model'     => \App\Models\CompGroup::class, // foreign key model
          'relation_type' => 'belongsTo'
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
        CRUD::setValidation(UnitRequest::class);

        CRUD::setFromDb(); // fields
        CRUD::modifyField('lob', ['type' => 'hidden', 'value' => 3000]);
        CRUD::modifyField('friday_hours', ['type' => 'hidden', 'value' => 7.5]);
        CRUD::modifyField('allowed', ['type' => 'checkbox', 'default' => true]);

        CRUD::modifyField('comp_group_id', [
          'label' => 'Division Group',
          'type' => 'relationship',
          'entity'    => 'comp_group', // the method that defines the relationship in your Model
          'attribute' => 'name', // foreign key attribute that is shown to user
          'model'     => \App\Models\CompGroup::class, // foreign key model
          'relation_type' => 'belongsTo'
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
