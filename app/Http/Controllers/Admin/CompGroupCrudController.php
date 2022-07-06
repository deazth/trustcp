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
class CompGroupCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

    public function __construct()
    {
      $this->middleware(['permission:diary-div-group']);
      parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\CompGroup::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/compgroup');
        CRUD::setEntityNameStrings('Div Grouping', 'Division Grouping');
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

         CRUD::removeColumn('created_by');
         CRUD::removeColumn('deleted_by');
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

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */

         CRUD::addField([
          'name'        => 'Caretakers',
          'label'       => 'Caretaker',
          'type'        => 'select2_from_ajax_multiple',
          'entity'      => 'Caretakers', // the method that defines the relationship in your Model
          'model'       => "App\Models\User", // foreign key model
          'attribute'   => "name", // foreign key attribute that is shown to user
          'data_source' => route('wa.finduser'), // url to controller search function (with /{id} should return model)
          'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?

          // OPTIONAL
          'delay'                 => 500, // the minimum amount of time between ajax requests when searching in the field
          'placeholder'           => "Staff No. or Name", // placeholder for the select
          'minimum_input_length'  => 4, // minimum characters to type before querying results
          // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)

         ]);

         CRUD::addField([
           'name' => 'Lovgps',
           'label' => 'LOV Groupings',
           'type' => 'select2_multiple',
           'entity'    => 'Lovgps', // the method that defines the relationship in your Model
           'model'     => "App\Models\Lovgp", // foreign key model
           'attribute' => 'name', // foreign key attribute that is shown to user
           'pivot'     => true,
           'options'   => (function ($query) {
               return $query->orderBy('name', 'ASC')->get();
           }),
         ]);

         // CRUD::addField([
         //  'name'        => 'Units',
         //  'label'       => 'Units',
         //  'type'        => 'select2_from_ajax_multiple',
         //  // 'entity'      => 'Units', // the method that defines the relationship in your Model
         //  // 'model'       => \App\Models\Unit::class, // foreign key model
         //  'attribute'   => "pporgunitdesc", // foreign key attribute that is shown to user
         //  'data_source' => route('wa.findunit'), // url to controller search function (with /{id} should return model)
         //
         //  // OPTIONAL
         //  'delay'                 => 500, // the minimum amount of time between ajax requests when searching in the field
         //  'placeholder'           => "pporgunit", // placeholder for the select
         //  'minimum_input_length'  => 4, // minimum characters to type before querying results
         //  // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
         //
         // ]);
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

    // protected function fetchUser()
    // {
    //     return $this->fetch([
    //         'model' => \App\Models\User::class, // required
    //         'searchable_attributes' => ['name', 'staff_no'],
    //         'paginate' => 10, // items to show per page
    //         'query' => function($model) {
    //             return $model->where('status', 1)->get();
    //         } // to filter the results that are returned
    //     ]);
    // }
}
