<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\GwdActivityRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\common\DiaryHelper;
use App\common\UserHelper;
use App\common\CommonHelper;
use App\common\TribeApiCallHandler;
use App\Models\ActivityType;
use App\Models\ActSubType;
use App\Models\GwdActivity;
use App\Models\User;
use App\Models\DailyPerformance;
use Backpack\CRUD\app\Library\Widget;

/**
 * Class GwdActivityCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class GwdActivityCrudController extends CrudController
{
  use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
  use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
    store as traitStore;
  }
  use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
    update as traitUpdate;
  }
  use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
  // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

  /**
   * Configure the CrudPanel object. Apply settings to all operations.
   *
   * @return void
   */
  public function setup()
  {
    CRUD::setModel(\App\Models\GwdActivity::class);
    CRUD::setRoute(config('backpack.base.route_prefix') . '/gwdactivity');
    CRUD::setEntityNameStrings('Diary Entry', 'Diary Entries');
    $this->crud->enableExportButtons();
  }

  /**
   * Define what happens when the List operation is loaded.
   *
   * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
   * @return void
   */
  protected function setupListOperation()
  {
    CRUD::setHeading('My Diary Entries');
    $tuserid = backpack_user()->id;

    $req = CRUD::getRequest();
    if($req->filled('uid')){
      $tuserid = $req->uid;
      if(!\App\common\CommonHelper::UserCanAccessUser(backpack_user()->id, $req->uid)){
        abort(403);
      }

      $tuser = User::findOrFail($tuserid);
      CRUD::setSubheading($tuser->id_name);

      if(backpack_user()->id != $tuserid){
        $this->crud->denyAccess(['update', 'create', 'delete']);
      }
    } else {

      CRUD::setSubheading(backpack_user()->id_name);
    }

    // CRUD::setFromDb(); // columns
    /**
     * Columns can be defined using the fluent syntax or array syntax:
     * - CRUD::column('price')->type('number');
     * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
     */
    // CRUD::removeColumn('user_id');
    CRUD::addClause('where', 'user_id', '=', $tuserid);

    CRUD::addColumn(['name' => 'activity_date', 'type' => 'date', 'label' => 'Activity Date']);
    CRUD::addColumn(['name' => 'hours_spent', 'type' => 'number', 'label' => 'Hours', 'decimals'     => 1]);
    CRUD::addColumn(['name' => 'ActivityTag.descr', 'type' => 'text', 'label' => 'Tag', 'priority' => 2]);
    CRUD::addColumn(['name' => 'ActivityType.descr', 'type' => 'text', 'label' => 'Type', 'priority' => 2]);
    CRUD::addColumn(['name' => 'ActSubType.descr', 'type' => 'text', 'label' => 'Subtype', 'priority' => 3]);
    CRUD::addColumn(['name' => 'parent_number', 'type' => 'textarea', 'label' => 'ID / Title', 'priority' => 1, 'escaped' => true]);
    CRUD::addColumn(['name' => 'details', 'type' => 'textarea', 'label' => 'Details', 'priority' => 3, 'escaped' => true]);
    CRUD::addColumn(['name' => 'created_at', 'type' => 'datetime', 'label' => 'Date Entered', 'priority' => 3]);


    $this->crud->addFilter(
      [
        'type'  => 'date_range',
        'name'  => 'act_range',
        'label' => 'Date Range'
      ],
      false,
      function ($value) { // if the filter is active, apply these constraints
        $dates = json_decode($value);
        $this->crud->addClause('where', 'activity_date', '>=', $dates->from);
        $this->crud->addClause('where', 'activity_date', '<=', $dates->to);
      }
    );

    $this->crud->addFilter(
      [
        'type'  => 'date',
        'name'  => 'act_date',
        'label' => 'Specific Date'
      ],
      false,
      function ($value) { // if the filter is active, apply these constraints
        $this->crud->addClause('whereDate', 'activity_date', $value);
      }
    );

    // $this->crud->denyAccess(['update', 'create', 'delete']);
  }

  /**
   * Define what happens when the Create operation is loaded.
   *
   * @see https://backpackforlaravel.com/docs/crud-operation-create
   * @return void
   */
  protected function setupCreateOperation()
  {
    CRUD::setValidation(GwdActivityRequest::class);

    // CRUD::setFromDb(); // fields

    // selit terus hidden fields
    // CRUD::addField(['name' => 'user_id', 'type' => 'hidden', 'value' => backpack_user()->id]);
    // CRUD::addField(['name' => 'title', 'type' => 'hidden', 'value' => 'web']);

    // activity_date
    $prevdate = \Session::get('prevdate', date('Y-m-d'));
    CRUD::addField([
      'name' => 'activity_date',

      'type' => 'date',
      'label' => 'Activity Date',
      'default' => $prevdate,
      'attributes' => [
        'max' => date('Y-m-d'),

        'required' => 'required',
        'tabindex' => 1,
        'id' => 'activity_date_id'
      ],
      'wrapper'   => [
        'class'      => 'col-md-6',
      ],
      // optional:
      'date_options' => [
        'format' => 'DD/MM/YYYY HH:mm',
        'language' => 'en'
      ],
    ]);

    CRUD::addField([
      'name' => 'hours_spent',
      'type' => 'number',
      'label' => 'Hours Spent',
      'default' => 1,
      'attributes' => ['required' => 'required', 'min' => 0.1, 'max' => 24, 'step' => 0.1, 'tabindex' => 2],
      'wrapper'   => [
        'class'      => 'form-group col-md-6'
      ],
    ]);
    // activity tag
    $at = collect(UserHelper::GetUserTaskCat(backpack_user()->id))->prepend(['id' => '', 'descr' => 'Please Select Value'])->pluck('descr', 'id');
    //dd($at);
    CRUD::addField([
      'name' => 'task_category_id',
      'type' => 'select2_from_array',
      'label' => 'Activity Tag',
      'attributes' => ['required' => 'required', 'tabindex' => '4'],
      // 'options' => DiaryHelper::GetActTag(backpack_user()->id),
      'options' => $at,
      'allows_null' => false,
      'placeholder' => 'Please select value ...',
      'wrapper'   => [
        'class'      => 'col-md-6',
        'tabindex' => '3'
      ],
    ]);

    CRUD::addField([
      'name' => 'parent_number',
      'type' => 'text',
      'label' => 'ID / Title',
      'attributes' => ['required' => 'required', 'id' => 'parent_no', 'maxlength' => 250],
      'wrapper'   => [
        'class'      => 'form-group col-md-6',
        'tabindex' => '4'
      ],
    ]);

    CRUD::addField([
      'name'  => 'opendiv',
      'type'  => 'custom_html',
      'value' => '<div class="no-gutter col-md-12" style="background-color: coral">',
      'wrapper'   => [
        'class'      => 'col-md-6 no-gutter '
      ],


    ]);





    // activity type
    CRUD::addField([
      'name' => 'activity_type_id',
      'type' => 'select2_from_ajax',
      'label' => 'Activity Type',
      'entity' => 'ActivityType',
      'attribute' => 'descr',
      'model'       => ActivityType::class,
      'data_source' => route('wa.getacttype'),
      'allows_null' => false,
      'attributes' => ['required' => 'required'],
      'include_all_form_fields' => true,
      'dependencies' => ['task_category_id'],
      'minimum_input_length' => 0,
      'placeholder' => 'Select an activity type',
      'wrapper'   => [
        'class'      => 'form-group',
        'tabindex' => '4'
      ],
    ]);



    // activity tag
    CRUD::addField([
      'name' => 'act_sub_type_id',
      'type' => 'select2_from_ajax',
      'label' => 'Activity Subtype',
      'entity' => 'ActSubType',
      'attribute' => 'descr',
      'model'       => ActSubType::class,
      'data_source' => route('wa.getactsubtype'),
      'allows_null' => true,
      'include_all_form_fields' => true,
      'dependencies' => ['activity_type_id', 'task_category_id'],
      'minimum_input_length' => 0,
      'placeholder' => 'Optional - Activity Subtype',
      'wrapper'   => [
        'class'      => 'form-group'
      ],
    ]);
    CRUD::addField([
      'name'  => 'closing rows',
      'type'  => 'custom_html',
      'value' => '</div> <!---closediv--->'


    ]);


    CRUD::addField([
      'name' => 'details',
      'type' => 'textarea',
      'label' => 'Details',
      'attributes' => ['required' => 'required'],
      'wrapper'   => [
        'class'      => 'form-group col-md-6'
      ],
      'attributes' => ['rows' => '4'],
      'placeholder' => 'TRIBE id',


    ]);






    $amik1 = [];
    // $cctribe = CommonConfig::where('key', 'tribe')->first();
    $cctribe = CommonHelper::GetCConfig('tribe', 'true') == 'true';

    // $cctribe = collect([['value'=>'true']])->first();
    // if ($cctribe && $cctribe['value']== 'true') {

    $user = User::find(backpack_user()->id);

    $ass = TribeApiCallHandler::getTribeAssigment($user);
    $amik1 = $ass["assigments"];
    $exist = true;

    $kira = count($amik1);
    if ($kira == 0) {
      $exist = false;
    }


    CRUD::addField([
      'name' => 'tribe_id',
      'type' => 'text_with_reset',
      'placeholder' => 'TRIBE id',

      'label' => 'TRIBE Assignment ID',
      'reset_label' => 'Remove TRIBE id',
      'attributes' => ['id' => 'tribe_assigment_id', 'readonly' => 'true', 'placeholder' => 'Select TRIBE ID'],
      'allows_null' => true,
      'wrapper'   => [
        'class'      => 'form-group col-md-3 col-sm-12'
      ],

      'tab'   => 'TRIBE Assigment',
      'reset_fields' => ['tribe_assigment_id', 'parent_no']
    ]);

    CRUD::addField(
      [   // view
        'name' => 'List of Assignments',
        'type' => 'view',
        'view' =>  'tribe/list_of_assigments',
        'assignments' => $amik1,
        'tab'   => 'TRIBE Assigment',
        'wrapper'   => [],


      ],
    );

    CRUD::addField(
      [   // view
        'name' => 'Diary Entries',
        'type' => 'view',
        'view' =>  'diary/list_of_entries',
        'uid' =>  backpack_user()->id,


        'tab'   => 'Diary Entries',
        'wrapper'   => [],


      ],
    );





    // CRUD::addField([
    //  'name'        => 'user_id',
    //  'label'       => 'Owner',
    //  'type'        => 'select2_from_ajax',
    //  'entity'      => 'Owner', // the method that defines the relationship in your Model
    //  'model'       => "App\Models\User", // foreign key model
    //  'attribute'   => "name", // foreign key attribute that is shown to user
    //  'data_source' => route('wa.finduser'), // url to controller search function (with /{id} should return model)
    //  // OPTIONAL
    //  'delay'                 => 500, // the minimum amount of time between ajax requests when searching in the field
    //  'placeholder'           => "Staff No. or Name", // placeholder for the select
    //  'minimum_input_length'  => 4, // minimum characters to type before querying results
    //  // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
    //
    // ]);





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
    if (!CommonHelper::UserCanAccessUser(backpack_user()->id, $obj->user_id)) {
      abort(403);
    }
    $this->setupCreateOperation();

    $df = DiaryHelper::GetDailyPerfObj($obj->user_id, $obj->activity_date);
    // dd($df);

    CRUD::modifyField('activity_date', [
      'attributes' => ['readonly' => 'readonly', 'id' => 'activity_date_id'],
      'format' => 'l j F Y',
    ]);




    if ($df->expected_hours == 0) {
      $perc = 100;
    } elseif ($df->actual_hours > $df->expected_hours) {
      $perc = 100;
    } else {
      $perc = intval($df->actual_hours / $df->expected_hours * 100);
    }

    $bg = 'bg-success';
    if ($perc < 85) {
      $bg = 'bg-info';
    }

    if ($perc < 50) {
      $bg = 'bg-warning';
    }

    if ($perc == 0) {
      $bg = 'bg-danger';
    }



    Widget::add([
      'type'        => 'progress',
      'class'       => 'card text-white mb-2 ' . $bg,
      'value'       => ($df->actual_hours + 0) . ' / ' . ($df->expected_hours + 0) . ' hours',
      'description' => $df->record_date,
      'progress'    => $perc, // integer
      'hint'        => 'Current total / expected',
      'wrapper' => [
        'class' => 'col-md-6 m-0 p-0', // customize the class on the parent element (wrapper)
      ]
    ])->to('after_content');
  }

  // override default operations
  public function store()
  {
    // append extra info dari user profile
    $user = backpack_user();
    $this->crud->addField(['type' => 'hidden', 'name' => 'user_id']);
    $this->crud->getRequest()->request->add(['user_id' => $user->id]);

    $this->crud->addField(['type' => 'hidden', 'name' => 'unit_id']);
    $this->crud->getRequest()->request->add(['unit_id' => $user->unit_id]);

    $df = DiaryHelper::GetDailyPerfObj($user->id, $this->crud->getRequest()->request->get('activity_date'));
    $this->crud->addField(['type' => 'hidden', 'name' => 'daily_performance_id']);
    $this->crud->getRequest()->request->add(['daily_performance_id' => $df->id]);

    $hrspent = $this->crud->getRequest()->request->get('hours_spent');
    $newtotal = $df->actual_hours + $hrspent;
    $this->crud->getRequest()->request->add(['total_daily_hours' => $newtotal]);
    $this->crud->addField(['type' => 'hidden', 'name' => 'title']);
    $this->crud->getRequest()->request->add(['title' => 'web']);

    // dd($this->crud->getStrippedSaveRequest());

    // $reflFunc = new \ReflectionMethod($this->crud, 'validateRequest');
    // dd( $reflFunc->getFileName() . ':' . $reflFunc->getStartLine());

    // dd($this->crud->getStrippedSaveRequest());

    $response = $this->traitStore();
    // update the df actual hours
    $df->actual_hours += $hrspent;
    $df->save();
    \Session::flash('prevdate', $this->crud->getRequest()->request->get('activity_date'));

    return $response;
  }

  public function update()
  {

    // only allow owner to edit
    $obj = CRUD::getCurrentEntry();
    // dd($obj);
    if (!CommonHelper::UserCanAccessUser(backpack_user()->id, $obj->user_id)) {
      abort(403);
    }

    // check if the new value will cause issue
    $df = DiaryHelper::GetDailyPerfObj($obj->user_id, $obj->activity_date);
    $hrspent = $this->crud->getRequest()->request->get('hours_spent');
    $diff = $obj->hours_spent - $hrspent;
    $newtotal = $df->actual_hours - $diff;
    $this->crud->getRequest()->request->add(['total_daily_hours' => $newtotal]);

    // dd($this->crud->getRequest()->all());

    $response = $this->traitUpdate();
    $df->actual_hours -= $diff;
    $df->save();

    return $response;
  }

  public function destroy($id)
  {
    $this->crud->hasAccessOrFail('delete');
    $id = $this->crud->getCurrentEntryId() ?? $id;

    $obj = GwdActivity::findOrFail($id);
    if (!CommonHelper::UserCanAccessUser(backpack_user()->id, $obj->user_id)) {
      abort(403);
    }

    // deduct the hour from df
    $df = DiaryHelper::GetDailyPerfObj($obj->user_id, $obj->activity_date);
    $df->actual_hours -= $obj->hours_spent;
    $df->save();

    return $this->crud->delete($id);
  }


  public function listbydate($uid, $dt)
  {

    $gwd = GwdActivity::where('user_id', $uid)->where('activity_date', $dt)
      ->with('ActivityTag', 'ActivityType')
      ->get();
    return ["data" => $gwd];
  }





}
