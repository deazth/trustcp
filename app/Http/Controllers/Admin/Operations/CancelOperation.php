<?php

namespace App\Http\Controllers\Admin\Operations;

use Illuminate\Support\Facades\Route;

trait CancelOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupCancelRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/{id}/cancel', [
            'as'        => $routeName.'.cancel',
            'uses'      => $controller.'@cancel',
            'operation' => 'cancel',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupCancelDefaults()
    {
        $this->crud->allowAccess('cancel');

        $this->crud->operation('cancel', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();
        });

        $this->crud->operation('list', function () {
            // $this->crud->addButton('top', 'cancel', 'view', 'crud::buttons.cancel');
            $this->crud->addButtonFromView('line', 'cancel', 'cancel', 'beginning');
        });
    }

    public function cancel()
    {
      $this->crud->hasAccessOrFail('cancel');

      $data = $this->crud->getCurrentEntry() ??  $this->crud->model->findOrFail($id);
      if($data->user_id == backpack_user()->id){
        $data->status = 'Cancelled';
        $data->save();
        return "Success";
      } else {
        abort(403);
      }
    }
}
