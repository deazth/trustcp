<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CtUserManageRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\CompGroup;
use App\Models\User;
use App\Models\UserTeamHistory;
use App\common\CommonHelper;
use Exception;

/**
 * Class CtUserManageCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CtUserManageCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/ct-user-manage');
        CRUD::setEntityNameStrings('Manage User', 'Manage User');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
      // check if this user is allowed
      if(!backpack_user()->hasPermissionTo('diary-admin')){
        if(!request()->filled('gid')){
          abort(403);
        }
        // get allowed group

        $im_ct = backpack_user()->CompGroups->where('id', request()->gid)->first();

        if($im_ct){
        } else {
          // im not caretaker for this group
          abort(403);
        }
      } else {
        CRUD::addButtonFromModelFunction('line', 'leaveFromSAP', 'leaveFromSAP', 'end');
        CRUD::addButtonFromModelFunction('line', 'loadedLeave', 'loadedLeave', 'end');
      }

      CRUD::addColumn(['name' => 'name', 'type' => 'text']);
      CRUD::addColumn(['name' => 'email', 'type' => 'email']);
      CRUD::addColumn(['name' => 'staff_no', 'type' => 'text']);
      CRUD::addColumn(['name' => 'sp_name', 'type' => 'text', 'label' => 'Report To']);
      CRUD::addColumn(['name' => 'Division.pporgunitdesc', 'type' => 'text', 'label' => 'Org Unit']);

      if(request()->filled('gid')){

        $cg = CompGroup::findOrFail(request()->gid);
        CRUD::setSubheading('Group: ' . $cg->name);

        // dd($cg->Units->pluck('id')->toArray());


        CRUD::addClause('whereIn', 'unit_id', $cg->Units->pluck('id')->toArray());
        $this->data['breadcrumbs'] = [
            'Home' => backpack_url('dashboard'),
            'Caretaker' => backpack_url('caretaker'),
            $cg->name . ' Users' => route('ct-user-manage.index', ['gid' => $cg->id]),
            'List' => false
        ];

      } else {
        CRUD::addColumn(['name' => 'Division.comp_group.name', 'type' => 'text', 'label' => 'Group']);
      }

      CRUD::addColumn(['name' => 'persno', 'type' => 'text']);
      CRUD::addColumn(['name' => 'Boss.id_name', 'type' => 'text', 'label' => 'Report To']);
      CRUD::addColumn(['name' => 'status', 'type' => 'select_from_array', 'options' => [
        '0' => 'Inactive',
        '1' => 'Active'
      ]]);


        // CRUD::setFromDb(); // columns
        // CRUD::removeColumn('role');
        // CRUD::removeColumn('curr_reserve');
        // CRUD::removeColumn('curr_checkin');
        // CRUD::removeColumn('last_checkin');
        // CRUD::removeColumn('photo_url');
        // CRUD::removeColumn('allowed_building');
        // CRUD::removeColumn('lob');
        // CRUD::removeColumn('unit');
        // CRUD::removeColumn('subunit');
        // CRUD::removeColumn('curr_attendance');
        // CRUD::removeColumn('avatar_rank');
        // CRUD::removeColumn('division_id');
        // CRUD::removeColumn('verified');
        // CRUD::removeColumn('last_location_id');
        // CRUD::removeColumn('new_ic');
        // CRUD::removeColumn('partner_id');




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
        // CRUD::setValidation(CtUserManageRequest::class);

        CRUD::setFromDb(); // fields

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
      $perm = CommonHelper::UserCanAccessUser(backpack_user()->id, $obj->id);
      if($perm < 2){
        abort(403);
      }

      $boss_id = 0;
      try{
        $boss_id = $obj->Boss->id;
      }catch(Exception $e){}

      CRUD::addField(['name' => 'name', 'type' => 'text', 'attributes' => ['disabled' => true]]);
      CRUD::addField(['name' => 'Boss.id_name', 'label' => 'Current Report To', 'type' => 'text', 'attributes' => ['disabled' => true]]);

      CRUD::addField( ['name' => 'boss_id',
       'label'       => 'New Report To',
       'type'        => 'select2_from_ajax',
       'entity'      => 'Boss', // the method that defines the relationship in your Model
       'model'       => "App\Models\User", // foreign key model
       'attribute'   => "name", // foreign key attribute that is shown to user
       'data_source' => route('wa.finduser'), // url to controller search function (with /{id} should return model)
       'default'     =>  $boss_id ,
       'attributes' => ['required' => 'required'],
       // OPTIONAL
       'delay'                 => 500, // the minimum amount of time between ajax requests when searching in the field
       'placeholder'           => "Staff No or Name", // placeholder for the select
       'minimum_input_length'  => 4, // minimum characters to type before querying results
       // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)

      ]);

      CRUD::addField(['name' => 'Division.long_name', 'label' => 'Current OrgUnit', 'type' => 'text', 'attributes' => ['disabled' => true]]);

      CRUD::addField( ['name' => 'unit_id',
       'label'       => 'New OrgUnit',
       'type'        => 'select2_from_ajax',
       'entity'      => 'Division', // the method that defines the relationship in your Model
       'model'       => "App\Models\Unit", // foreign key model
       'attribute'   => "pporgunitdesc", // foreign key attribute that is shown to user
       'data_source' => route('wa.findunit'), // url to controller search function (with /{id} should return model)
       'attributes' => ['required' => 'required'],
       // OPTIONAL
       'delay'                 => 500, // the minimum amount of time between ajax requests when searching in the field
       'placeholder'           => "ppOrgUnit", // placeholder for the select
       'minimum_input_length'  => 4, // minimum characters to type before querying results
       // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)

      ]);

      if($perm >= 4 || ($perm > 1 && substr($obj->staff_no, 0, 2) == 'XL')){
        CRUD::addField(['name' => 'status', 'type' => 'select_from_array', 'options' => [
          '0' => 'Inactive',
          '1' => 'Active'
        ], 'allows_null' => false]);
      }

      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'Caretaker' => backpack_url('caretaker'),
          'User List' => url()->previous(),
          'Edit user' => false
      ];
      // $this->crud->route = url()->previous();
    }

    protected function setupShowOperation()
    {
      // $this->crud->set('show.setFromDb', false);
      // CRUD::setFromDb(); // columns
      //
      // // get allowed group
      // // CRUD::addClause('whereIn', 'id', backpack_user()->CompGroups->pluck('id')->toArray());
      //
      // CRUD::removeColumn('role');
      // CRUD::removeColumn('curr_reserve');
      // CRUD::removeColumn('curr_checkin');
      // CRUD::removeColumn('last_checkin');
      // CRUD::removeColumn('photo_url');
      // CRUD::removeColumn('allowed_building');
      // CRUD::removeColumn('lob');
      // CRUD::removeColumn('unit');
      // CRUD::removeColumn('subunit');
      // CRUD::removeColumn('curr_attendance');
      // CRUD::removeColumn('avatar_rank');
      // CRUD::removeColumn('division_id');
      // CRUD::removeColumn('verified');
      // CRUD::removeColumn('last_location_id');
      // CRUD::removeColumn('new_ic');
      // CRUD::removeColumn('partner_id');

      $this->crud->route = url()->previous();

      // CRUD::addColumn(['name' => 'Units',
      //   'label' => 'Units',
      //   'type' => 'relationship',
      //   'entity'    => 'Units',
      //   'attribute' => 'pporgunitdesc',
      //   'model'     => App\Models\Unit::class,
      //   "relation_type" => "hasMany"
      // ]);
      //
      // CRUD::addColumn(['name' => 'Caretakers',
      //   'label' => 'Caretakers',
      //   'type' => 'relationship',
      //   'entity'    => 'Caretakers',
      //   'attribute' => 'id_name',
      //   'model'     => App\Models\User::class,
      //   "relation_type" => "belongsToMany"
      // ]);

    }

    public function update(){
      $obj = CRUD::getCurrentEntry();

      if(!backpack_user()->hasPermissionTo('diary-admin') && !backpack_user()->hasPermissionTo('super-admin')){
        $perm = CommonHelper::UserCanAccessUser(backpack_user()->id, $obj->id);
        if($perm < 2){
          abort(403);
        }
      }


      $oldboss = $obj->Boss;
      $nuboss = User::findOrFail($this->crud->getRequest()->request->get('boss_id'));
      $uth = new UserTeamHistory;
      $uth->user_id = $obj->id;
      $uth->old_superior_id = $oldboss ? $oldboss->id : null ;
      $uth->new_superior_id = $nuboss->id;
      $uth->edited_by = backpack_user()->id;
      $uth->remark = 'Caretaker edit';
      $uth->save();

      $obj->report_to = $nuboss->persno;
      $obj->report_to_id = $nuboss->id;
      $obj->save();

      $response = $this->traitUpdate();

      $obj = CRUD::getCurrentEntry();
      $nudiv = $obj->Division;
      $obj->lob = $nudiv->pporgunit;
      $obj->unit = $nudiv->pporgunitdesc;
      $obj->save();

      return $response;

    }
}
