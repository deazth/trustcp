<?php namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;

trait UpdatedByTrait {

    /**
     * Stores the user id at each create & update.
     */
    public function save(array $options = [])
    {

        if (\Auth::check())
        {
            if (!isset($this->created_by) || $this->created_by=='') {
                $this->created_by = \Auth::user()->id;
            }

            $this->updated_by = \Auth::user()->id;
        }

        parent::save();
    }


    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function updator()
    {
        return $this->belongsTo('App\User', 'updated_by');
    }
}
