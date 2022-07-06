<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ActivityTypeRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ActivityTypeCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ActivityTypeCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function __construct()
    {
      $this->middleware(['permission:diary-types']);
      parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\ActivityType::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/activitytype');
        CRUD::setEntityNameStrings('Activity Types', 'Activity Types');
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

         CRUD::modifyColumn('status',
         [
           'label' => 'Status',
           'type' => 'select_from_array',
           'options' => [
             '0' => 'Inactive',
             '1' => 'Active'
           ]
         ]);
         CRUD::addColumn([
           'name' => 'TaskCategories',
           'label' => 'Activity Tags',
           'type' => 'relationship',
           'entity'    => 'TaskCategories', // the method that defines the relationship in your Model
           'attribute' => 'descr', // foreign key attribute that is shown to user
           'model'     => App\Models\TaskCategory::class, // foreign key model
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
        CRUD::setValidation(ActivityTypeRequest::class);

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
         ]);

         CRUD::addField([
           'name' => 'TaskCategories',
           'label' => 'Activity Tags',
           'type' => 'select2_multiple',
           'entity'    => 'TaskCategories', // the method that defines the relationship in your Model
           'model'     => "App\Models\TaskCategory", // foreign key model
           'attribute' => 'descr', // foreign key attribute that is shown to user
           'pivot'     => true,
           'options'   => (function ($query) {
               return $query->orderBy('descr', 'ASC')->where('status', 1)->get();
           }),
         ]);
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
