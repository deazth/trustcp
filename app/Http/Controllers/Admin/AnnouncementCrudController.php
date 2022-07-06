<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AnnouncementRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class AnnouncementCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AnnouncementCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function __construct()
    {
      $this->middleware(['permission:bc-announcement']);
      parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Announcement::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/announcement');
        CRUD::setEntityNameStrings('announcement', 'announcements');
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

        // CRUD::removeColumn('deleted_by');


        CRUD::addColumn([
          'name' => 'content',
          // 'label' => 'Content',
          'type' => 'text'
        ]);

        CRUD::addColumn([
          'name' => 'start_date',
          // 'label' => 'Content',
          'type' => 'date'
        ]);

        CRUD::addColumn([
          'name' => 'end_date',
          // 'label' => 'Content',
          'type' => 'date'
        ]);

        CRUD::addColumn([
          'name' => 'url',
          // 'label' => 'Content',
          'type' => 'text'
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
        CRUD::setValidation(AnnouncementRequest::class);

        CRUD::setFromDb(); // fields
        CRUD::removeField('deleted_by');
        CRUD::removeField('url_text');

        CRUD::modifyField('added_by', [
          'type' => 'hidden',
          'value' => backpack_user()->id
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

        CRUD::addField([
            'name'  => 'last_admin',
            'label' => 'Added By',
            'type'  => 'text',
            'value' => CRUD::getCurrentEntry()->AddedBy->id_name,
            'attributes' => ['readonly' => 'readonly']
        ]);
    }
}
