<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class ActivityType extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'activity_types';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function GrpSubTypes($gid){
      return ActSubType::where('comp_group_id', $gid)
        ->where('activity_type_id', $this->id)->pluck('descr');
    }

    public function showActSubTypeList($crud = false)
    {
      return '<a class="btn btn-sm btn-link" href="'
        . route('ctactsubtype.index', ['gid' => request()->gid, 'atid' => $this->id ])
        . '" title="Sub Types"><i class="las la-list-alt"></i> Sub Types</a>';

    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function TaskCategories(){
      return $this->belongsToMany(TaskCategory::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
