<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AppreciateCardRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Jobs\AppCardSender;

use Auth;
/**
 * Class AppreciateCardCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AppreciateCardCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
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
        CRUD::setRoute(config('backpack.base.route_prefix') . '/appreciatecard');
        CRUD::setEntityNameStrings('Appreciation Card', 'Appreciation Card');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
      CRUD::setHeading('Sent Appreciations');
      CRUD::setSubheading('<i class="la la-heart"></i>');
      CRUD::addClause('where', 'sender_id', '=', backpack_user()->id);
        // CRUD::setFromDb(); // columns
        CRUD::addColumn(['name' => 'entry_date', 'type' => 'date', 'label' => 'Date']);
        CRUD::addColumn(['name' => 'recipient.name', 'type' => 'text', 'label' => 'Recipient']);
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

        $this->crud->removeButton('create');
        $this->crud->addButtonFromView('top', 'sendcard', 'sendcard', 'end');
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
        CRUD::setValidation(AppreciateCardRequest::class);

        CRUD::addField( ['name' => 'user_id',
         'label'       => 'Recipients',
         'type'        => 'select2_from_ajax_multiple',
         'entity'      => 'recipient', // the method that defines the relationship in your Model
         'model'       => "App\Models\User", // foreign key model
         'attribute'   => "name", // foreign key attribute that is shown to user
         'data_source' => route('wa.finduser'), // url to controller search function (with /{id} should return model)
         'attributes' => ['required' => 'required'],
         // OPTIONAL
         'delay'                 => 500, // the minimum amount of time between ajax requests when searching in the field
         'placeholder'           => "Staff No. or Name", // placeholder for the select
         'minimum_input_length'  => 4, // minimum characters to type before querying results
         // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)

        ]);

        $this->crud->addField([
            'label'     => "Card Theme",
            'name'      => 'template',
            'type'      => 'radio_image',
            'default'   => 'awesome',
            'options'   => [
                'awesome'   => "awesome",
                'gj'        => "gj",
                'superb'    => "superb",
                'welldone'  => "welldone",
            ],
        ]);

        $this->crud->addField([
            'label' => "Message",
            'name'  => 'content',
            'type'  => 'text',
            'attributes' => ['maxlength' => 250, 'required' => 'required'],
        ]);
        //
        // $this->crud->addField([
        //     'name'      => 'sender_id',
        //     'type'      => 'hidden',
        //     'default'   => Auth::user()->id,
        //  ]);
        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */

        $this->crud->removeSaveActions(['save_and_new','save_and_preview']);
        $this->crud->replaceSaveActions(
          [
            'name' => 'save_and_back',
            'button_text' => 'Send card'
          ],
        );
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
      $kad = CRUD::getCurrentEntry();

      if($kad->sender_id == backpack_user()->id || $kad->user_id == backpack_user()->id){
        return view('staff.appcards', ['card' => $kad]);
      } else {
        abort(403);
      }
    }

    public function store()
    {
        $req = CRUD::getRequest();
        // dd(CRUD::getRequest()->all());
        $user = backpack_user();
        $tosendid = [];

        // insert the record into DB
        foreach($req->user_id as $uid){
          // skip self
          if($uid == $user->id){
            continue;
          }

          $tosendid[] = $uid;
        }

        // todo: maybe prevent send to same person on the same day

        if(sizeof($tosendid) > 0){
          AppCardSender::dispatch($tosendid, $user->id, $req->template, $req->content);
          \Alert::info("Queued for delivery")->flash();
        } else {
          \Alert::warning("No valid recipient")->flash();
        }

        return $this->crud->performSaveAction();
    }
}
