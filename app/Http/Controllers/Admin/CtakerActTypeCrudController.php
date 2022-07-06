<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ActivityTypeRequest;
use App\common\CommonHelper;
use App\common\DiaryHelper;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ActivityTypeCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CtakerActTypeCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\ActivityType::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/ctacttype');
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

        // filter the data
        $taglist = DiaryHelper::GetGrpActType(request()->gid);
        CRUD::addClause('whereIn', 'id', $taglist);

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */



        CRUD::addColumn([
         'name' => 'stype',
         'label' => 'Subtype',
         'type' => 'model_function',
         'function_name' => 'GrpSubTypes',
         'function_parameters' => [request()->gid]
        ]);
        CRUD::removeColumn('status');
        CRUD::removeColumn('remark');

        CRUD::addButtonFromModelFunction('line', 'showActSubTypeList', 'showActSubTypeList', 'end');
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

    protected function setupShowOperation()
    {
      $this->crud->route = url()->previous();
      // get the param from previous url
      $parsedUrl = parse_url($this->crud->route);
      // 
      // $parsedUrl['post']; // www.example.com
      // $parsedUrl['path']; // /posts
      // $parsedUrl['query']; // param=val&param2=val
      parse_str($parsedUrl['query'], $output);

      $this->data['crud'] = $this->crud;
      $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);
      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'Caretaker' => backpack_url('caretaker'),
          'Activity Types' => $this->crud->route,
          'Preview' => false
      ];

      $this->crud->set('show.setFromDb', false);
      CRUD::setFromDb(); // columns
      CRUD::addColumn([
       'name' => 'stype',
       'label' => 'Subtype',
       'type' => 'model_function',
       'function_name' => 'GrpSubTypes',
       'function_parameters' => [$output['gid']]
      ]);
      CRUD::removeColumn('status');
      // CRUD::removeColumn('remark');
      CRUD::addButtonFromModelFunction('line', 'showActSubTypeList', 'showActSubTypeList', 'end');

    }

    // override default list to add controls
    public function index(){
      $this->crud->hasAccessOrFail('list');

      if(!request()->filled('gid')){
        \Alert::error('Please select a group first')->flash();
        return redirect(route('caretaker.index'));
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
          'Activity Types' => false
      ];

      // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
      return view($this->crud->getListView(), $this->data);
    }
}
