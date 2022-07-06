<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SkillTypeRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class SkillTypeCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SkillTypeCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function __construct()
    {
      $this->middleware(['permission:skill-type']);
      parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\SkillType::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/skilltype');
        CRUD::setEntityNameStrings('Skill Type', 'Skill Type');
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
        CRUD::addColumn([
        'name'  => 'name',
        'label' => 'Name',
        'type'  => 'text']);
        CRUD::addColumn([
        'name'  => 'skill_category_id',
        'label' => 'Skill Category',
        'type' => 'relationship',
        'entity'    => 'SkillCategory', // the method that defines the relationship in your Model
        'attribute' => 'name', // foreign key attribute that is shown to user
        'model'     => App\Models\SkillCategory::class, // foreign key model
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
        CRUD::setValidation(SkillTypeRequest::class);

        // CRUD::setFromDb(); // fields
        CRUD::addField([
        'name'  => 'name',
        'label' => 'Name',
        'type'  => 'text']);

        CRUD::addField([
        'label' => 'Skill Category',
        'type'  => 'select',
        'name'  => 'skill_category_id',// the db column for the foreign key
        'entity'  => 'SkillCategory', // the method that defines the relationship in your Model
        'attribute' => 'name',// foreign key attribute that is shown to user
        'model'     => 'App\Models\SkillCategory',
        // 'pivot'     => true,
      ]);
      CRUD::addField([
      'name'  => 'created_by',
      'value'     => '', //temporary
      'type'  => 'hidden']);
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
}
