<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\IdeaRequest;
use Backpack\CRUD\app\Library\Widget;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class IdeaCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class IdeaCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation { destroy as traitDestroy; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Idea::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/ideabox');
        CRUD::setEntityNameStrings('idea', 'Idea Box');
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
          'heading'      => 'Welcome to IdeaBox!',
          'content'      => 'Got any idea to share with the company? Looking for new idea to be made into a new project? Look no further!',
          'close_button' => true, // show close button or not
        ])->to('before_content');

        CRUD::addColumn(['name' => 'details', 'type' => 'text', 'escaped' => true]);
        CRUD::addColumn(['name' => 'likes', 'type' => 'number']);
        CRUD::addColumn(['name' => 'Category.name', 'type' => 'text', 'label' => 'Category']);
        CRUD::addColumn(['name' => 'User.id_name', 'type' => 'text', 'label' => 'Idea By']);
        CRUD::addColumn(['name' => 'created_at', 'type' => 'datetime']);
        CRUD::addColumn(['name' => 'status', 'type' => 'text']);
        // CRUD::addColumn([
        //    'name'  => 'likes',
        //    'label' => 'Likes',
        //    'type'  => 'model_function',
        //    'function_name' => 'getLikeNett',
        //    'orderable' => true,
        // ]);

        CRUD::addFilter([
          'type' => 'simple',
          'name' => 'own',
          'label' => 'Show Mine'
        ], false, function(){
          CRUD::addClause('where', 'user_id', '=', backpack_user()->id);
        });

        $cats = \App\Models\IdeaCategory::all()->pluck('name', 'id')->toArray();

        CRUD::addFilter([
          'name'  => 'cat',
          'type'  => 'dropdown',
          'label' => 'Category'
        ], $cats , function($value) { // if the filter is active
          CRUD::addClause('where', 'idea_category_id', '=', $value);
        });

        CRUD::addFilter([
          'name'  => 'status',
          'type'  => 'dropdown',
          'label' => 'Status'
        ], [
          'Open' => 'Open',
          'Taken' => 'Taken',
          'In Progress' => 'In Progress',
          'Launched' => 'Launched'
        ] , function($value) { // if the filter is active
          CRUD::addClause('where', 'status', '=', $value);
        });

        CRUD::addButtonFromModelFunction('line', 'getLikeBtn', 'getLikeBtn', 'beginning');

        $this->crud->removeButton('update');
        $this->crud->addButton('line', 'update', 'view', 'crud::buttons.update_mine', 'end');

        $this->crud->removeButton('delete');
        $this->crud->addButton('line', 'delete', 'view', 'crud::buttons.delete_mine', 'end');


        // CRUD::button('delete')->view('crud::buttons.delete_mine');
        // CRUD::button('update')->view('crud::buttons.update_mine');

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
        CRUD::setValidation(IdeaRequest::class);

        CRUD::setFromDb(); // fields
        CRUD::removeField('user_id');
        CRUD::removeField('status');

        CRUD::modifyField('idea_category_id', [
          'label' => 'Category',
          'type' => 'select',
          'entity'    => 'Category',
          'attribute' => 'name',
          'model'     => \App\Models\IdeaCategory::class,
          'attributes' => ['required' => 'required'],
          "relation_type" => "BelongsTo"
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
      $obj = CRUD::getCurrentEntry();
      if($obj->user_id != backpack_user()->id && !backpack_user()->isAdmin()){
        abort(403);
      }
        $this->setupCreateOperation();
        CRUD::addField([
          'name' => 'status',
          'label' => 'Status',
          'type' => 'select_from_array',
          'options' => [
            'Open' => 'Open',
            'Taken' => 'Taken',
            'In Progress' => 'In Progress',
            'Launched' => 'Launched'
          ]
        ]);
    }

    protected function setupShowOperation(){
      $this->crud->set('show.setFromDb', false);
      CRUD::addColumn(['name' => 'details', 'type' => 'text', 'escaped' => true]);
      CRUD::addColumn(['name' => 'likes', 'type' => 'number']);
      CRUD::addColumn(['name' => 'Category.name', 'type' => 'text', 'label' => 'Category']);
      CRUD::addColumn(['name' => 'User.id_name', 'type' => 'text', 'label' => 'Idea By']);
      CRUD::addColumn(['name' => 'created_at', 'type' => 'datetime']);
      CRUD::addColumn(['name' => 'status', 'type' => 'text']);

      CRUD::addButtonFromModelFunction('line', 'getLikeBtn', 'getLikeBtn', 'beginning');

      $this->crud->removeButton('update');
      $this->crud->addButton('line', 'update', 'view', 'crud::buttons.update_mine', 'end');

      $this->crud->removeButton('delete');
      $this->crud->addButton('line', 'delete', 'view', 'crud::buttons.delete_mine', 'end');
    }

    public function togglelike(){
      $obj = CRUD::getCurrentEntry();
      backpack_user()->toggleLike($obj);

      return redirect()->back();
    }

    public function update(){
      $obj = CRUD::getCurrentEntry();
      if($obj->user_id != backpack_user()->id && !backpack_user()->isAdmin()){
        abort(403);
      }
      $response = $this->traitUpdate();
      return $response;
    }

    public function destroy($id){
      $this->crud->hasAccessOrFail('delete');
      $id = $this->crud->getCurrentEntryId() ?? $id;
      $obj = \App\Models\Idea::findOrFail($id);
      if($obj->user_id != backpack_user()->id && !backpack_user()->isAdmin()){
        abort(403);
      }
      
      return $this->crud->delete($id);
    }
}
