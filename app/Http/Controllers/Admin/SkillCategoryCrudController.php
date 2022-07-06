<?php

namespace App\Http\Controllers\Admin;


use App\Http\Requests\SkillCategoryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;

/**
 * Class SkillCategoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SkillCategoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function __construct()
    {
      $this->middleware(['permission:skill-category']);
      parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\SkillCategory::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/skillcategory');
        CRUD::setEntityNameStrings('Skill Category', 'Skill Category Management');
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
        'name'  => 'sequence',
        'label' => 'Sequence Order',
        'type'  => 'number']);
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
        CRUD::setValidation(SkillCategoryRequest::class);

        // CRUD::setFromDb(); // fields
        CRUD::addField([
        'name'  => 'name',
        'label' => 'Name',
        'type'  => 'text']);
        CRUD::addField([
        'name'  => 'sequence',
        'label' => 'Sequence Order',
        'type'  => 'number']);
        CRUD::addField([
        'name'  => 'created_by',
        'value'     =>  '', //temporary
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

    public function update(UpdateRequest $request)
{
    // your additional operations before save here
    $redirect_location = parent::updateCrud($request);
    return $redirect_location;
}
}
