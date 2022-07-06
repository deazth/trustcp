<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CommonSkillsetRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CommonSkillsetCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CommonSkillsetCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function __construct()
    {
      $this->middleware(['permission:skill-list']);
      parent::__construct();
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\CommonSkillset::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/commonskillset');
        CRUD::setEntityNameStrings('commonskillset', 'common skillsets');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        //CRUD::column('id');
        //CRUD::column('created_at');
        //CRUD::column('updated_at');
        CRUD::column('category');
        //CRUD::column('skillgroup');
        CRUD::column('name');
        //CRUD::column('skilltype');
        //CRUD::column('skill_category_id');



        CRUD::addColumn([
            'name'  => 'skill_category_id',
            'label' => 'Skill Category',
            'type' => 'relationship',
            'entity'    => 'SkillCategory', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => App\Models\SkillCategory::class, // foreign key model
          ]);




          CRUD::addColumn([
            'name'  => 'skill_type_id',
            'label' => 'Skill Type',
            'type' => 'relationship',
            'entity'    => 'SkillType', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => App\Models\SkillType::class, // foreign key model
          ]);



         // CRUD::column('added_by');
          CRUD::column('skill_type_id');

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
        CRUD::setValidation(CommonSkillsetRequest::class);

        //CRUD::field('id');
        //CRUD::field('created_at');
        //CRUD::field('updated_at');


       // CRUD::field('category');

        CRUD::addField([
          'name'            => 'category',
          'label'           => "Category",
          'type'            => 'select_from_array',
          'options'         => ['p' => 'p'],
          'allows_null'     => false,
          'allows_multiple' => false,
          //'tab'             => 'Tab name here',
          'wrapperAttributes' => [
            'class' => 'col-md-3'
       ]
      ]);


        //CRUD::field('skillgroup');
        CRUD::field('name');
        //CRUD::field('skilltype');
        //CRUD::field('skill_category_id');
        CRUD::addField([
            'label' => 'Skill Category',
            'type'  => 'select',
            'name'  => 'skill_category_id',// the db column for the foreign key
            'entity'  => 'SkillCategory', // the method that defines the relationship in your Model
            'attribute' => 'name',// foreign key attribute that is shown to user
            'model'     => 'App\Models\SkillCategory',
            // 'pivot'     => true,
          ]);

        //CRUD::field('skill_type_id');
        CRUD::addField([
            'label' => 'Skill Type',
            'type'  => 'select',
            'name'  => 'skill_type_id',// the db column for the foreign key
            'entity'  => 'SkillType', // the method that defines the relationship in your Model
            'attribute' => 'name',// foreign key attribute that is shown to user
            'model'     => 'App\Models\SkillType',
            // 'pivot'     => true,
          ]);
          //CRUD::field('added_by');


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
