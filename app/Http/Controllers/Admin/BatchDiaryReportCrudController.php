<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BatchDiaryReportRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Jobs\BatchDiaryRptProcessor;
use App\Models\CompGroup;
use App\common\CommonHelper;
use App\common\ExcelHandler;

/**
 * Class BatchDiaryReportCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BatchDiaryReportCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\BatchDiaryReport::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/batch-diary-report');
        CRUD::setEntityNameStrings('batch diary report', 'batch diary reports');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // request()->gid
        // CRUD::setFromDb(); // columns

        $cg = CompGroup::findOrFail(request()->gid);
        CRUD::setSubheading('Group: ' . $cg->name);

        if(!CommonHelper::UserCanAccessGroup($cg->id, backpack_user()->id)){
          abort(403);
        }

        CRUD::addClause('where', 'obj_id', request()->gid);
        CRUD::addClause('where', 'class_name', 'CompGroup');

        CRUD::addColumn(['name' => 'from_date', 'type' => 'date', 'label' => 'From']);
        CRUD::addColumn(['name' => 'to_date', 'type' => 'date', 'label' => 'To']);
        CRUD::addColumn(['name' => 'status', 'type' => 'text', 'label' => 'Status']);
        CRUD::addColumn(['name' => 'created_at', 'type' => 'datetime', 'label' => 'Queued At']);
        CRUD::addColumn(['name' => 'processed_at', 'type' => 'datetime', 'label' => 'Processed At']);
        CRUD::addColumn(['name' => 'completed_at', 'type' => 'datetime', 'label' => 'Completed At']);
        CRUD::addColumn(['name' => 'User.id_name', 'type' => 'text', 'label' => 'Requestor']);
        CRUD::addColumn(['name' => 'filename', 'type' => 'textarea', 'label' => 'Filename']);
        CRUD::addColumn(['name' => 'extra_info', 'type' => 'textarea', 'label' => 'Extra Info', 'escaped' => true]);

        $this->crud->removeButton('create');
        $this->crud->gid = request()->gid;
        $this->crud->addButtonFromView('top', 'addbdiaryrpt', 'addbdiaryrpt', 'end');
        CRUD::addButtonFromModelFunction('line', 'dlExcel', 'dlExcel', 'end');

        $this->data['breadcrumbs'] = [
            'Home' => backpack_url('dashboard'),
            'Caretaker' => backpack_url('caretaker'),
            'Batch Diary Reports' => route('batch-diary-report.index', ['gid' => $cg->id]),
            'List' => false
        ];

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
      $cg = CompGroup::findOrFail(request()->gid);
      CRUD::setSubHeading('Group: ' . $cg->name);

      $maxdate = date('Y-m-d');
        CRUD::setValidation(BatchDiaryReportRequest::class);

        // CRUD::setFromDb(); // fields

      CRUD::addField(['name' => 'from_date', 'type' => 'date', 'attributes' => ['max' => $maxdate, 'min' => '2021-01-01'], 'value' => $maxdate]);
      CRUD::addField(['name' => 'to_date', 'type' => 'date', 'attributes' => ['max' => $maxdate, 'min' => '2021-01-01'], 'value' => $maxdate]);
      CRUD::addField(['name' => 'obj_id', 'type' => 'hidden', 'value' => request()->gid]);
      CRUD::addField(['name' => 'class_name', 'type' => 'hidden', 'value' => 'CompGroup']);
      CRUD::addField(['name' => 'user_id', 'type' => 'hidden', 'value' => backpack_user()->id]);
      $this->crud->route = url()->previous();

      $this->data['breadcrumbs'] = [
          'Home' => backpack_url('dashboard'),
          'Caretaker' => backpack_url('caretaker'),
          'Batch Diary Reports' => route('batch-diary-report.index', ['gid' => $cg->id]),
          'Create' => false
      ];
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
      if(!CommonHelper::UserCanAccessGroup($this->crud->getRequest()->request->get('obj_id'), backpack_user()->id)){
        abort(403);
      }
      $response = $this->traitStore();

      // trigger the job
      // $this->crud->entry->floor_section->increment('tracked_seat_count');
      BatchDiaryRptProcessor::dispatch($this->crud->entry->id)->onQueue('default_long');
      return $response;
    }

    public function download($id){
      $obj = CRUD::getCurrentEntry();
      if(isset($obj->filename)){
        return ExcelHandler::DownloadFromPerStorage($obj->filename);
      } else {
        \Alert::error('No report file exist for this entry')->flash();
        return redirect()->back();
      }
    }
}
