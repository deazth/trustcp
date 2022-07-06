<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DailyPerformanceRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\common\CommonHelper;
use App\Models\User;
use App\Models\DailyPerformance;

/**
 * Class DailyPerformanceCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DailyPerformanceCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\DailyPerformance::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/dailyperformance');
        CRUD::setEntityNameStrings('dailyperformance', 'Daily Diary Summary');
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
      $cuser = backpack_user();
      if(request()->filled('uid')){
        $perm = CommonHelper::UserCanAccessUser(backpack_user()->id, request()->uid);
        if(!$perm){
          abort(403);
        }

        $cuser = User::findOrFail(request()->uid);
        CRUD::setSubheading($cuser->id_name);
      }
      CRUD::addClause('where', 'user_id', '=', $cuser->id);
      CRUD::addColumn(['name' => 'record_date', 'type' => 'date', 'label' => 'Date', 'priority' => 1]);
      CRUD::addColumn(['name' => 'expected_hours', 'type' => 'number', 'label' => 'Expected', 'decimals'     => 1]);
      CRUD::addColumn(['name' => 'actual_hours', 'type' => 'number', 'label' => 'Actual', 'decimals'     => 1]);
      CRUD::addColumn(['name' => 'performance', 'type' => 'number', 'label' => 'Percentage', 'suffix'=> '%']);
      CRUD::addColumn(['name' => 'info', 'type' => 'text', 'label' => 'Info', 'priority' => 1]);

        // CRUD::setFromDb(); // columns

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */

        CRUD::addButtonFromModelFunction('line', 'getEntriesBtn', 'getEntriesBtn', 'end');
        CRUD::addButtonFromModelFunction('line', 'getResetdfBtn', 'getResetdfBtn', 'end');

        $this->crud->addFilter([
          'type'  => 'date_range',
          'name'  => 'rec_date',
          'label' => 'Date'
        ],
          false,
        function ($value) { // if the filter is active, apply these constraints
          $dates = json_decode($value);
          $this->crud->addClause('where', 'record_date', '>=', $dates->from);
          $this->crud->addClause('where', 'record_date', '<=', $dates->to);
        });
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(DailyPerformanceRequest::class);

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
        $this->setupCreateOperation();
    }

    public function resetDailyPerf(){
      $req = request();

      if($req->filled('dfid')){
        $df = DailyPerformance::findOrFail($req->dfid);
        $perm = CommonHelper::UserCanAccessUser(backpack_user()->id, $df->user_id);
        if(!$perm){
          CommonHelper::Log403Err(backpack_user(), $df->User, 'DailyPerf-reset', 'perm ' . $perm);
          abort(403);
        }

        $nudf = \App\common\GDWActions::GetDailyPerfObj($df->user_id, $df->record_date, true, true);
        \Alert::info('Record reset successful for ' . $df->record_date)->flash();
        return redirect()->back();
      } else {
        abort(404);
      }

    }
}
