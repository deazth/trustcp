<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AssistantRequest;
use Backpack\CRUD\app\Library\Widget;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class AssistantCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AssistantCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Assistant::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/assistant');
        CRUD::setEntityNameStrings('assistant', 'assistants');
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

        Widget::add([
          'type'         => 'alert',
          'class'        => 'alert alert-info mb-2',
          'heading'      => 'Access Delegation',
          'content'      => 'Too lazy to monitor your subordinates on your own? Worry not! Get your assistant to do it for you!',
          'close_button' => true, // show close button or not
        ])->to('before_content');

        if(backpack_user()->hasPermissionTo('super-admin')){
          CRUD::addFilter([
            'type' => 'simple',
            'name' => 'own',
            'label' => 'Show Mine'
          ], false, function(){
            CRUD::addClause('where', 'user_id', '=', backpack_user()->id);
          });
        } else {
          CRUD::addClause('where', 'user_id', '=', backpack_user()->id);
          CRUD::addClause('orWhere', 'assist_id', '=', backpack_user()->id);
        }
        
        CRUD::addColumn(['name' => 'User.id_name', 'type' => 'text', 'label' => 'User']);
        CRUD::addColumn(['name' => 'Assistant.id_name', 'type' => 'text', 'label' => 'Assistant']);
        CRUD::addColumn(['name' => 'created_at', 'type' => 'datetime']);

        if(backpack_user()->hasPermissionTo('super-admin')){
          CRUD::addColumn(['name' => 'Creator.id_name', 'type' => 'text', 'label' => 'Creator']);
        }

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
        CRUD::setValidation(AssistantRequest::class);

        // CRUD::setFromDb(); // fields
        if(backpack_user()->hasPermissionTo('super-admin')){
          CRUD::addField([
           'name'        => 'User',
           'label'       => 'User',
           'type'        => 'select2_from_ajax',
           'entity'      => 'User', // the method that defines the relationship in your Model
           'model'       => "App\Models\User", // foreign key model
           'attribute'   => "name", // foreign key attribute that is shown to user
           'data_source' => route('wa.finduser'), // url to controller search function (with /{id} should return model)
           // 'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?
           'attributes' => ['required' => 'required'],
           // OPTIONAL
           'delay'                 => 500, // the minimum amount of time between ajax requests when searching in the field
           'placeholder'           => "Staff No. or Name", // placeholder for the select
           'minimum_input_length'  => 4, // minimum characters to type before querying results
           // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)

          ]);
        } else {
          CRUD::addField([
            'name' => 'user_id',
            'type' => 'hidden',
            'value' => backpack_user()->id
          ]);
        }

        CRUD::addField([
         'name'        => 'Assistant',
         'label'       => 'Assistant',
         'type'        => 'select2_from_ajax',
         'entity'      => 'Assistant', // the method that defines the relationship in your Model
         'model'       => "App\Models\User", // foreign key model
         'attribute'   => "name", // foreign key attribute that is shown to user
         'data_source' => route('wa.finduser'), // url to controller search function (with /{id} should return model)
         // 'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?
         'attributes' => ['required' => 'required'],
         // OPTIONAL
         'delay'                 => 500, // the minimum amount of time between ajax requests when searching in the field
         'placeholder'           => "Staff No. or Name", // placeholder for the select
         'minimum_input_length'  => 4, // minimum characters to type before querying results
         // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)

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
