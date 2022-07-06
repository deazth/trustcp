<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BuildingRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class BuildingCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BuildingCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function __construct()
    {
      $this->middleware(['permission:infra-building']);
      parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Building::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/building');
        CRUD::setEntityNameStrings('New Building Location', 'Office Building Management');
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
        'name'  => 'building_name',
        'label' => 'Building Name',
        'type'  => 'text']);
        CRUD::addColumn([
        'name'  => 'a_latitude',
        'label' => 'Point A latitude',
        'type'  => 'decimals']);
        CRUD::addColumn([
        'name'  => 'a_longitude',
        'label' => 'Point A longitude',
        'type'  => 'decimals']);
        CRUD::addColumn([
        'name'  => 'b_latitude',
        'label' => 'Point B latitude',
        'type'  => 'decimals']);
        CRUD::addColumn([
        'name'  => 'b_longitude',
        'label' => 'Point B longitude',
        'type'  => 'decimals']);

        CRUD::addColumn([
        'name'  => 'Floors',
        'label' => 'Floor counts',
        'type'  => 'relationship_count']);
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
        CRUD::setValidation(BuildingRequest::class);

        // CRUD::setFromDb(); // fields
        CRUD::addField([
        'name'  => 'building_name',
        'label' => 'Building Name',
        'type'  => 'text']);
        CRUD::addField([
        'name'  => 'a_latitude',
        'label' => 'Point A latitude',
        'type'  => 'number',
        'attributes' => ["step" => "any"] // allow decimals
      ]);
        CRUD::addField([
        'name'  => 'a_longitude',
        'label' => 'Point A longitude',
        'type'  => 'number',
        'attributes' => ["step" => "any"] // allow decimals
      ]);
        CRUD::addField([
        'name'  => 'b_latitude',
        'label' => 'Point B latitude',
        'type'  => 'number',
        'attributes' => ["step" => "any"] // allow decimals
      ]);
        CRUD::addField([
        'name'  => 'b_longitude',
        'label' => 'Point B longitude',
        'type'  => 'number',
        'attributes' => ["step" => "any"] // allow decimals
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
    }
}
