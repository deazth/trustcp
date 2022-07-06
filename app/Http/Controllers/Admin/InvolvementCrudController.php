<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\InvolvementRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\Widget;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\common\CommonHelper;
use App\Models\User;
use App\Models\BauExpType;

/**
 * Class InvolvementCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class InvolvementCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Involvement::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/involvement');
        CRUD::setEntityNameStrings('involvement', 'involvements');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
      $cuser = backpack_user();
      CRUD::setSubheading('My current work involvements');
      if(request()->filled('uid')){
        $perm = CommonHelper::UserCanAccessUser(backpack_user()->id, request()->uid);
        if(!$perm){
          abort(403);
        }

        $cuser = User::findOrFail(request()->uid);
        CRUD::setSubheading($cuser->id_name);
        if(backpack_user()->id != $cuser->id){
          $this->crud->denyAccess(['update', 'create', 'delete']);
        }
      }

      CRUD::addClause('where', 'user_id', '=', $cuser->id);
      // CRUD::setFromDb();
      CRUD::addColumn(['name' => 'BauExpType.name', 'type' => 'text', 'label' => 'Category']);
      CRUD::addColumn(['name' => 'BauExp.name', 'type' => 'text', 'label' => 'App/Exp']);

      CRUD::addColumn([
        'name' => 'Jobscopes',
        'label' => 'Role',
        'type' => 'relationship',
        'entity'    => 'Jobscopes', // the method that defines the relationship in your Model
        'attribute' => 'name', // foreign key attribute that is shown to user
        'model'     => App\Models\Jobscope::class, // foreign key model
      ]);

      CRUD::addColumn(['name' => 'perc', 'type' => 'number', 'label' => 'Percentage']);

      // CRUD::addColumn([
      //   'name' => 'totperc',
      //   'label' => 'Percentage',
      //   'type' => 'model_function',
      //   'function_name'    => 'GetSumPerc'
      // ]);

      // get the total sum of all involvement percentage
      $sumallperc = \App\Models\Involvement::where('user_id', $cuser->id)->sum('perc');

      $sumcol = 'info';
      $sumtext = 'Add or adjust so that the total is 100%.';
      if($sumallperc > 100){
        $this->crud->denyAccess('create');
        $sumcol = 'warning';
        $sumtext = 'Remove or adjust so that the total is 100%.';
      } elseif ($sumallperc == 100) {
        $this->crud->denyAccess('create');
        $sumcol = 'success';
        $sumtext = 'Thank you for your cooperation';
      }

      Widget::add([
        'type'         => 'alert',
        'class'        => 'alert mb-2 alert-'. $sumcol,
        'heading'      => 'Total involvement percentage is ' . $sumallperc . '%',
        'content'      => $sumtext,
        'close_button' => false, // show close button or not
      ])->to('after_content');

    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(InvolvementRequest::class);

        Widget::add([
          'type'         => 'alert',
          'class'        => 'alert mb-2 alert-info',
          'heading'      => 'Disclaimer',
          'content'      => 'I hereby confirm that the information declared herein is true and correct to the best of my knowledge of my Individual Skill and Work Involvement. I also undertake and agree that this declaration is my individual skill preference and related with my current work involvement which can be used for further analysis.',
          'close_button' => false, // show close button or not
        ])->to('after_content');

        // $cats = BauExpType::all()->pluck('name', 'id');
        //
        // CRUD::addField([
        //   'name' => 'bau_exp_type_id',
        //   'label' => 'Category',
        //   'type' => 'select_from_array',
        //   'options'     => $cats,
        //   'allows_null' => false,
        //   'wrapper'   => [
        //     'class'      => 'form-group col-md-6'
        //   ],
        // ]);

        CRUD::addField([
          'name' => 'bau_exp_type_id',
          'label' => 'Category',
          'type' => 'select',
          'entity'    => 'BauExpType',
          'model'     => "App\Models\BauExpType",
          'attribute' => 'name',
          'allows_null' => false,
          'wrapper'   => [
            'class'      => 'form-group col-md-6'
          ],
        ]);

        CRUD::addField([
          'name' => 'user_id',
          'value' => backpack_user()->id,
          'type' => 'hidden',
        ]);

        CRUD::addField([
          'name' => 'bau_experience_id',
          'type' => 'select2_from_ajax',
          'label' => 'Application / Function',
          'entity' => 'BauExp',
          'attribute' => 'name',
          'model'       => "App\Models\BauExperience",
          'data_source' => route('wa.getbauexps'),
          'allows_null' => false,
          'attributes' => ['required' => 'required'],
          'include_all_form_fields' => true,
          'dependencies' => ['bau_exp_type_id'],
          'minimum_input_length' => 0,
          'placeholder' => 'Select one',
          'wrapper'   => [
            'class'      => 'form-group col-md-6'
          ],
        ]);

        CRUD::addField([
          'name'            => 'role_perc_list',
          'label'           => 'Role / Jobscope',
          'type'            => 'repeatable',
          'new_item_label' => 'Add Role', // used on the "Add X" button
          'fields' => [
              [
                'name' => 'scope_id',
                'type' => 'select2_from_ajax',
                'label' => 'Role',
                // 'entity' => 'Jobscopes',
                'attribute' => 'name',
                'model'       => "App\Models\Jobscope",
                'data_source' => route('wa.getbauroles'),
                'allows_null' => false,
                'attributes' => ['required' => 'required'],
                'include_all_form_fields' => true,
                'dependencies' => ['bau_exp_type_id'],
                'minimum_input_length' => 0,
                'placeholder' => 'Select one',
                'wrapper' => ['class' => 'form-group col-md-8'],
              ],
              [
                  'name'    => 'percenta',
                  'type'    => 'number',
                  'label'   => 'Percentage',
                  'attributes' => [
                    'step' => '1',
                    'min' => '1',
                    'max' => '100',
                    'required' => 'required'
                  ],
                  'wrapper' => ['class' => 'form-group col-md-4'],

              ],
          ],
          'min_rows' => 1, // maximum rows allowed in the table
          'max_rows' => 15
        ]);
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

        $cab = CRUD::getCurrentEntry();
        $roleval = [];
        foreach ($cab->Jobscopes as $key => $value) {
          $roleval[] = [
            'scope_id' => $value->id,
            'percenta' => $value->pivot->perc,
            'curr_text' => $value->name,
            'curr_id' => $value->id
          ];
        }

        CRUD::addField([
          'name' => 'old_perc',
          'value' => $cab->perc,
          'type' => 'hidden',
        ]);

        CRUD::removeField('role_perc_list');

        CRUD::addField([
          'name'            => 'role_perc_list',
          'label'           => 'Role / Jobscope',
          'type'            => 'repeatable',
          'new_item_label' => 'Add Role', // used on the "Add X" button
          'fields' => [
              [
                'name'    => 'curr_text',
                'type'    => 'text',
                'label'   => 'Role',
                'attributes' => ['disabled' => 'disabled'],
                'wrapper' => ['class' => 'form-group col-md-5'],
              ],
              [
                'name' => 'scope_id',
                'type' => 'select2_from_ajax',
                'label' => 'Change to New Role?',
                // 'entity' => 'ValidRole',
                'attribute' => 'name',
                'model'       => "App\Models\Jobscope",
                'data_source' => route('wa.getbauroles'),
                'allows_null' => true,
                'include_all_form_fields' => true,
                'dependencies' => ['bau_exp_type_id'],
                'minimum_input_length' => 0,
                'placeholder' => 'Select to change (optional)',
                'wrapper' => ['class' => 'form-group col-md-5'],
              ],
              [
                  'name'    => 'percenta',
                  'type'    => 'number',
                  'label'   => 'Percentage',
                  'attributes' => [
                    'step' => '1',
                    'min' => '1',
                    'max' => '100',
                    'required' => 'required'
                  ],
                  'wrapper' => ['class' => 'form-group col-md-2'],

              ],
              [
                  'name'    => 'curr_id',
                  'type'    => 'hidden'
              ],
          ],
          'min_rows' => 1, // maximum rows allowed in the table
          'max_rows' => 15,
          'value' => $roleval
        ]);
    }

    public function store(){
      $hrspent = json_decode($this->crud->getRequest()->request->get('role_perc_list'));
      $user_id = $this->crud->getRequest()->request->get('user_id');
      $pivdata = [];
      $sumperc = 0;
      foreach ($hrspent as $key => $value) {
        $pivdata[$value->scope_id] = ['perc' => $value->percenta];
        $sumperc += $value->percenta;
      }

      if(!$this->canAddPerc($user_id, $sumperc)){
        \Alert::error('New total percentage will exceed 100%')->flash();
        return redirect()->back()->withInput();
      }

      $response = $this->traitStore();

      // add the pivot data
      $cur = CRUD::getCurrentEntry();

      $cur->Jobscopes()->sync($pivdata);
      $cur->perc = $sumperc;
      $cur->save();

      return $response;
    }

    public function update(){
      // dd($this->crud->getRequest()->all());
      $hrspent = json_decode($this->crud->getRequest()->request->get('role_perc_list'));
      $user_id = $this->crud->getRequest()->request->get('user_id');
      $oldpprec = $this->crud->getRequest()->request->get('old_perc');
      $pivdata = [];
      $sumperc = 0;
      foreach ($hrspent as $key => $value) {
        $sid = $value->scope_id ?? $value->curr_id;
        $pivdata[$sid] = ['perc' => $value->percenta];
        $sumperc += $value->percenta;
      }

      $delta = $sumperc - $oldpprec;
      if(!$this->canAddPerc($user_id, $delta)){
        \Alert::error('New total percentage will exceed 100%')->flash();
        return redirect()->back()->withInput();
      }

      $response = $this->traitUpdate();
      $cur = CRUD::getCurrentEntry();



      $cur->Jobscopes()->sync($pivdata);
      $cur->perc = $sumperc;
      $cur->save();

      return $response;
    }

    private function canAddPerc($user_id, $newperc){
      $user = User::findOrFail($user_id);

      $inv = $user->Involvements;
      if($inv){
        $sump = $inv->sum('perc');

        return ($sump + $newperc) <= 100;
      }

      return true;
    }
}
