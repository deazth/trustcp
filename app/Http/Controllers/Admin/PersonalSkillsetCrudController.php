<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PersonalSkillsetRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use App\Models\PersonalSkillset;
use App\Models\PersSkillHistory;
use App\Models\SkillType;

use App\common\SkillHelper;
use App\Models\CommonSkillset;
use Illuminate\Support\Facades\Log;

/**
 * Class PersonalSkillsetCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PersonalSkillsetCrudController extends CrudController
{
  use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
    store as traitStore;
    update as traitUpdate;
  }
  use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
    edit as traitEdit;
  }
  //use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation { destroy as traitDestroy;  }
  //use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

  /**
   * Configure the CrudPanel object. Apply settings to all operations.
   *
   * @return void
   */
  public function setup()
  {
    CRUD::setModel(\App\Models\PersonalSkillset::class);
    CRUD::setRoute(config('backpack.base.route_prefix') . '/personalskillset');
    CRUD::setEntityNameStrings('Personal Skillset', 'My Skillsets');
    //  CRUD::setResetButton(false);


  }


  /**
   * Define what happens when the List operation is loaded.
   *
   * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
   * @return void
   */
  protected function setupListOperation()
  {
    $this->crud->setOperationSetting('searchableTable', false);
    $this->crud->setOperationSetting('resetButton', false);
    $this->crud->setOperationSetting('defaultPageLength', 100);



    $this->data['breadcrumbs'] = [

      "My Skillset" => trans('personalskillset')
    ];


    CRUD::addFilter([
      'type' => 'simple',
      'name' => 'status',
      'label' => 'Active Status Only'
    ], true, function () {
      CRUD::addClause('where', 'status', '!=', 'D');
    });

    CRUD::addClause('where', 'user_id', '=', backpack_user()->id);


    CRUD::addColumn([
      'name'  => 'commonSkillset.skillType.skillCategory',
      'label' => 'Skill Category',
      'type' => 'relationship',
      'entity'    => 'CommonSkillset.SkillType.SkillCategory', // the method that defines the relationship in your Model
      'attribute' => 'name', // foreign key attribute that is shown to user
      'model'     => App\Models\SkillCategory::class, // foreign key model

    ]);

    CRUD::addColumn([
      'name'  => 'commonSkillset.skillType',
      'label' => 'Skill Type',
      'type' => 'relationship',
      'entity'    => 'CommonSkillset.SkillType', // the method that defines the relationship in your Model
      'attribute' => 'name', // foreign key attribute that is shown to user
      'model'     => App\Models\SkillType::class, // foreign key model

    ]);


    CRUD::addColumn([
      'name'  => 'common_skill_id',
      'label' => 'Common Skill',
      'type' => 'relationship',
      'entity'    => 'CommonSkillset', // the method that defines the relationship in your Model
      'attribute' => 'name', // foreign key attribute that is shown to user
      'model'     => App\Models\CommonSkillset::class, // foreign key model

    ]);
    CRUD::addColumn(
      [   // select_from_array
        'name'        => 'level',
        'type'        => 'select_from_array',
        'options'     => PersonalSkillset::level_desc,
        'allows_null' => false,
        'default'     => 'one',
        // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
      ]
    );

   // CRUD::column('status');
    CRUD::addColumn(
      [
        'label'        => 'Status',
        'type'        => 'model_function',
        'function_name' => 'active_status2',

        // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
      ]
    );

    CRUD::addColumn(
    [
      'name'  => 'updated_at', // The db column name
     // 'label' => 'Tag Name', // Table column heading
      'type'  => 'date',
      'format' => 'DD/MM/Y ', // use something else than the base.default_date_format config value
      // 'format' => 'l j F Y', // use something else than the base.default_date_format config value
  ],
  );


  }


  /**
   * Define what happens when the Create operation is loaded.
   *
   * @see https://backpackforlaravel.com/docs/crud-operation-create
   * @return void
   */
  protected function setupCreateOperation()
  {


    CRUD::setValidation(PersonalSkillsetRequest::class);
    $obj = CRUD::getCurrentEntry();
    $id = null;
    if ($obj) {
      $id = $obj->id;
    };
    $skill = PersonalSkillset::find($id);

    //dd($obj);
    //CRUD::field('id');
    //CRUD::field('created_at');
    //CRUD::field('updated_at');
    //CRUD::field('common_skill_id');
    //CRUD::field('user_id');


    CRUD::addField([
      'name'  => 'user_id',
      'value'     => backpack_user()->id, //temporary
      'type'  => 'hidden'
    ]);

    CRUD::addField([
      'name'  => 'status',
      'value'     => 'A',
      'type'  => 'hidden'
    ]);

    CRUD::addField([
      'name'  => 'prev_level',
      'value'     => '0',
      'type'  => 'hidden'
    ]);




    CRUD::addField([
      'name' => 'skill_cat_id',
      'type' => 'select2_from_array',
      'label' => 'Skill Category',
      'attributes' => ['required' => 'required'],

      'options' => collect(SkillHelper::SSGetCat()->pluck('name', 'id')),
      'allows_null' => false,
      'wrapper'   => [
        'class'      => 'form-group col-md-6'
      ],
    ]);

    CRUD::addField([
      'name' => 'skill_type_id',
      'type' => 'select2_from_ajax',
      'label' => 'Skill Type',
      'entity' => 'SkillType',
      'attribute' => 'name',
      'model'       => SkillType::class,
      'data_source' => route('wa.skilltype'),
      'allows_null' => false,
      'attributes' => ['required' => 'required'],
      'include_all_form_fields' => true,
      'dependencies' => ['skill_cat_id'],
      'minimum_input_length' => 0,
      'placeholder' => 'Select skill type',
      'wrapper'   => [
        'class'      => 'form-group col-md-6'
      ],
      'fake'     => true,



    ]);

    CRUD::addField([
      'name'  => 'common_skill_id', // the db column for the foreign key
      'type' => 'select2_from_ajax',
      'label' => 'Skillset',
      'entity'  => 'CommonSkillset', // the method that defines the relationship in your Model
      'attribute' => 'name', // foreign key attribute that is shown to user
      'model'     => CommonSkillset::class,
      'data_source' => route('wa.skillset'),
      'allows_null' => false,
      'attributes' => ['required' => 'required'],
      'include_all_form_fields' => true,
      'dependencies' => ['skill_type_id'],
      'minimum_input_length' => 0,
      'placeholder' => 'Select skillset',
      'wrapper'   => [
        'class'      => 'form-group col-md-6'
      ],

    ]);
    CRUD::addField(
      [   // radio
        'name'        => 'level', // the name of the db column
        'label'       => 'Level', // the input label
        'type'        => 'radio',
        'options'     => PersonalSkillset::level_desc,
        // optional
        'inline'      => true,
        'wrapper'   => [
          'class'      => 'form-group col-md-6'
        ],
      ]
    );

    CRUD::addField([
      'name'  => 'justification',
      'label'  => 'Justification',
      'value'     => '',
      'type'  => 'text',
      'wrapper'   => [
        'class'      => 'form-group col-md-6'
      ],
      'attributes' => [
        'required' => true,
    ],
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


  public function checkDup($user_id, $cs_id)
  {
    $cps = PersonalSkillset::where('user_id', $user_id)->where('common_skill_id', $cs_id)->count();

    if ($cps > 0) {
      return false;
    } else {
      return true;
    };
  }
  //store
  public function store()
  {
    // append extra info dari user profile
    $user = backpack_user();
    $cs = $this->crud->getRequest()->request->get('common_skill_id');
    $check =  $this->checkDup(backpack_user()->id, $cs);

    $this->crud->addField(['type' => 'hidden', 'name' => 'check_dup']);
    $this->crud->getRequest()->request->add(['check_dup' => $check]);
    $ps = $this->crud->getRequest()->request;


    $response = $this->traitStore();

    $psid = $this->crud->entry->id;



    $phist = new PersSkillHistory;
    $phist->personal_skillset_id = $psid;
    $phist->action_user_id = backpack_user()->id;
    $phist->newlevel = $ps->get('level');
    $phist->oldlevel = $ps->get('prev_level');
    $phist->action = 'Add';
    $phist->remark = $ps->get('justification');
    $phist->save();


    return $response;
  }
  //edit
  public function edit($id)
  {
    $this->data['breadcrumbs'] = [
      'My Skillset' => backpack_url('personalskillset'),

      'Edit' => trans('edit')
    ];

    $obj = CRUD::getCurrentEntry();
    $id = null;
    if ($obj) {
      $id = $obj->id;
    };
    $skill = PersonalSkillset::find($id);


    CRUD::modifyField('common_skill_id', [

      'attributes' => ['readonly' => 'readonly', 'disabled' => 'disabled'],
      'readonly' => true

    ]);

    CRUD::removeField('skill_cat_id');
    CRUD::removeField('skill_type_id');
    CRUD::removeField('prev_level');
    CRUD::addField([
      'name'  => 'prev_level',
      'value'     => $obj->level,
      'type'  => 'hidden'
    ]);


    CRUD::addField(
      [   // Checkbox
        'name'  => 'active_check',
        'label' => 'Active Status',
        'type'  => 'toggle2',
        'fake' => true,
        'value' => $obj->active_status(),
        'wrapper'   => [
          'class'      => 'form-group col-md-3 float-right'
        ],

      ],
    );


    $psh = PersSkillHistory::where('personal_skillset_id', $id);


    CRUD::addField(
      [   // view
        'name' => 'Skillset History',
        'type' => 'view',
        'view' =>  'staff/skill/skill_history',
        'skills' => $psh->get(),
        'skill' => $skill,
        'tab'   => 'History',
        'wrapper'   => [],


      ],
    );




    // <div class="form-check form-switch">


    $response = $this->traitEdit($id);





    return $response;
  }

  public function update($id)
  {


    $ps = $this->crud->getRequest()->request;
   // dd($ps->get('active_check'));

    $this->crud->getRequest()->request->add(['check_dup' => true]);
    if($ps->get('active_check') == 0){
      $this->crud->getRequest()->request->set('status' , 'D');
      $this->crud->getRequest()->request->set('level','0');
    }
    $response = $this->traitUpdate($id);



    $psid = $this->crud->entry->id;



    $phist = new PersSkillHistory;
    $phist->personal_skillset_id = $psid;
    $phist->action_user_id = backpack_user()->id;
    $phist->newlevel = $ps->get('level');
    $phist->oldlevel = $ps->get('prev_level');
    $phist->action = 'Update';
    $phist->remark = $ps->get('justification');
    $phist->save();

    return $response;
  }

  public function destroy32($id)
  {
    $this->crud->hasAccessOrFail('delete');

    return [
      'success' => true,
      'message' => [
        'title' => 'lorem ipsum',
        'text' => 'lorem ipsum',
        'type' => 'danger',
        'icon' => 'lorem ipsum',
      ]
    ];
  }

  public function destroy($id)
  {
    $psR = $this->crud->getRequest()->request;

    LOG::error($psR->justification);

    $ps = PersonalSkillset::find($id);



    //dd($id);




    return [
      'success' => true,
      'message' => [
        'title' => 'lorem ipsum',
        'text' => 'lorem ipsum',
        'type' => 'danger',
        'icon' => 'lorem ipsum',
      ]
    ];;
  }
  public function del($id)
  {
  }
}
