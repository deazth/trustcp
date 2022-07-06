<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\NewsRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;

/**
 * Class NewsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class NewsCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation ;

    public function __construct()
    {
      $this->middleware(['permission:bc-news']);
      parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\News::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/news');
        CRUD::setEntityNameStrings('news', 'news');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        //CRUD::setFromDb(); // columns
        $this->crud->denyAccess('update');
        CRUD::removeField('user_id');
        CRUD::column('id');
        CRUD::column('title');
        CRUD::column('size');
        CRUD::column('created_at');
        CRUD::addColumn([
            'name'  => 'user_id2',
            'label' => 'Created/Updated By',
            'type' => 'relationship',
            'entity'    => 'updated_by', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => App\Models\User::class, // foreign key model
          ]);

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
        CRUD::setValidation(NewsRequest::class);


        Widget::add([

                'type'         => 'alert',
                'class'        => 'alert alert-warning',

                'content'      => 'Please have some basic HTML understanding first before posting any NEWS',
                'close_button' => true, // show close button or not



          ])->to('before_content');

        CRUD::setFromDb(); // fields
        $this->crud->denyAccess('update');
        CRUD::modifyField('content', [
            'type' => 'summernote',
            'options' => ['height' => 300],
            'attributes' => ['rows' => '5'],
        ]

        );
        $user_id = backpack_user()->id;
        CRUD::modifyField('user_id', [
        'type' => 'hidden',
        'value' =>  $user_id
        ]);



        CRUD::removeField('deleted_by');


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

    protected function setupShowOperation()
    {
        // by default the Show operation will try to show all columns in the db table,
        // but we can easily take over, and have full control of what columns are shown,
        // by changing this config for the Show operation
        $this->crud->denyAccess('update');
        //$this->crud->set('show.setFromDb', false);
        $this->crud->removeField('deleted_by');
        // example logic
        $this->crud->addColumn('user_id',['type' => "number",]);



        CRUD::addColumn([
            'name'  => 'user_id2',
            'label' => 'Created By',
            'type' => 'relationship',
            'entity'    => 'updated_by', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => App\Models\User::class, // foreign key model
          ]);

          $this->crud->removeField('deleted_by');
          CRUD::removeField('deleted_by');
        // $this->crud->removeColumn('date');
        // $this->crud->removeColumn('extras');

        // Note: if you HAVEN'T set show.setFromDb to false, the removeColumn() calls won't work
        // because setFromDb() is called AFTER setupShowOperation(); we know this is not intuitive at all
        // and we plan to change behaviour in the next version; see this Github issue for more details
        // https://github.com/Laravel-Backpack/CRUD/issues/3108
    }
}
