<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AppreciateCardRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use Auth;
/**
 * Class AppreciateCardCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ReceivedCardCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\AppreciateCard::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/receivedcard');
        CRUD::setEntityNameStrings('Appreciation Card', 'Received Appreciation Card');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
      CRUD::addClause('where', 'user_id', '=', backpack_user()->id);
      CRUD::setHeading('My Received Appreciations');
      CRUD::setSubheading('<i class="la la-heart"></i>');
        // CRUD::setFromDb(); // columns
        CRUD::addColumn(['name' => 'entry_date', 'type' => 'date', 'label' => 'Date']);
        CRUD::addColumn(['name' => 'sender.name', 'type' => 'text', 'label' => 'Sender']);
        CRUD::addColumn([
          'name'  => 'template',
          'label' => 'Template',
          'type' => 'select_from_array',
          'options' => [
            'awesome'   => "Awesome",
            'gj'        => "Good Job",
            'superb'    => "Superb",
            'welldone'  => "Well Done",
          ]
        ]);
        CRUD::addColumn(['name' => 'content', 'type' => 'text', 'label' => 'Content']);

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
        //
        // $this->crud->removeButton('create');
        // $this->crud->addButtonFromView('top', 'sendcard', 'sendcard', 'end');
        CRUD::addButtonFromModelFunction('line', 'viewCard', 'viewCard', 'end');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
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

    public function previewCard(){
      return view('staff.appcards', ['card' => CRUD::getCurrentEntry()]);
    }
}
