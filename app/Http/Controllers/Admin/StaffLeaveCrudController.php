<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StaffLeaveRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use App\Models\User;
use App\Jobs\ManualLeaveZerod;
use App\common\CommonHelper;

/**
 * Class StaffLeaveCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StaffLeaveCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    // public function __construct()
    // {
    //   $this->middleware(['permission:diary-admin']);
    //   parent::__construct();
    // }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\StaffLeave::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/staffleave');
        CRUD::setEntityNameStrings('zerorized leave', 'Leave Record in trUSt');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
      // \Illuminate\Support\Facades\Log::warning('1 staff leave list ');
        CRUD::setFromDb(); // columns
        CRUD::removeColumn('user_id');

        if(request()->filled('uid')){
          $cuser = User::findOrFail(request()->uid);
          $perm = CommonHelper::UserCanAccessUser(backpack_user()->id, request()->uid);
          if(!$perm){
            CommonHelper::Log403Err(backpack_user(), $cuser, 'StaffLeave-list', 'perm ' . $perm);
            abort(403);
          }

          CRUD::setSubheading($cuser->id_name);
          CRUD::addClause('where', 'user_id', $cuser->id);

          // dont show add button for self
          $this->crud->removeButton('create');
          if($perm > 1){
            if($perm == 2 && substr($cuser->staff_no, 0, 1) != 'X'){

            } else {
              $this->crud->uid = $cuser->id;
              $this->crud->addButtonFromView('top', 'zerorizedf', 'zerorizedf', 'end');
            }

          }

        } else {
          if(backpack_user()->hasPermissionTo('super-admin') || backpack_user()->hasPermissionTo('diary-admin')){
            CRUD::addColumn(['name' => 'User.name', 'type' => 'text', 'label' => 'Staff']);
          } else {
            CommonHelper::Log403Err(backpack_user(), null, 'StaffLeave-list', 'non-admin no uid');
            abort(403);
          }
        }

        CRUD::removeColumn('leave_type_id');
        CRUD::removeColumn('created_by');
        CRUD::addColumn(['name' => 'LeaveType.descr', 'type' => 'text', 'label' => 'Leave Type']);
        CRUD::addColumn(['name' => 'Creator.name', 'type' => 'text', 'label' => 'Added By']);

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
      Widget::add([
        'type'         => 'alert',
        'class'        => 'alert alert-danger mb-2',
        'heading'      => 'Important information!',
        'content'      => 'Adding zerorized leave will set the expected hours for those date to 0. This process is not reversible. Proceed with caution.',
        'close_button' => true, // show close button or not
      ])->to('before_content');

      if(request()->filled('uid')){
        $cuser = User::findOrFail(request()->uid);
        $perm = CommonHelper::UserCanAccessUser(backpack_user()->id, request()->uid);
        if($perm < 2){
          CommonHelper::Log403Err(backpack_user(), $cuser, 'StaffLeave-create', 'perm ' . $perm);
          abort(403);
        }

        // dont allow for SP > non X*
        if($perm == 2 && substr($cuser->staff_no, 0, 1) != 'X'){
          CommonHelper::Log403Err(backpack_user(), $cuser, 'StaffLeave-create', 'perm ' . $perm);
          abort(403);
        }
        CRUD::addField(['name' => 'staff', 'type' => 'text', 'value' => $cuser->id_name,
        'attributes' => ['disabled' => 'disabled']]);
        CRUD::addField(['name' => 'user_id', 'type' => 'hidden', 'value' => $cuser->id]);
        CRUD::addField(['name' => 'uid', 'type' => 'hidden', 'value' => $cuser->id]);

      } else {
        if(backpack_user()->hasPermissionTo('super-admin') || backpack_user()->hasPermissionTo('diary-admin')){
          CRUD::addField( ['name' => 'user_id',
           'label'       => 'Staff',
           'type'        => 'select2_from_ajax',
           'entity'      => 'User', // the method that defines the relationship in your Model
           'model'       => "App\Models\User", // foreign key model
           'attribute'   => "name", // foreign key attribute that is shown to user
           'data_source' => route('wa.finduser'), // url to controller search function (with /{id} should return model)
           'attributes' => ['required' => 'required'],
           // OPTIONAL
           'delay'                 => 500, // the minimum amount of time between ajax requests when searching in the field
           'placeholder'           => "Staff No or Name", // placeholder for the select
           'minimum_input_length'  => 4, // minimum characters to type before querying results
           // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)

          ]);
        } else {
          CommonHelper::Log403Err(backpack_user(), null, 'StaffLeave-create', 'non-admin no uid');
          abort(403);
        }
      }

      CRUD::addField([
        'name'  => ['start_date', 'end_date'], // db columns for start_date & end_date
        'label' => 'Leave Date Range',
        'type'  => 'date_range',

        // OPTIONALS
        // default values for start_date & end_date
        'default'            => [date('Y-m-d'), date('Y-m-d')],
        // options sent to daterangepicker.js
        'date_range_options' => [
            'drops' => 'down', // can be one of [down/up/auto]
            'timePicker' => false,
            'locale' => ['format' => 'DD/MM/YYYY']
        ],
        'attributes' => [
          'required' => 'required',
        ],
        'wrapper'   => [
          'class'      => 'col-md-6',
        ],
      ]);

      // CRUD::addField([
      //   'name' => 'start_date',
      //   'type' => 'date',
      //   'label' => 'Start Date',
      //   'default' => date('Y-m-d'),
      //   'attributes' => [
      //     'required' => 'required',
      //   ],
      //   'wrapper'   => [
      //     'class'      => 'col-md-6',
      //   ],
      //   // optional:
      //   'date_options' => [
      //     'format' => 'DD/MM/YYYY',
      //     'language' => 'en'
      //   ],
      // ]);
      //
      // CRUD::addField([
      //   'name' => 'end_date',
      //   'type' => 'date',
      //   'label' => 'End Date',
      //   'default' => date('Y-m-d'),
      //   'attributes' => [
      //     'required' => 'required',
      //   ],
      //   'wrapper'   => [
      //     'class'      => 'col-md-6',
      //   ],
      //   // optional:
      //   'date_options' => [
      //     'format' => 'DD/MM/YYYY',
      //     'language' => 'en'
      //   ],
      // ]);

      CRUD::addField([
        'label' => 'Leave Type',
        'type'  => 'select2',
        'name'  => 'leave_type_id',// the db column for the foreign key
        'entity'  => 'LeaveType', // the method that defines the relationship in your Model
        'attribute' => 'code_descr',// foreign key attribute that is shown to user
        'model'     => 'App\Models\LeaveType',
        'wrapper'   => [
          'class'      => 'col-md-6',
        ],
        // 'pivot'     => true,
      ]);

      CRUD::addField([
        'name' => 'remark',
        'type' => 'text',
        'label' => 'Remark',
        'attributes' => ['required' => 'required', 'id' => 'parent_no', 'maxlength' => 250],
      ]);



        CRUD::setValidation(StaffLeaveRequest::class);

        // CRUD::setFromDb(); // fields

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

    public function store()
    {
      // dd('sini');
      // doublecheck permission
      $target_id = $this->crud->getRequest()->request->get('user_id');
      // \Illuminate\Support\Facades\Log::warning('1 staff leave store ' . $target_id);
      $user = backpack_user();

      $perm = CommonHelper::UserCanAccessUser($user->id, $target_id);
      // \Illuminate\Support\Facades\Log::warning('2 staff leave store ' . $perm);
      $cuser = User::findOrFail($target_id);
      if($perm < 2){
        CommonHelper::Log403Err(backpack_user(), $cuser, 'StaffLeave-store', 'perm ' . $perm);
        abort(403);
      }

      // add the additional fields
      $this->crud->addField(['type' => 'hidden', 'name' => 'is_manual']);
      $this->crud->getRequest()->request->add(['is_manual' => true]);

      $this->crud->addField(['type' => 'hidden', 'name' => 'created_by']);
      $this->crud->getRequest()->request->add(['created_by' => $user->id]);

      // save the record
      $response = $this->traitStore();
      // \Illuminate\Support\Facades\Log::warning('3 staff leave store ' . $target_id);

      // queue the job that will perform the zerorize
      $obj = CRUD::getCurrentEntry();
      ManualLeaveZerod::dispatch($obj->id);
      // \Illuminate\Support\Facades\Log::warning('4 staff leave store ' . $obj->id);

      return redirect()->route('staffleave.index',['uid'=>$target_id]);
    }
}
