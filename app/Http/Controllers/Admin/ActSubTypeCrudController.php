<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ActSubTypeRequest;
use App\common\CommonHelper;
use App\common\DiaryHelper;
use App\Models\ActivityType;
use App\Models\CompGroup;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ActSubTypeCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ActSubTypeCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    // public function __construct()
    // {
    //   $this->middleware(['permission:diary-subtype']);
    //   parent::__construct();
    // }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\ActSubType::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/ctactsubtype');
        CRUD::setEntityNameStrings('subtype', 'Subtype');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {

        $cg = CompGroup::findOrFail(request()->gid);
        if(!CommonHelper::UserCanAccessGroup($cg->id, backpack_user()->id)){
          abort(403);
        }

        $actt = ActivityType::findOrFail(request()->atid);
        CRUD::setHeading('Subtype for ' . $actt->descr);
        CRUD::setSubHeading('Group: ' . $cg->name);


        CRUD::addClause('where', 'comp_group_id', '=', request()->gid);
        CRUD::addClause('where', 'activity_type_id', '=', request()->atid);
        // CRUD::setFromDb(); // columns
        CRUD::addColumn(['name' => 'descr', 'type' => 'text']);
        $this->crud->removeButton('create');
        $this->crud->gid = request()->gid;
        $this->crud->atid = request()->atid;
        $this->crud->addButtonFromView('top', 'addsubtype', 'addsubtype', 'end');



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
        CRUD::setValidation(ActSubTypeRequest::class);

        // CRUD::setFromDb(); // fields

        CRUD::addField([
          'name' => 'comp_group_id',
          'label' => 'Division Group',
          'type' => 'select',
          'entity'    => 'CompGroup', // the method that defines the relationship in your Model
          'model'     => "App\Models\CompGroup", // foreign key model
          'attribute' => 'name', // foreign key attribute that is shown to user
          'default' => request()->gid,
          'attributes' => ['required' => 'required'],
          'allows_null' => false,
          'options'   => (function ($query) {
              return $query->where('id', request()->gid)->get();
          }),
        ]);

        CRUD::addField([
          'name' => 'activity_type_id',
          'label' => 'Activity Type',
          'type' => 'select',
          'entity'    => 'ActivityType', // the method that defines the relationship in your Model
          'model'     => "App\Models\ActivityType", // foreign key model
          'attribute' => 'descr', // foreign key attribute that is shown to user
          'default' => request()->atid,
          'attributes' => ['required' => 'required'],
          'allows_null' => false,
          'options'   => (function ($query) {
              return $query->where('id', request()->atid)->get();
          }),
        ]);

        CRUD::addField(['name' => 'descr', 'type' => 'text',
        'attributes' => ['required' => 'required']]);

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
      CRUD::addField(['name' => 'descr', 'type' => 'text',
      'attributes' => ['required' => 'required']]);
      $this->crud->route = url()->previous();
      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'Caretaker' => backpack_url('caretaker'),
          'Activity Type' => route('ctacttype.index', ['gid' => request()->gid]),
          'Sub Types' => route('ctactsubtype.index', ['gid' => request()->gid, 'atid' => request()->atid]),
          'Edit' => false
      ];
    }

    // override default list to add controls
    public function index(){
      $this->crud->hasAccessOrFail('list');

      if(!request()->filled('gid')){
        \Alert::error('Please select a group first')->flash();
        return redirect(route('caretaker.index'));
      }

      if(!request()->filled('atid')){
        \Alert::error('Please select an activity type first')->flash();
        return redirect(route('ctacttype.index', ['gid' => request()->gid]));
      }

      if(!CommonHelper::UserCanAccessGroup(request()->gid, backpack_user()->id)){
        \Alert::error('You are not allowed to access that group')->flash();
        return redirect(route('caretaker.index'));
      }

      $this->data['crud'] = $this->crud;
      $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'Caretaker' => backpack_url('caretaker'),
          'Activity Type' => route('ctacttype.index', ['gid' => request()->gid]),
          'Sub Types' => false
      ];

      // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
      return view($this->crud->getListView(), $this->data);
    }

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        if(!request()->filled('gid')){
          \Alert::error('Please select a group first')->flash();
          return redirect(route('caretaker.index'));
        }

        if(!request()->filled('atid')){
          \Alert::error('Please select an activity type first')->flash();
          return redirect(route('ctacttype.index', ['gid' => request()->gid]));
        }

        if(!CommonHelper::UserCanAccessGroup(request()->gid, backpack_user()->id)){
          \Alert::error('You are not allowed to access that group')->flash();
          return redirect(route('caretaker.index'));
        }

        $this->crud->route = url()->previous();

        // prepare the fields you need to show
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.add').' '.$this->crud->entity_name;

        $this->data['breadcrumbs'] = [
            'Home' => backpack_url('dashboard'),
            'Caretaker' => backpack_url('caretaker'),
            'Activity Type' => route('ctacttype.index', ['gid' => request()->gid]),
            'Sub Types' => route('ctactsubtype.index', ['gid' => request()->gid, 'atid' => request()->atid]),
            'Add' => false
        ];

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getCreateView(), $this->data);
    }
}
