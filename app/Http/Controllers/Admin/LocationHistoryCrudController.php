<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LocationHistoryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\common\UserRegisterHandler;
use App\Models\User;

/**
 * Class LocationHistoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class LocationHistoryCrudController extends CrudController
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
        CRUD::setModel(\App\Models\LocationHistory::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/locationhistory');
        CRUD::setEntityNameStrings('Location History', 'Location Histories');
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
        CRUD::setFromDb(); // columns
        CRUD::setHeading('Location History');
        CRUD::setSubheading('Work outside agile office workspace');
        CRUD::removeColumn('user_id');
        CRUD::addColumn(['name' => 'created_at', 'type' => 'datetime', 'label' => 'Time', 'priority' => 1]);

        $user_id = backpack_user()->id;
        $req = CRUD::getRequest();
        if($req->filled('uid')){
          $user_id = $req->uid;
          if(!\App\common\CommonHelper::UserCanAccessUser(backpack_user()->id, $req->uid)){
            abort(403);
          }
          $tuser = User::findOrFail($user_id);
          CRUD::setSubheading($tuser->id_name);
        }
        CRUD::modifyColumn('address', ['type' => 'textarea', 'escaped' => true]);

        CRUD::addClause('where', 'user_id', '=', $user_id);
        CRUD::addButtonFromModelFunction('line', 'getGmapBtn', 'getGmapBtn', 'end');

        $this->crud->addFilter(
          [
            'type'  => 'date_range',
            'name'  => 'act_range',
            'label' => 'Date Range'
          ],
          false,
          function ($value) { // if the filter is active, apply these constraints
            $dates = json_decode($value);
            $this->crud->addClause('whereDate', 'created_at', '>=', $dates->from);
            $this->crud->addClause('whereDate', 'created_at', '<=', $dates->to);
          }
        );

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
        CRUD::setValidation(LocationHistoryRequest::class);

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

    public function checkinloc(){

      $this->data['title'] = 'Location Check-in';
      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'Location Check-in' => false,
      ];
      $this->data['user'] = backpack_user();

      return view('staff.loc_checkin', $this->data);
    }

    public function docheckinloc(){
      $req = CRUD::getRequest();

      if($req->filled('lat') && $req->filled('long')){
        if($req->filled('action')){
          if($req->action == 'updateloc'){
            UserRegisterHandler::attUpdateLoc($req->staff_id,
              $req->lat, $req->long,
              $req->filled('reason') ? $req->reason : '',
              $req->address
            );
            \Alert::info("Location updated")->flash();
          } elseif ($req->action == 'clockout') {
            UserRegisterHandler::attClockOut($req->staff_id, \Carbon\Carbon::now(),
              $req->lat, $req->long,
              $req->filled('reason') ? $req->reason : '',
              $req->address
            );
            \Alert::info("Checked-out")->flash();
          } elseif ($req->action == 'clockin') {
            UserRegisterHandler::attClockIn($req);
            \Alert::info("Checked-in")->flash();
          } else {
            \Alert::error("Unknown action code")->flash();
          }

        } else {
          \Alert::error("No action code")->flash();
        }
      } else {
        \Alert::error("No coordinate provided")->flash();
      }

      return redirect()->back();
    }
}
