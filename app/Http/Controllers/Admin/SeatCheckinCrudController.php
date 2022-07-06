<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SeatCheckinRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class SeatCheckinCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SeatCheckinCrudController extends CrudController
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
        CRUD::setModel(\App\Models\SeatCheckin::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/seatcheckin');
        CRUD::setEntityNameStrings('seatcheckin', 'Workspace Check-in History');
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
        CRUD::addClause('where', 'user_id', '=', backpack_user()->id);

        CRUD::removeColumn('latitude');
        CRUD::removeColumn('longitude');
        CRUD::removeColumn('qr_code');
        CRUD::removeColumn('area_boooking_id');
        CRUD::removeColumn('user_id');

        CRUD::modifyColumn('seat_id', [
          'label' => 'Location',
          'type' => 'relationship',
          'entity'    => 'Seat',
          'attribute' => 'label',
          'model'     => Seat::class,
          "relation_type" => "BelongsTo",
        ]);

        CRUD::modifyColumn('event_attendance_id', [
          'label' => 'Event',
          'type' => 'relationship',
          'entity'    => 'EventAttendance',
          'attribute' => 'name',
          'model'     => EventAttendance::class,
          "relation_type" => "BelongsTo",
        ]);

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */

        $this->crud->addFilter([
          'type'  => 'date',
          'name'  => 'in_time',
          'label' => 'Check-in Date'
        ],
          false,
        function ($value) { // if the filter is active, apply these constraints
          $this->crud->addClause('whereDate', 'in_time', $value);
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
        CRUD::setValidation(SeatCheckinRequest::class);

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
}
