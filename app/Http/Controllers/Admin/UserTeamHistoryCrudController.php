<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserTeamHistoryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class UserTeamHistoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserTeamHistoryCrudController extends CrudController
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
        CRUD::setModel(\App\Models\UserTeamHistory::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/user-team-history');
        CRUD::setEntityNameStrings('user team history', 'user team histories');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // CRUD::setFromDb(); // columns

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */

        CRUD::addColumn(['name' => 'created_at', 'type' => 'datetime', 'label' => 'Date']);
        CRUD::addColumn([
          'name' => 'user_id',
          'label' => 'User',
          'type' => 'relationship',
          'entity'    => 'user',
          'attribute' => 'id_name',
          'model'     => User::class,
          "relation_type" => "BelongsTo",
          'priority' => 1,
          'searchLogic' => function ($query, $column, $searchTerm) {
            $query->orWhereHas('user', function ($q) use ($column, $searchTerm) {
              $q->where('name', 'like', '%'.$searchTerm.'%')
                ->orWhere('staff_no', '=', $searchTerm);
            });
          }
        ]);
        CRUD::addColumn([
          'name' => 'old_superior_id',
          'label' => 'From',
          'type' => 'relationship',
          'entity'    => 'oldboss',
          'attribute' => 'id_name',
          'model'     => User::class,
          "relation_type" => "BelongsTo",
          'priority' => 1
        ]);
        CRUD::addColumn([
          'name' => 'new_superior_id',
          'label' => 'To',
          'type' => 'relationship',
          'entity'    => 'newboss',
          'attribute' => 'id_name',
          'model'     => User::class,
          "relation_type" => "BelongsTo",
          'priority' => 1
        ]);
        CRUD::addColumn(['name' => 'remark', 'type' => 'text', 'label' => 'Remark']);
        CRUD::addColumn(['name' => 'editor.name', 'type' => 'text', 'label' => 'Edited By']);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(UserTeamHistoryRequest::class);

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
